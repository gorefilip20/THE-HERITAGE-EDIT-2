<?php

declare(strict_types=1);

namespace HeritageEdit\Controllers;

use HeritageEdit\Core\Request;
use HeritageEdit\Core\Response;
use HeritageEdit\Core\Session;
use HeritageEdit\Models\Cart;
use HeritageEdit\Models\Order;
use HeritageEdit\Services\PaystackService;
use HeritageEdit\Services\ShippingService;

final class CheckoutController
{
    private Cart $cart;
    private Order $order;
    private PaystackService $paystack;
    private ShippingService $shipping;

    public function __construct()
    {
        $this->cart     = new Cart();
        $this->order    = new Order();
        $this->paystack = new PaystackService();
        $this->shipping = new ShippingService();
    }

    /** GET /checkout */
    public function show(Request $request): void
    {
        $cartData = $this->cart->getCartWithItems();
        if (empty($cartData['items'])) {
            Response::redirect('/cart');
        }

        $config = require __DIR__ . '/../../config/services.php';
        Response::view('pages/checkout', [
            'cart'              => $cartData,
            'paystack_pub_key'  => $config['paystack']['public_key'],
        ]);
    }

    /** POST /api/shipping/rates */
    public function shippingRates(Request $request): void
    {
        $data    = $request->json();
        $country = strtoupper($data['country'] ?? 'NG');
        $weight  = (int) ($data['weight_grams'] ?? 500);

        $rates       = $this->shipping->getRates($data, $weight);
        $landedCost  = $this->shipping->calculateLandedCost(
            (float) ($data['subtotal'] ?? 0),
            $country
        );

        Response::json(['rates' => $rates, 'landed_cost' => $landedCost]);
    }

    /** POST /api/checkout/initialize */
    public function initialize(Request $request): void
    {
        $data     = $request->json();
        $cartData = $this->cart->getCartWithItems();

        if (empty($cartData['items'])) {
            Response::json(['error' => 'Cart is empty'], 422);
        }

        $email     = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
        if (!$email) {
            Response::json(['error' => 'Valid email required'], 422);
        }

        $currency      = strtoupper($data['currency'] ?? 'NGN');
        $subtotal      = (float) $cartData['subtotal'];
        $shippingCost  = (float) ($data['shipping_cost']  ?? 0);
        $dutiesTaxes   = (float) ($data['duties_taxes']   ?? 0);
        $discount      = (float) ($data['discount_amount'] ?? 0);
        $total         = $subtotal + $shippingCost + $dutiesTaxes - $discount;

        try {
            // Create the order (pending payment)
            $orderId = $this->order->createFromCart($cartData, array_merge($data, [
                'user_id'      => Session::userId(),
                'guest_email'  => $email,
                'subtotal'     => $subtotal,
                'shipping_cost'=> $shippingCost,
                'duties_taxes' => $dutiesTaxes,
                'discount_amount' => $discount,
                'currency'     => $currency,
            ]));

            // Initialize Paystack
            $reference  = PaystackService::generateReference();
            $amountKobo = PaystackService::toSubunit($total, $currency);

            $txData = $this->paystack->initialize(
                email: $email,
                amountKobo: $amountKobo,
                reference: $reference,
                currency: $currency,
                metadata: ['order_id' => $orderId, 'order_total' => $total]
            );

            // Store reference in session for verification
            Session::set('pending_order_id', $orderId);
            Session::set('pending_reference', $reference);

            Response::json([
                'success'           => true,
                'order_id'          => $orderId,
                'reference'         => $reference,
                'authorization_url' => $txData['authorization_url'],
                'access_code'       => $txData['access_code'],
            ]);
        } catch (\Throwable $e) {
            error_log('[Checkout] Init error: ' . $e->getMessage());
            Response::json(['error' => 'Payment initialization failed. Please try again.'], 500);
        }
    }

    /** GET /checkout/verify?reference=xxx */
    public function verify(Request $request): void
    {
        $reference = $request->get('reference', '');
        $orderId   = Session::get('pending_order_id');

        if (!$reference || !$orderId) {
            Response::redirect('/cart');
        }

        try {
            $tx = $this->paystack->verify($reference);

            if ($tx['status'] === 'success') {
                $this->order->updatePaymentStatus($orderId, 'paid', $reference);

                // Clear cart
                $cartId = Session::get('cart_id');
                if ($cartId) {
                    $this->cart->clear($cartId);
                }

                Session::forget('pending_order_id');
                Session::forget('pending_reference');
                Session::forget('cart_id');

                $order = $this->order->findById($orderId);
                Response::view('pages/order-confirmation', ['order' => $order]);
            } else {
                Response::redirect('/checkout?payment_failed=1');
            }
        } catch (\Throwable $e) {
            error_log('[Checkout] Verify error: ' . $e->getMessage());
            Response::redirect('/checkout?payment_failed=1');
        }
    }

    /** POST /api/webhooks/paystack */
    public function paystackWebhook(Request $request): void
    {
        $payload   = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_X_PAYSTACK_SIGNATURE'] ?? '';

        if (!$this->paystack->validateWebhook($payload, $signature)) {
            Response::json(['error' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true);
        if (($event['event'] ?? '') === 'charge.success') {
            $meta    = $event['data']['metadata'] ?? [];
            $orderId = $meta['order_id'] ?? null;
            if ($orderId) {
                $this->order->updatePaymentStatus($orderId, 'paid', $event['data']['reference']);
            }
        }

        http_response_code(200);
        exit;
    }
}
