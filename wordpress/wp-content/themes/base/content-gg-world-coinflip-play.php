<?php
/* Template Name: GgWorldCoinFlip: Play */

if (!defined('WPINC')) {
    die;
}

use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Presenters\Wordpress\Base\MiniGames\GgWorldCoinFlipGamePresenter;

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$isUser = Lotto_Settings::getInstance()->get('is_user');
$loginSlug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login'));

if (!$isUser) {
    header('Location: ' . $loginSlug);
}

get_header();

$presenter = Container::get(GgWorldCoinFlipGamePresenter::class);
echo $presenter->view();

$ggWorldCoinFlipJs = AssetHelper::mix('js/MiniGames/GgWorldCoinFlip.min.js', AssetHelper::TYPE_WORDPRESS, true);
$ggWorldCoinFlipCss = AssetHelper::mix('css/MiniGames/GgWorldCoinFlip.min.css', AssetHelper::TYPE_WORDPRESS, true);
wp_enqueue_script(
    'GgWorldFlipCoinJs',
    $ggWorldCoinFlipJs,
    [],
    false,
    true
);
wp_enqueue_style('GgWorldFlipCoinCss', $ggWorldCoinFlipCss);

get_footer();
