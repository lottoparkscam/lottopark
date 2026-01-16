<?php

/**
 * Part of the Fuel framework.
 *
 * @package    Fuel
 * @version    1.8
 * @author     Fuel Development Team
 * @license    MIT License
 * @copyright  2010 - 2016 Fuel Development Team
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

$domain = Lotto_Helper::getWhitelabelDomainFromUrl();
$domainWithoutDots = str_replace('.', '', $domain);

return [
    /**
     * global configuration
    */

    // set it to false to prevent the static session from auto-initializing, know that it might make your session
    // expire sooner because it's not updated when it's not used. note that auto-initializing always loads the default driver
    'auto_initialize'   => true,

    // if no session type is requested, use the default
    'driver'            => 'redis',

    // check for an IP address match after loading the cookie (optional, default = false)
    'match_ip'          => false,

    // check for a user agent match after loading the cookie (optional, default = true)
    'match_ua'          => true,

    // cookie domain  (optional, default = '')
    'cookie_domain'     => '',

    // cookie path  (optional, default = '/')
    'cookie_path'       => '/',

    // cookie http_only flag  (optional, default = use the cookie class default)
    'cookie_http_only'  => true,

    // whether or not to encrypt the session cookie (optional, default is true)
    'encrypt_cookie'    => true,

    // if true, the session expires when the browser is closed (optional, default = false)
    'expire_on_close'   => false,

    // session expiration time, <= 0 means 2 years! (optional, default = 2 hours) numbers of seconds
    'expiration_time'   => Helpers_Time::DAY_IN_SECONDS,

    // session ID rotation time  (optional, default = 300) Set to false to disable rotation
    'rotation_time'     => false,

    // default ID for flash variables  (optional, default = 'flash')
    'flash_id'          => 'flash',

    // if false, expire flash values only after it's used  (optional, default = true)
    'flash_auto_expire' => false,

    // if true, a get_flash() automatically expires the flash data
    'flash_expire_after_get' => true,

    // for requests that don't support cookies (i.e. flash), use this POST variable to pass the cookie to the session driver
    'post_cookie_name'  => '',

    // for requests in which you don't want to use cookies, use an HTTP header by this name to pass the cookie to the session driver
    'http_header_name' => 'Session-Id',

    // if false, no cookie will be added to the response send back to the client
    'enable_cookie' => true,

    // if true, session data will be synced with PHP's native $_SESSION, to allow easier integration of third-party components
    'native_emulation'  => false,

    /**
     * specific driver configurations. to override a global setting, just add it to the driver config with a different value
    */

    // specific configuration settings for redis based sessions
    'redis'         => [
        // name of the session cookie for redis based sessions
        'cookie_name'       => "{$domainWithoutDots}_lottorsesid",
        'cookie_domain'     => ".{$domain}",
        'database'          => 'sessions',              // name of the redis database to use (as configured in config/db.php)
    ],
];
