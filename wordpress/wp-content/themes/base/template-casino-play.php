<?php
/* Template Name: Casino: Play */

if (!defined('WPINC')) {
    die;
}

use Helpers\AssetHelper;
use Presenters\Wordpress\Base\Slots\GamePlayPresenter;

get_header();
get_template_part('content', 'login-register-box-mobile');

$presenter = Container::get(GamePlayPresenter::class);
echo $presenter->view();

$casinoPlayJs = AssetHelper::mix('js/slots/CasinoPlay.min.js', AssetHelper::TYPE_WORDPRESS, true);
wp_enqueue_script(
    'CasinoPlay',
    $casinoPlayJs,
    ['jquery'],
    false,
    true
);

get_footer();
