<?php

declare(strict_types=1);

namespace HeritageEdit\Services;

use HeritageEdit\Core\HttpClient;
use HeritageEdit\Core\Env;

final class PaystackService
{
    private HttpClient $http;
    private string $secretKey;
    private string $webhookSecret;
    private const BASE = 'https://api.paystack.co';

    public function __construct()
    {
        $this->secretKey     = Env::get('PAYSTACK_SECRET_KEY', '');
        $this->webhookSecret = Env::get('PAYSTACK_WEBHOOK_SECRET', '');
        $this->http = new HttpClient([
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type'  => 'application/json',
            ],
        ]);
    }

    /**
     * Initialize a transaction.
     * Returns ['authorization_url', 'access_code', 'reference'].
     */
    public function initialize(
        string $email,
        int    $amountKobo,
        string $reference,
        string $currency = 'NGN',
        array  $metadata = []
    ): array {
        $callbackUrl = rtrim(Env::get('APP_URL', ''), '/') . '/checkout/verify';

        $response = $this->http->post(self::BASE . '/transaction/initialize', [
            'email'        => $email,
            'amount'       => $amountKobo,
            'reference'    => $reference,
            'currency'     => $currency,
            'metadata'     => $metadata,
            'callback_url' => $callbackUrl,
        ])->throw();

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new \RuntimeException('Paystack init failed: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body['data'];
    }

    /**
     * Verify a transaction by reference.
     */
    public function verify(string $reference): array
    {
        $response = $this->http->get(
            self::BASE . '/transaction/verify/' . rawurlencode($reference)
        )->throw();

        $body = $response->json();

        if (!($body['status'] ?? false)) {
            throw new \RuntimeException('Paystack verify failed: ' . ($body['message'] ?? 'Unknown error'));
        }

        return $body['data'];
    }

    /**
     * Validate Paystack webhook HMAC-SHA512 signature.
     */
    public function validateWebhook(string $payload, string $signature): bool
    {
        $expected = hash_hmac('sha512', $payload, $this->webhookSecret);
        return hash_equals($expected, $signature);
    }

    /**
     * Convert decimal amount to Paystack smallest unit (kobo/cents).
     */
    public static function toSubunit(float $amount): int
    {
        return (int) round($amount * 100);
    }

    /**
     * Generate a cryptographically random payment reference.
     */
    public static function generateReference(string $prefix = 'THE'): string
    {
        return $prefix . '-' . strtoupper(bin2hex(random_bytes(8)));
    }
}
