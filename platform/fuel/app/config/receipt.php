<?php

/**
 * These credentials are used in WP Receipt plugin to manage content programmatically.
 */

return [
    'site_url' => $_ENV['RECEIPT_WP_SITE_URL'] ?? null,
    'admin_login' => $_ENV['RECEIPT_ADMIN_LOGIN'] ?? null,
    'admin_password' => $_ENV['RECEIPT_ADMIN_PASSWORD'] ?? null,
    'guzzle_verify' => $_ENV['RECEIPT_GUZZLE_VERIFY'] ?? null,

    'base_auth' => [
        'user' => $_ENV['RECEIPT_BASE_AUTH_USER'] ?? null,
        'password' => $_ENV['RECEIPT_BASE_AUTH_PASSWORD'] ?? null
    ]
];
