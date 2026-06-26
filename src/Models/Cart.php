<?php

declare(strict_types=1);

namespace HeritageEdit\Models;

use HeritageEdit\Core\Database;
use HeritageEdit\Core\Session;
use HeritageEdit\Core\Uuid;

final class Cart
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getOrCreate(): array
    {
        $cartId = Session::get('cart_id');

        if ($cartId) {
            $cart = $this->db->fetch(
                'SELECT * FROM carts WHERE id = ? AND expires_at > NOW()',
                [$cartId]
            );
            if ($cart) return $cart;
        }

        $cartId = Uuid::v4();
        $this->db->insert('carts', [
            'id'         => $cartId,
            'user_id'    => Session::userId(),
            'session_id' => Session::id(),
            'currency'   => 'NGN',
            'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        ]);

        Session::set('cart_id', $cartId);
        return $this->db->fetch('SELECT * FROM carts WHERE id = ?', [$cartId]);
    }

    public function addItem(string $productId, ?string $variantId, int $qty = 1): array
    {
        $cart = $this->getOrCreate();

        $product = $this->db->fetch(
            'SELECT COALESCE(sale_price, base_price) AS price FROM products WHERE id = ?',
            [$productId]
        );
        if (!$product) throw new \InvalidArgumentException('Product not found');

        $priceDelta = 0.0;
        if ($variantId) {
            $variant    = $this->db->fetch('SELECT price_delta FROM product_variants WHERE id = ?', [$variantId]);
            $priceDelta = (float) ($variant['price_delta'] ?? 0);
        }

        $unitPrice = (float) $product['price'] + $priceDelta;

        $existing = $this->db->fetch(
            'SELECT id, quantity FROM cart_items
             WHERE cart_id = ? AND product_id = ?
               AND (variant_id = ? OR (variant_id IS NULL AND ? IS NULL))',
            [$cart['id'], $productId, $variantId, $variantId]
        );

        if ($existing) {
            $this->db->update('cart_items', ['quantity' => $existing['quantity'] + $qty], 'id = ?', [$existing['id']]);
        } else {
            $this->db->insert('cart_items', [
                'id'         => Uuid::v4(),
                'cart_id'    => $cart['id'],
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity'   => $qty,
                'unit_price' => $unitPrice,
            ]);
        }

        return $this->getCartWithItems($cart['id']);
    }

    public function updateItem(string $itemId, int $qty): array
    {
        $item = $this->db->fetch('SELECT * FROM cart_items WHERE id = ?', [$itemId]);
        if (!$item) throw new \InvalidArgumentException('Cart item not found');

        if ($qty <= 0) {
            $this->db->query('DELETE FROM cart_items WHERE id = ?', [$itemId]);
        } else {
            $this->db->update('cart_items', ['quantity' => $qty], 'id = ?', [$itemId]);
        }

        return $this->getCartWithItems($item['cart_id']);
    }

    public function removeItem(string $itemId): array
    {
        $item = $this->db->fetch('SELECT cart_id FROM cart_items WHERE id = ?', [$itemId]);
        $this->db->query('DELETE FROM cart_items WHERE id = ?', [$itemId]);
        return $this->getCartWithItems($item['cart_id'] ?? '');
    }

    public function getCartWithItems(?string $cartId = null): array
    {
        if (!$cartId) {
            $c      = $this->db->fetch('SELECT id FROM carts WHERE id = ?', [Session::get('cart_id')]);
            $cartId = $c['id'] ?? null;
        }
        if (!$cartId) return ['items' => [], 'subtotal' => 0, 'item_count' => 0];

        $items = $this->db->fetchAll(
            "SELECT ci.id, ci.quantity, ci.unit_price,
                    p.id AS product_id, p.slug, p.title,
                    b.name AS brand_name,
                    v.size, v.color, v.color_hex,
                    (SELECT url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) AS image
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             LEFT JOIN brands b             ON b.id = p.brand_id
             LEFT JOIN product_variants v   ON v.id = ci.variant_id
             WHERE ci.cart_id = ?
             ORDER BY ci.added_at DESC",
            [$cartId]
        );

        $subtotal   = array_sum(array_map(fn($i) => $i['unit_price'] * $i['quantity'], $items));
        $item_count = array_sum(array_column($items, 'quantity'));

        return compact('items', 'subtotal', 'item_count');
    }

    public function clear(string $cartId): void
    {
        $this->db->query('DELETE FROM cart_items WHERE cart_id = ?', [$cartId]);
    }
}
