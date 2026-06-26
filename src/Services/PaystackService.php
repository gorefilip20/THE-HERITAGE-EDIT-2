<?php

declare(strict_types=1);

namespace HeritageEdit\Services;

use GuzzleHttp\Client;

final class PaystackService
{
    private Client $http;
    private array $config;

    public function __construct()
    {
        $services    = require __DIR__ . '/../../config/services.php';
        $this->config = $services['paystack'];
        $this->http   = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout'  => 30,
            'headers'  => [
                'Authorization' => 'Bearer ' . $this->config['secret_key'],
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    /**
     * Initialize a Paystack transaction.
     * Returns ['authorization_url', 'access_code', 'reference'].
     */
    public function initialize(
        string $email,
        int $amountKobo,
        string $reference,
        string $currency = 'NGN',
        array $metadata = []
    ): array {
        $response = $this->http->post('/transaction/initialize', [
            'json' => [
                'email'     => $email,
                'amount'    => $amountKobo,
                'reference' => $reference,
                'currency'  => $currency,
                'metadata'  => $metadata,
                'callback_url' => rtrim(config('app.url'), '/') . '/checkout/verify',
            ],
        ]);

        $body = json_decode((string) $response->getBody(), true);
        if (!$body['status']) {
            throw new \RuntimeException('Paystack init failed: ' . ($body['message'] ?? 'Unknown'));
        }

        return $body['data'];
    }

    /**
     * Verify a transaction by reference.
     */
    public function verify(string $reference): array
    {
        $response = $this->http->get('/transaction/verify/' . rawurlencode($reference));
        $body     = json_decode((string) $response->getBody(), true);

        if (!$body['status']) {
            throw new \RuntimeException('Paystack verify failed: ' . ($body['message'] ?? 'Unknown'));
        }

        return $body['data'];
    }

    /**
     * Validate Paystack webhook signature.
     */
    public function validateWebhook(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha512', $payload, $this->config['webhook_secret']);
        return hash_equals($expected, $signature);
    }

    /**
     * Convert decimal amount to Paystack kobo/cents (smallest unit).
     */
    public static function toSubunit(float $amount, string $currency = 'NGN'): int
    {
        // All Paystack currencies use 100-based subunits
        return (int) round($amount * 100);
    }

    /**
     * Generate a unique payment reference.
     */
    public static function generateReference(string $prefix = 'THE'): string
    {
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(8)));
    }
}
