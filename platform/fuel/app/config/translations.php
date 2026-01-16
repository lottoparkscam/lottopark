<?php

return [
    'wp_translations' => [
        'dbhost' => $_ENV['WP_TRANSLATIONS_HOST'],
        'dbname' => $_ENV['WP_TRANSLATIONS_NAME'],
        'dbuser' => $_ENV['WP_TRANSLATIONS_USER'],
        'dbpassword' => $_ENV['WP_TRANSLATIONS_PASSWORD'],
    ],
    'wp' => [
        'dbhost' => $_ENV['WP_HOST'],
        'dbname' => $_ENV['WP_NAME'],
        'dbuser' => $_ENV['WP_USER'],
        'dbpassword' => $_ENV['WP_PASSWORD'],
        'path' => $_ENV['WP_PATH'],
        'url' => $_ENV['WP_URL'],
        'admin' => $_ENV['WP_ADMIN'],
    ],
];
