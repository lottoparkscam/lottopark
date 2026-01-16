<?php
/**
 * The staging database settings. These get merged with the global settings.
 */

return [
	'default' => [
		'type' => 'pdo',
		'connection'  => [
			'dsn'        => 'mysql:host='.$_ENV["DB_PLATFORM_HOST"].';dbname='.$_ENV["DB_PLATFORM_NAME"],
			'username'   => $_ENV['DB_PLATFORM_USER'],
			'password'   => $_ENV['DB_PLATFORM_PASSWORD'],
        ],
		'charset' => 'utf8mb4',
		//'enable_caching' => false,
    ],
	'redis' => [
		'cache' => [
			'hostname' => $_ENV["REDIS_SERVER_HOST"],
			'port' => 6379,
			'database' => $_ENV["REDIS_CACHE_DATABASE"],
			'password' => $_ENV["REDIS_SERVER_PASSWORD"],
		],
		'sessions' => [
			'hostname' => $_ENV["REDIS_SERVER_HOST"],
			'port' => 6379,
			'database' => $_ENV["REDIS_SESSIONS_DATABASE"],
			'password' => $_ENV["REDIS_SERVER_PASSWORD"],
		]
	],
];
