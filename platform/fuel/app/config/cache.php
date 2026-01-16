<?php
/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.7
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2015 Fuel Development Team
 * @link       http://fuelphp.com
 */

/**
 * NOTICE:
 *
 * If you need to make modifications to the default configuration, copy
 * this file to your app/config folder, and make them in there.
 *
 * This will allow you to upgrade fuel without losing your custom config.
 */

return [

    /**
     * ----------------------------------------------------------------------
     * global settings
     * ----------------------------------------------------------------------
     */

    // default storage driver
    'driver'      => $_ENV['CACHE_DRIVER'],

    // default expiration (null = no expiration)
    'expiration'  => null,

    /**
     * Default content handlers: convert values to strings to be stored
     * You can set them per primitive type or object class like this:
     *   - 'string_handler' 		=> 'string'
     *   - 'array_handler'			=> 'json'
     *   - 'Some_Object_handler'	=> 'serialize'
     */

    /**
     * ----------------------------------------------------------------------
     * storage driver settings
     * ----------------------------------------------------------------------
     */

    // specific configuration settings for the file driver
	'file'  => [
        'path'  =>	'',  // if empty the default will be application/cache/
    ],

    // specific configuration settings for the memcached driver
	'memcached'  => [
        'cache_id'  => $_ENV['CACHE_ID'],  // unique id to distinquish fuel cache items from others stored on the same server(s)
		'servers'   => [   // array of servers and portnumbers that run the memcached service
			'default' => ['host' => $_ENV['CACHE_MEMCACHED_HOST'], 'port' => 11211, 'weight' => 100],
        ],
    ],

    // specific configuration settings for the apc driver
	'apc'  => [
        'cache_id'  => 'fuel',  // unique id to distinquish fuel cache items from others stored on the same server(s)
    ],

    // specific configuration settings for the redis driver
	'redis'  => [
        'database'  => 'cache',  // name of the redis database to use (as configured in config/db.php)
    ],

    // specific configuration settings for the xcache driver
	'xcache'  => [
        'cache_id'  => 'fuel',  // unique id to distinquish fuel cache items from others stored on the same server(s)
    ],

    // api doc cache file path
    'api_doc' => [
        'path' => $_ENV['API_DOC_CACHE_PATH'] ?? '/'
    ]
];
