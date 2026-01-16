<?php

use Helpers\AssetHelper;

if (!defined('WPINC')) {
    die;
}

function lottopark_enqueue_styles()
{
    $stylePath = AssetHelper::mix('css/app.min.css', AssetHelper::TYPE_WORDPRESS);
    $jsVendorPath = AssetHelper::mix('js/vendor.min.js', AssetHelper::TYPE_WORDPRESS);
    $jsAppPath = AssetHelper::mix('js/app.min.js', AssetHelper::TYPE_WORDPRESS);

    wp_enqueue_style('base-theme-styles', $stylePath);

    add_filter('style_loader_tag', 'preload_filter', 10, 2);
    function preload_filter($html, $handle) {
        if (strcmp($handle, 'base-theme-styles') == 0) {
            $html = str_replace("rel='stylesheet'", "rel='preload' as='style' onload='this.rel=\"stylesheet\"'", $html);
        }
        return $html;
    }

    wp_enqueue_script('vendor-theme-scripts', $jsVendorPath, ['jquery', 'masonry'], false, true);
    wp_enqueue_script('base-theme-scripts', $jsAppPath, [], false, true);
}

add_action('wp_enqueue_scripts', 'lottopark_enqueue_styles', 11);

if (!function_exists('lottopark_theme_setup')) {
    function lottopark_theme_setup() {
        $defaults = array(
            'width'                  => 276,
            'height'                 => 53,
            'header-text'            => false,
            'random-default'         => false
        );
        add_theme_support('custom-header', $defaults);
    }
}

add_action('after_setup_theme', 'lottopark_theme_setup');
