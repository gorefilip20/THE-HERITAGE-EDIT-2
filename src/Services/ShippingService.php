<?php

declare(strict_types=1);

namespace HeritageEdit\Services;

use GuzzleHttp\Client;
use HeritageEdit\Core\Database;

final class ShippingService
{
    private Client $http;
    private array $config;
    private Database $db;

    // Flat duty rates by country (simplified; use a real service in production)
    private const DUTY_RATES = [
        'NG' => 0.00,  // Nigeria — home market
        'US' => 0.15,
        'GB' => 0.12,
        'DE' => 0.19,
        'FR' => 0.20,
        'IT' => 0.22,
        'AE' => 0.05,
        'GH' => 0.10,
        'ZA' => 0.15,
    ];

    // VAT / GST rates
    private const VAT_RATES = [
        'NG' => 0.075,
        'US' => 0.00,
        'GB' => 0.20,
        'DE' => 0.19,
        'FR' => 0.20,
        'IT' => 0.22,
        'AE' => 0.05,
        'GH' => 0.125,
        'ZA' => 0.15,
    ];

    public function __construct()
    {
        $services    = require __DIR__ . '/../../config/services.php';
        $this->config = $services['easypost'];
        $this->db     = Database::getInstance();
        $this->http   = new Client([
            'base_uri' => $this->config['base_url'],
            'timeout'  => 20,
            'auth'     => [$this->config['api_key'], ''],
        ]);
    }

    /**
     * Get available shipping rates for a given shipment.
     * Returns array of rate tiers with carrier, service, cost, and ETA.
     */
    public function getRates(array $toAddress, int $weightGrams = 500): array
    {
        // Try cache first
        $cached = $this->getCachedRates($toAddress['country'], $weightGrams);
        if ($cached) return $cached;

        try {
            $shipment = $this->http->post('/shipments', [
                'json' => [
                    'shipment' => [
                        'to_address' => [
                            'city'    => $toAddress['city']        ?? '',
                            'state'   => $toAddress['state']       ?? '',
                            'zip'     => $toAddress['postal_code'] ?? '',
                            'country' => $toAddress['country'],
                        ],
                        'from_address' => [
                            'city'    => 'Lagos',
                            'state'   => 'Lagos',
                            'zip'     => '100001',
                            'country' => 'NG',
                        ],
                        'parcel' => [
                            'weight' => round($weightGrams / 453.592, 2), // oz
                            'length' => 12,
                            'width'  => 9,
                            'height' => 3,
                        ],
                    ],
                ],
            ]);

            $data  = json_decode((string) $shipment->getBody(), true);
            $rates = array_map(fn($r) => [
                'carrier'        => $r['carrier'],
                'service'        => $r['service'],
                'rate'           => (float) $r['rate'],
                'currency'       => $r['currency'],
                'est_days'       => (int) ($r['est_delivery_days'] ?? 14),
                'delivery_label' => $this->formatDeliveryLabel((int) ($r['est_delivery_days'] ?? 14)),
            ], $data['shipment']['rates'] ?? []);

            usort($rates, fn($a, $b) => $a['rate'] <=> $b['rate']);
            return $rates;
        } catch (\Throwable $e) {
            error_log('[ShippingService] EasyPost error: ' . $e->getMessage());
            return $this->fallbackRates($toAddress['country']);
        }
    }

    /**
     * Calculate duties and taxes for a cart total.
     */
    public function calculateLandedCost(float $subtotal, string $destCountry): array
    {
        $dutyRate = self::DUTY_RATES[$destCountry] ?? 0.15;
        $vatRate  = self::VAT_RATES[$destCountry]  ?? 0.10;

        $duty     = round($subtotal * $dutyRate, 2);
        $vat      = round(($subtotal + $duty) * $vatRate, 2);
        $total    = round($duty + $vat, 2);

        return [
            'duty_amount'    => $duty,
            'vat_amount'     => $vat,
            'total_taxes'    => $total,
            'duty_rate'      => $dutyRate,
            'vat_rate'       => $vatRate,
            'dest_country'   => $destCountry,
            'disclaimer'     => 'Landed costs are estimates. Final charges may vary by customs authority.',
        ];
    }

    private function fallbackRates(string $country): array
    {
        $isAfrica      = in_array($country, ['NG', 'GH', 'ZA', 'KE', 'CI']);
        $isEurope      = in_array($country, ['GB', 'DE', 'FR', 'IT', 'NL', 'ES']);
        $isMiddleEast  = in_array($country, ['AE', 'SA', 'QA', 'KW']);

        if ($country === 'NG') {
            return [
                ['carrier' => 'DHL',   'service' => 'Express', 'rate' => 2500,  'currency' => 'NGN', 'est_days' => 1, 'delivery_label' => 'Next Day'],
                ['carrier' => 'FedEx', 'service' => 'Standard', 'rate' => 1500, 'currency' => 'NGN', 'est_days' => 3, 'delivery_label' => '2-3 Days'],
            ];
        }

        return [
            ['carrier' => 'DHL',   'service' => 'Express Worldwide',      'rate' => 45.00, 'currency' => 'USD', 'est_days' => 3,  'delivery_label' => '3-5 Business Days'],
            ['carrier' => 'FedEx', 'service' => 'International Priority', 'rate' => 38.00, 'currency' => 'USD', 'est_days' => 5,  'delivery_label' => '5-7 Business Days'],
            ['carrier' => 'DHL',   'service' => 'Economy Select',         'rate' => 22.00, 'currency' => 'USD', 'est_days' => 10, 'delivery_label' => '10-14 Business Days'],
        ];
    }

    private function formatDeliveryLabel(int $days): string
    {
        if ($days <= 1)  return 'Next Business Day';
        if ($days <= 3)  return '2-3 Business Days';
        if ($days <= 7)  return '5-7 Business Days';
        if ($days <= 14) return '10-14 Business Days';
        return '2-4 Weeks';
    }

    private function getCachedRates(string $country, int $weight): ?array
    {
        $rows = $this->db->fetchAll(
            'SELECT carrier, service_level AS service, rate_amount AS rate, currency, estimated_days AS est_days
             FROM shipping_rate_cache
             WHERE dest_country = ? AND weight_min_g <= ? AND weight_max_g >= ? AND expires_at > NOW()
             ORDER BY rate_amount',
            [$country, $weight, $weight]
        );

        if (!$rows) return null;
        return array_map(fn($r) => array_merge($r, [
            'delivery_label' => $this->formatDeliveryLabel((int) $r['est_days']),
        ]), $rows);
    }
}
