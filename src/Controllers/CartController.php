<?php

declare(strict_types=1);

namespace HeritageEdit\Controllers;

use HeritageEdit\Core\Request;
use HeritageEdit\Core\Response;
use HeritageEdit\Models\Cart;

final class CartController
{
    private Cart $cart;

    public function __construct()
    {
        $this->cart = new Cart();
    }

    public function show(Request $request): void
    {
        $cartData = $this->cart->getCartWithItems();
        Response::view('pages/cart', ['cart' => $cartData]);
    }

    public function add(Request $request): void
    {
        $data      = $request->json();
        $productId = $data['product_id'] ?? null;
        $variantId = $data['variant_id'] ?? null;
        $qty       = (int) ($data['quantity'] ?? 1);

        if (!$productId) {
            Response::json(['error' => 'product_id required'], 422);
        }

        try {
            $cart = $this->cart->addItem($productId, $variantId, max(1, $qty));
            Response::json(['success' => true, 'cart' => $cart]);
        } catch (\InvalidArgumentException $e) {
            Response::json(['error' => $e->getMessage()], 404);
        }
    }

    public function update(Request $request): void
    {
        $data   = $request->json();
        $itemId = $data['item_id'] ?? null;
        $qty    = (int) ($data['quantity'] ?? 0);

        if (!$itemId) {
            Response::json(['error' => 'item_id required'], 422);
        }

        try {
            $cart = $this->cart->updateItem($itemId, $qty);
            Response::json(['success' => true, 'cart' => $cart]);
        } catch (\InvalidArgumentException $e) {
            Response::json(['error' => $e->getMessage()], 404);
        }
    }

    public function remove(Request $request): void
    {
        $data   = $request->json();
        $itemId = $data['item_id'] ?? null;

        if (!$itemId) {
            Response::json(['error' => 'item_id required'], 422);
        }

        $cart = $this->cart->removeItem($itemId);
        Response::json(['success' => true, 'cart' => $cart]);
    }

    public function get(Request $request): void
    {
        Response::json($this->cart->getCartWithItems());
    }
}
