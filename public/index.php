<?php

declare(strict_types=1);

define('APP_ROOT', dirname(__DIR__));

require APP_ROOT . '/vendor/autoload.php';

// Load environment
$dotenv = Dotenv\Dotenv::createImmutable(APP_ROOT);
$dotenv->safeLoad();

// Start session
HeritageEdit\Core\Session::start();

// Error handling (dev mode)
if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
    $whoops = new Whoops\Run();
    $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
    $whoops->register();
}

// Config helper
function config(string $key): mixed {
    static $configs = [];
    [$file, $subkey] = explode('.', $key, 2) + [1 => ''];
    if (!isset($configs[$file])) {
        $path = APP_ROOT . "/config/$file.php";
        $configs[$file] = file_exists($path) ? require $path : [];
    }
    if ($subkey === '') return $configs[$file];
    $parts = explode('.', $subkey);
    $val   = $configs[$file];
    foreach ($parts as $p) {
        $val = $val[$p] ?? null;
        if ($val === null) return null;
    }
    return $val;
}

// ─── Routes ──────────────────────────────────────────
use HeritageEdit\Core\{Router, Request};
use HeritageEdit\Controllers\{ProductController, CartController, CheckoutController, AdminController};

$router  = new Router();
$request = new Request();

// Storefront
$router->get('/',                [ProductController::class, 'home']);
$router->get('/shop',            [ProductController::class, 'catalog']);
$router->get('/product/{slug}',  [ProductController::class, 'show']);

// Cart API
$router->get('/cart',                [CartController::class, 'show']);
$router->get('/api/cart',            [CartController::class, 'get']);
$router->post('/api/cart/add',       [CartController::class, 'add']);
$router->post('/api/cart/update',    [CartController::class, 'update']);
$router->post('/api/cart/remove',    [CartController::class, 'remove']);

// Checkout
$router->get('/checkout',                   [CheckoutController::class, 'show']);
$router->post('/api/shipping/rates',         [CheckoutController::class, 'shippingRates']);
$router->post('/api/checkout/initialize',    [CheckoutController::class, 'initialize']);
$router->get('/checkout/verify',             [CheckoutController::class, 'verify']);
$router->post('/api/webhooks/paystack',      [CheckoutController::class, 'paystackWebhook']);

// Admin
$router->get('/admin',                   [AdminController::class, 'dashboard']);
$router->get('/admin/products',          [AdminController::class, 'products']);
$router->get('/admin/products/new',      [AdminController::class, 'createProductForm']);
$router->post('/admin/products',         [AdminController::class, 'storeProduct']);
$router->get('/admin/orders',            [AdminController::class, 'orders']);
$router->get('/admin/orders/{id}',       [AdminController::class, 'orderDetail']);

// API
$router->get('/api/products',            [ProductController::class, 'apiCatalog']);

$router->dispatch($request);
