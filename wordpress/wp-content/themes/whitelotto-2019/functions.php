<?php

use Helpers\AssetHelper;

if (!defined('WPINC')) {
    die;
}

define('ICL_DONT_LOAD_NAVIGATION_CSS', true);
define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);
define('ICL_DONT_LOAD_LANGUAGES_JS', true);

if (!function_exists('whitelotto_join_theme_setup')) {
    function whitelotto_join_theme_setup()
    {
        add_theme_support('title-tag');
        redirect_ips();
    }
}

add_action('after_setup_theme', 'whitelotto_join_theme_setup');

function whitelotto_join_theme_scripts()
{
    $stylePath = AssetHelper::mix('css/app.min.css', AssetHelper::TYPE_WORDPRESS);
    $jsPath = AssetHelper::mix('js/main.min.js', AssetHelper::TYPE_WORDPRESS);

    wp_enqueue_style('whitelotto-join-theme-style', $stylePath);
    wp_enqueue_script('whitelotto-join-theme-script', $jsPath, ['jquery'], false, true);
}

add_action('wp_enqueue_scripts', 'whitelotto_join_theme_scripts');

// Include custom navwalker
require_once('bs4navwalker.php');

// Register WordPress nav menu
register_nav_menu('primary', 'Primary menu');

//Remove JQuery migrate
function remove_jquery_migrate( $scripts ) {
    if ( ! is_admin() && isset( $scripts->registered['jquery'] ) ) {
        $script = $scripts->registered['jquery'];

        if ( $script->deps ) { // Check whether the script has any dependencies
            $script->deps = array_diff( $script->deps, array( 'jquery-migrate' ) );
        }
    }
}

add_action( 'wp_default_scripts', 'remove_jquery_migrate' );

/**
 * Disable search functionality
 */
function whitelotto_disable_search() {
    if (is_search()) {
        wp_redirect(home_url('/'));
        exit;
    }
}
add_action('template_redirect', 'whitelotto_disable_search');

/**
 * Handle ip redirects
 */
function redirect_ips() : void
{
    if (defined('WP_CLI')) {
        return;
    }
    $user_ip = Lotto_Security::get_IP();
    $geoip = Lotto_Helper::get_geo_IP_record($user_ip);

    $country_code = null;
    if ($geoip !== false) {
        $country_code = $geoip->country->isoCode;
    }

    if ($country_code !== null) {
        redirect_some_ip_to_lottopark($country_code);
    }
}

function redirect_some_ip_to_lottopark(string $country_code) : void
{
    // It was done cause we were receiving too many forms
    $codes_to_redirect = [
        "IN",
        "TR"
    ];

    if (in_array($country_code, $codes_to_redirect)) {
        // It's temporarily disabled because of page cache
        // exit(wp_redirect('https://lottopark.com'));
    }
}
