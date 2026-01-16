<?php
/* Template Name: GgWorldTicTacBoo: Play */

if (!defined('WPINC')) {
    die;
}

use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Presenters\Wordpress\Base\MiniGames\GgWorldTicTacBooGamePresenter;

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$isUser = Lotto_Settings::getInstance()->get('is_user');
$loginSlug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login'));

if (!$isUser) {
    header('Location: ' . $loginSlug);
}

get_header();

$presenter = Container::get(GgWorldTicTacBooGamePresenter::class);
echo $presenter->view();

$ggWorldTicTacBooJs = AssetHelper::mix('js/MiniGames/GgWorldTicTacBoo.min.js', AssetHelper::TYPE_WORDPRESS, true);
$ggWorldTicTacBooCss = AssetHelper::mix('css/MiniGames/GgWorldTicTacBoo.min.css', AssetHelper::TYPE_WORDPRESS, true);
wp_enqueue_script(
    'GgWorldTicTacBooJs',
    $ggWorldTicTacBooJs,
    [],
    false,
    true
);
wp_enqueue_style('GgWorldFlipCoinCss', $ggWorldTicTacBooCss);

get_footer();
