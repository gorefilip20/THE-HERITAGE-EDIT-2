<?php

declare(strict_types=1);

return [
    'name'    => $_ENV['APP_NAME']  ?? 'THE HERITAGE EDIT',
    'env'     => $_ENV['APP_ENV']   ?? 'production',
    'debug'   => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url'     => $_ENV['APP_URL']   ?? 'http://localhost',
    'key'     => $_ENV['APP_KEY']   ?? '',
    'timezone'=> 'Africa/Lagos',

    'session' => [
        'lifetime' => (int) ($_ENV['SESSION_LIFETIME'] ?? 7200),
        'secure'   => filter_var($_ENV['SESSION_SECURE'] ?? true, FILTER_VALIDATE_BOOLEAN),
    ],

    'pagination' => [
        'per_page' => 24,
    ],

    'currency' => [
        'default'    => 'NGN',
        'supported'  => ['NGN', 'USD', 'GBP', 'EUR'],
        'symbol_map' => ['NGN' => '₦', 'USD' => '$', 'GBP' => '£', 'EUR' => '€'],
    ],
];
