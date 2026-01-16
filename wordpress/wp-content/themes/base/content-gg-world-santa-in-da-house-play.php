<?php
/* Template Name: GgWorldSantaInDaHouse: Play */

if (!defined('WPINC')) {
    die;
}

use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Presenters\Wordpress\Base\MiniGames\GgWorldSantaInDaHouseGamePresenter;

header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$isUser = Lotto_Settings::getInstance()->get('is_user');
$loginSlug = UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login'));

if (!$isUser) {
    header('Location: ' . $loginSlug);
}

get_header();

$presenter = Container::get(GgWorldSantaInDaHouseGamePresenter::class);
echo $presenter->view();

$ggWorldSantaInDaHouseJs = AssetHelper::mix('js/MiniGames/GgWorldSantaInDaHouse.min.js', AssetHelper::TYPE_WORDPRESS, true);
$ggWorldSantaInDaHouseCss = AssetHelper::mix('css/MiniGames/GgWorldSantaInDaHouse.min.css', AssetHelper::TYPE_WORDPRESS, true);
wp_enqueue_script(
    'GgWorldSantaInDaHouseJs',
    $ggWorldSantaInDaHouseJs,
    [],
    false,
    true
);
wp_enqueue_style('GgWorldSantaInDaHouseCss', $ggWorldSantaInDaHouseCss);

get_footer();
