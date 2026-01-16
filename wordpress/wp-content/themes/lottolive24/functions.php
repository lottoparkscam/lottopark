<?php

use Helpers\AssetHelper;

if (!defined('WPINC')) {
    die;
}

function lottolive24_enqueue_styles()
{
    $stylePath = AssetHelper::mix('css/app.min.css', AssetHelper::TYPE_WORDPRESS);
    $jsVendorPath = AssetHelper::mix('js/vendor.min.js', AssetHelper::TYPE_WORDPRESS);
    $jsAppPath = AssetHelper::mix('js/app.min.js', AssetHelper::TYPE_WORDPRESS);

    wp_enqueue_style('base-theme-styles', $stylePath);
    wp_enqueue_script('vendor-theme-scripts', $jsVendorPath, ['jquery', 'masonry'], false, true);
    wp_enqueue_script('base-theme-scripts', $jsAppPath, [], false, true);
}

add_action('wp_enqueue_scripts', 'lottolive24_enqueue_styles', 11);