<?php

declare(strict_types=1);

namespace HeritageEdit\Models;

use HeritageEdit\Core\Database;
use HeritageEdit\Core\Uuid;

final class Order
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function createFromCart(array $cartData, array $payload): string
    {
        $orderId     = Uuid::v4();
        $orderNumber = $this->generateOrderNumber();

        $this->db->beginTransaction();
        try {
            $shippingAddrId = $this->saveAddress($payload['shipping_address']);
            $billingAddrId  = isset($payload['billing_address'])
                ? $this->saveAddress($payload['billing_address'])
                : $shippingAddrId;

            $subtotal     = (float) $cartData['subtotal'];
            $shippingCost = (float) ($payload['shipping_cost']   ?? 0);
            $dutiesTaxes  = (float) ($payload['duties_taxes']    ?? 0);
            $discount     = (float) ($payload['discount_amount'] ?? 0);
            $total        = $subtotal + $shippingCost + $dutiesTaxes - $discount;

            $this->db->insert('orders', [
                'id'                  => $orderId,
                'order_number'        => $orderNumber,
                'user_id'             => $payload['user_id']     ?? null,
                'guest_email'         => $payload['guest_email'] ?? null,
                'status'              => 'pending',
                'payment_status'      => 'unpaid',
                'shipping_address_id' => $shippingAddrId,
                'billing_address_id'  => $billingAddrId,
                'subtotal'            => $subtotal,
                'discount_amount'     => $discount,
                'shipping_cost'       => $shippingCost,
                'duties_taxes'        => $dutiesTaxes,
                'total'               => $total,
                'currency'            => $payload['currency']    ?? 'NGN',
                'shipping_carrier'    => $payload['carrier']     ?? null,
            ]);

            foreach ($cartData['items'] as $item) {
                $variantLabel = implode(' / ', array_filter([$item['size'] ?? null, $item['color'] ?? null]));
                $this->db->insert('order_items', [
                    'id'            => Uuid::v4(),
                    'order_id'      => $orderId,
                    'product_id'    => $item['product_id'],
                    'product_title' => $item['title'],
                    'variant_label' => $variantLabel ?: null,
                    'quantity'      => $item['quantity'],
                    'unit_price'    => $item['unit_price'],
                    'total_price'   => $item['unit_price'] * $item['quantity'],
                ]);

                $this->db->query(
                    'UPDATE products SET total_sold = total_sold + ? WHERE id = ?',
                    [$item['quantity'], $item['product_id']]
                );
            }

            $this->db->commit();
        } catch (\Throwable $e) {
            $this->db->rollback();
            throw $e;
        }

        return $orderId;
    }

    public function findById(string $id): ?array
    {
        $order = $this->db->fetch(
            'SELECT o.*, a.first_name, a.last_name, a.line1, a.city, a.country
             FROM orders o
             LEFT JOIN addresses a ON a.id = o.shipping_address_id
             WHERE o.id = ?',
            [$id]
        );
        if (!$order) return null;
        $order['items'] = $this->db->fetchAll('SELECT * FROM order_items WHERE order_id = ?', [$id]);
        return $order;
    }

    public function findByNumber(string $number): ?array
    {
        $order = $this->db->fetch('SELECT * FROM orders WHERE order_number = ?', [$number]);
        if (!$order) return null;
        $order['items'] = $this->db->fetchAll('SELECT * FROM order_items WHERE order_id = ?', [$order['id']]);
        return $order;
    }

    public function updatePaymentStatus(string $orderId, string $status, string $reference = ''): void
    {
        $this->db->update('orders', [
            'payment_status' => $status,
            'status'         => 'confirmed',
        ], 'id = ?', [$orderId]);
    }

    private function saveAddress(array $addr): string
    {
        $id = Uuid::v4();
        $this->db->insert('addresses', [
            'id'          => $id,
            'user_id'     => $addr['user_id']     ?? null,
            'type'        => $addr['type']         ?? 'shipping',
            'first_name'  => $addr['first_name'],
            'last_name'   => $addr['last_name'],
            'company'     => $addr['company']      ?? null,
            'line1'       => $addr['line1'],
            'line2'       => $addr['line2']        ?? null,
            'city'        => $addr['city'],
            'state'       => $addr['state']        ?? '',
            'postal_code' => $addr['postal_code']  ?? '',
            'country'     => $addr['country'],
            'phone'       => $addr['phone']        ?? null,
        ]);
        return $id;
    }

    private function generateOrderNumber(): string
    {
        return 'THE-' . strtoupper(bin2hex(random_bytes(4)));
    }
}
