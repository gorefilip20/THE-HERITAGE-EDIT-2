<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

// ── Bootstrap ────────────────────────────────────────────
require APP_ROOT . '/src/Core/Autoloader.php';

use HeritageEdit\Core\Env;
use HeritageEdit\Core\Session;

Env::load(APP_ROOT . '/.env');

Session::start();

// ── Error handling ───────────────────────────────────────
$debug = filter_var(Env::get('APP_DEBUG', 'false'), FILTER_VALIDATE_BOOLEAN);

set_error_handler(function (int $severity, string $message, string $file, int $line): bool {
    if (!(error_reporting() & $severity)) return false;
    throw new \ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (\Throwable $e) use ($debug): void {
    http_response_code(500);
    if ($debug) {
        echo '<pre style="font:13px monospace;padding:24px;background:#1a1a1a;color:#f87171;">';
        echo '<strong>' . get_class($e) . '</strong>: ' . htmlspecialchars($e->getMessage()) . "\n";
        echo 'in ' . $e->getFile() . ':' . $e->getLine() . "\n\n";
        echo htmlspecialchars($e->getTraceAsString());
        echo '</pre>';
    } else {
        echo 'An unexpected error occurred. Please try again.';
    }
    exit(1);
});

// ── Config helper ────────────────────────────────────────
function config(string $key): mixed
{
    static $cache = [];
    [$file, $rest] = array_pad(explode('.', $key, 2), 2, '');
    if (!isset($cache[$file])) {
        $path = APP_ROOT . "/config/$file.php";
        $cache[$file] = file_exists($path) ? require $path : [];
    }
    if ($rest === '') return $cache[$file];
    $val = $cache[$file];
    foreach (explode('.', $rest) as $seg) {
        $val = $val[$seg] ?? null;
        if ($val === null) return null;
    }
    return $val;
}

// ── Router ───────────────────────────────────────────────
use HeritageEdit\Core\{Router, Request};
use HeritageEdit\Controllers\{ProductController, CartController, CheckoutController, AdminController};

$router  = new Router();
$request = new Request();

// Storefront
$router->get('/',               [ProductController::class, 'home']);
$router->get('/shop',           [ProductController::class, 'catalog']);
$router->get('/product/{slug}', [ProductController::class, 'show']);

// Cart API (JSON)
$router->get('/cart',              [CartController::class, 'show']);
$router->get('/api/cart',          [CartController::class, 'get']);
$router->post('/api/cart/add',     [CartController::class, 'add']);
$router->post('/api/cart/update',  [CartController::class, 'update']);
$router->post('/api/cart/remove',  [CartController::class, 'remove']);

// Checkout
$router->get('/checkout',                 [CheckoutController::class, 'show']);
$router->post('/api/shipping/rates',      [CheckoutController::class, 'shippingRates']);
$router->post('/api/checkout/initialize', [CheckoutController::class, 'initialize']);
$router->get('/checkout/verify',          [CheckoutController::class, 'verify']);
$router->post('/api/webhooks/paystack',   [CheckoutController::class, 'paystackWebhook']);

// Admin
$router->get('/admin',              [AdminController::class, 'dashboard']);
$router->get('/admin/products',     [AdminController::class, 'products']);
$router->get('/admin/products/new', [AdminController::class, 'createProductForm']);
$router->post('/admin/products',    [AdminController::class, 'storeProduct']);
$router->get('/admin/orders',       [AdminController::class, 'orders']);
$router->get('/admin/orders/{id}',  [AdminController::class, 'orderDetail']);

// Products JSON API
$router->get('/api/products', [ProductController::class, 'apiCatalog']);

$router->dispatch($request);
