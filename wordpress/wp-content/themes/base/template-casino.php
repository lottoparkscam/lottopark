<?php

use Helpers\AssetHelper;
use Presenters\Wordpress\Base\Slots\GameListPresenter;

/* Template Name: Casino: List of games */

$casinoUrl = lotto_platform_get_permalink_by_slug('/');

wp_enqueue_script_slick_plugin();
?>
<script>
    // This is the alternative view when this page is loaded in iframe
    window.onload = function() {
        let parentLocation = null;
        try {
            parentLocation = window.parent.location;
        } catch (error) {}

        if (window.location !== parentLocation) {
            window.top.location.href = "<?= $casinoUrl ?>";
            document.body = document.createElement("body");
            document.body.innerHTML = '';
            window.stop();
        }
    }
</script>
<div id="flashmessages"></div>
<?php

if (!defined('WPINC')) {
    die;
}
get_header();
get_template_part('content', 'login-register-box-mobile');

// Displays widgets from casino front page top area
if (is_active_sidebar('casino-frontpage-sidebar-top-id')) {
    dynamic_sidebar('casino-frontpage-sidebar-top-id');
}

/** @var GameListPresenter $gameListPresenter */
$gameListPresenter = Container::get(GameListPresenter::class);
echo $gameListPresenter->view();

$translatedPlayNow = _('Play now');
$translatedPlay = _('Play');
$translatedChoose = ucfirst(_('choose'));
?>
<script>
    window.internalInitUrl = '<?= $gameListPresenter->getCasinoPlayLink() ?>?';
    window.lobbySelectUrl = '<?= $gameListPresenter->getCasinoLobbyLink() ?>?';
    window.translatedPlayNow = '<?= $translatedPlayNow; ?>';
    window.translatedPlay = '<?= $translatedPlay; ?>';
    window.translatedChoose = '<?= $translatedChoose; ?>';
    document.querySelector("body").classList.add("casino");
</script>
<?php

// Displays widgets from casino front page area
if (is_active_sidebar('casino-frontpage-sidebar-bottom-id')) {
    dynamic_sidebar('casino-frontpage-sidebar-bottom-id');
}

/** this script uses const internalInitUrl */
if (!$gameListPresenter->shouldHideListOfGamesOnHomepage()) {
    $casinoGameListJs = AssetHelper::mix('js/slots/GameList.min.js', AssetHelper::TYPE_WORDPRESS, true);
    wp_enqueue_script('game-list', $casinoGameListJs, ['jquery'], false, true);
}

$casinoSliderJsFileName = 'PromoSlider.min.js';
$casinoSliderJs = AssetHelper::mix('js/slots/' . $casinoSliderJsFileName, AssetHelper::TYPE_WORDPRESS, true);
wp_enqueue_script('slots-widget', $casinoSliderJs, ['jquery'], false, true);

get_footer();
