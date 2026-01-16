<?php
/* Template Name: GgWorldRedOrBlue: Play */

if (!defined('WPINC')) {
    die;
}

use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Presenters\Wordpress\Base\MiniGames\GgWorldRedOrBlueGamePresenter;

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$isUser = Lotto_Settings::getInstance()->get('is_user');
$loginSlug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login'));

if (!$isUser) {
    header('Location: ' . $loginSlug);
}

get_header();

$presenter = Container::get(GgWorldRedOrBlueGamePresenter::class);
echo $presenter->view();

$ggWorldRedOrBlueJs = AssetHelper::mix('js/MiniGames/GgWorldRedOrBlue.min.js', AssetHelper::TYPE_WORDPRESS, true);
$ggWorldRedOrBlueCss = AssetHelper::mix('css/MiniGames/GgWorldRedOrBlue.min.css', AssetHelper::TYPE_WORDPRESS, true);
wp_enqueue_script(
    'GgWorldRedOrBlueJs',
    $ggWorldRedOrBlueJs,
    [],
    false,
    true
);
wp_enqueue_style('GgWorldRedOrBlueCss', $ggWorldRedOrBlueCss);

get_footer();
