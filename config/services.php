<?php

declare(strict_types=1);

return [
    'paystack' => [
        'public_key'      => $_ENV['PAYSTACK_PUBLIC_KEY']     ?? '',
        'secret_key'      => $_ENV['PAYSTACK_SECRET_KEY']     ?? '',
        'webhook_secret'  => $_ENV['PAYSTACK_WEBHOOK_SECRET'] ?? '',
        'base_url'        => 'https://api.paystack.co',
        'initialize_url'  => 'https://api.paystack.co/transaction/initialize',
        'verify_url'      => 'https://api.paystack.co/transaction/verify',
    ],

    'easypost' => [
        'api_key'  => $_ENV['EASYPOST_API_KEY'] ?? '',
        'base_url' => 'https://api.easypost.com/v2',
    ],

    'anthropic' => [
        'api_key'  => $_ENV['ANTHROPIC_API_KEY'] ?? '',
        'model'    => $_ENV['ANTHROPIC_MODEL']   ?? 'claude-sonnet-4-6',
        'base_url' => 'https://api.anthropic.com/v1',
        'max_tokens' => 2000,
    ],

    'mail' => [
        'host'      => $_ENV['MAIL_HOST']      ?? 'smtp.postmarkapp.com',
        'port'      => (int) ($_ENV['MAIL_PORT'] ?? 587),
        'user'      => $_ENV['MAIL_USER']      ?? '',
        'pass'      => $_ENV['MAIL_PASS']      ?? '',
        'from'      => $_ENV['MAIL_FROM']      ?? 'noreply@theheritageedit.com',
        'from_name' => $_ENV['MAIL_FROM_NAME'] ?? 'The Heritage Edit',
    ],
];
