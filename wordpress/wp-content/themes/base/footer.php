<?php

use Core\App;
use Fuel\Core\Cookie;
use Helpers\AssetHelper;
use Helpers\CountryHelper;
use Helpers\InfoBoxHelper;
use Helpers\UrlHelper;
use Helpers\Wordpress\LanguageHelper;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

$whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
$is_user = Lotto_Settings::getInstance()->get("is_user");
$isAccountPage = Lotto_Platform::is_page('account');

$lang = LanguageHelper::getCurrentWhitelabelLanguage();
$langcode = substr($lang['code'], 0, 2);
$fileExt = '.png';

/*GET CHILD THEME TRUSTED PARTNERS IMAGE PATH*/
$stylesheetDir = get_stylesheet_directory();

if (IS_CASINO) {
    $pathTrusted = "$stylesheetDir/images/casino_trusted";
} else {
    $pathTrusted =  "$stylesheetDir/images/trusted";
}

if ($langcode != 'en') {
    $langcodePathPart = $pathTrusted . '_' . $langcode;
    if (file_exists($langcodePathPart . $fileExt)) {
        $pathTrusted = $langcodePathPart;
    }
}

$pathToImgTrusted = $pathTrusted . $fileExt;

$image_size = null;

/*IF NOT EXIST, GET IT FROM BASE THEME*/
if (!file_exists($pathToImgTrusted)) {
    $templateDir = get_template_directory();
    if (IS_CASINO) {
        $pathTrusted = "$templateDir/images/casino_trusted";
    } else {
        $pathTrusted = "$templateDir/images/trusted";
    }
    if ($langcode != 'en') {
        $langcodePathPart = $pathTrusted . '_' . $langcode;
        if (file_exists($langcodePathPart . $fileExt)) {
            $pathTrusted = $langcodePathPart;
        }
    }

    $pathToImgTrusted = $pathTrusted . $fileExt;

    if (!empty($pathToImgTrusted)) {
        /* GET IMAGE SIZE AND REPLACE FULL PATH TO URL */
        $image_size_check = getimagesize($pathToImgTrusted);

        if ($image_size_check !== false) {
            $image_size = $image_size_check;

            $trusted_image_url = str_replace(
                get_template_directory(),
                get_template_directory_uri(),
                $pathToImgTrusted
            );
        }
    }
} else {
    /*GET IMAGE SIZE AND REPLACE FULL PATH TO URL*/
    $image_size_check = getimagesize($pathToImgTrusted);

    if ($image_size_check !== false) {
        $image_size = $image_size_check;

        $trusted_image_url = str_replace(
            get_stylesheet_directory(),
            get_stylesheet_directory_uri(),
            $pathToImgTrusted
        );
    }
}

$lottery_bets = Lotto_Settings::getInstance()->get("lottery_bets");

$min_bets = $lottery_bets['min_bets'] ?? 1;
$max_bets = $lottery_bets['max_bets'] ?? 1;

$dialog_wrapper_class = '';
if ((!empty(Input::post('login')) && !Lotto_Platform::is_page('login')) ||
    (!empty(Input::post('register')) && !Lotto_Platform::is_page('signup')) ||
    (!empty(Input::post('lost')) && !Lotto_Platform::is_page('lostpassword'))
) {
    if (!$is_user) {
        $dialog_wrapper_class = ' class="dialog-show"';
    }
} elseif (
    !empty(Input::post('myaccount_remove') &&
        Lotto_Platform::is_page('account')) &&
    $is_user
) {
    $dialog_wrapper_class = ' class="dialog-show"';
}

?>
<footer>
    <div class="footer-logotypes">
        <?php
        if (!empty($image_size)) :
        ?>
            <img width="<?= $image_size[0]; ?>" height="<?= $image_size[1]; ?>" src="<?= $trusted_image_url; ?>" alt="Footer Image" />
        <?php
        endif;
        ?>
    </div>
    <div class="main-width">
        <?php
        $footerPageSlug = IS_CASINO ? 'casino-footer' : 'footer';
        $footerPage = get_post(
            apply_filters(
                'wpml_object_id',
                lotto_platform_get_post_id_by_slug($footerPageSlug),
                'page',
                true
            )
        );
        // key is tag name in content, value is column name in database
        $tags_to_replace_in_footer = [
            'footer' => 'footer',
            'country' => 'country'
        ];

        $footer_content = "";
        if (!empty($footerPage) && !empty($footerPage->post_content)) {
            $footer_content = apply_filters('the_content', $footerPage->post_content);
            $footer_content = apply_filters('replace_wordpress_tags', $footer_content, $tags_to_replace_in_footer);
        }

        echo $footer_content;
        ?>
        <div class="licence-and-language-container">
            <!--    LICENCE    -->
            <?php
            //WHITELABEL_ID => LICENCE_ID
            const SEALS_LICENCE_IDS = [
                '1' => 'ff6015a9-5eaf-44eb-90fa-fb878fb5424c',
                '2' => '7098dc3f-ba71-4ba8-acce-600101ccda0f',
                '3' => '8885147d-8851-43d1-9b95-291e5a011fc1',
                '8' => '795247bc-4f71-438d-8c34-6fc779003827',
                '20' => 'f2adf746-f399-407f-9852-312dcf546e6d',
                '26' => '806c9abc-8f5d-402d-980f-9ee0e8008ad3',
                '14' => 'eb7d7080-671b-4784-9f13-959be95e58ce',
                '12' => '504be089-1345-417f-b74e-33bcd79e9742',
                '4' => '08f7e84d-7db7-437c-90c5-6b19dae0ecb8',
                '25' => '14d4b7d7-5bc2-4c24-a5cf-d1e7025061c0',
                //'24' => '862d51a7-3cc3-4298-a264-86a22f1ba765',
            ];

            /** @var App $app */
            $app = Container::get(App::class);
            $isProduction = $app->isProduction();
            $isLotteryProduction = $isProduction && !IS_CASINO;
            $shouldDisplayCegSeal = $isLotteryProduction && isset(SEALS_LICENCE_IDS[$whitelabel['id']]);
            if ($shouldDisplayCegSeal) :
                $ceg_seal_id = Security::htmlentities(SEALS_LICENCE_IDS[$whitelabel['id']]);
                $script_src = 'https://' . $ceg_seal_id . '.seals-xcm.certria.com/xcm-seal.js';
                ?>
                <div class="ceg-seal-container">
                    <div id="xcm-<?= $ceg_seal_id; ?>" data-xcm-seal-id="<?= $ceg_seal_id; ?>" data-xcm-image-size="128" data-xcm-image-type="basic-small"></div>
                </div>
                <script type="text/javascript" src="<?= $script_src; ?>" defer async></script>
            <?php endif; ?>
            <!--    END LICENCE    -->
            <div class="pull-right">
                <span class="language-label"><?= _("change language"); ?>:</span>
                <div class="lang-select">
                    <?php base_theme_language_switcher(); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="main-width main-width-nav">
        <?php
        $footerMenuOptions = ['theme_location' => IS_CASINO ? 'casino-footer' : 'footer'];
        if (has_nav_menu('footer')) :
        ?>
            <nav id="footer-nav">
                <div>
                    <?php wp_nav_menu($footerMenuOptions); ?>
                </div>
                <?php
                $locations = get_nav_menu_locations();
                $items = wp_get_nav_menu_items($locations['footer']);
                $facebook = get_theme_mod("base_facebook_" . $langcode);
                $twitter = get_theme_mod("base_twitter_" . $langcode);
                ?>
            </nav>
        <?php
        endif;

        if (!empty($facebook) || !empty($twitter)) :
        ?>
            <nav id="social-nav">
                <div>
                    <ul>
                        <?php
                        if (!empty($facebook)) :
                        ?>
                            <li>
                                <a href="<?= UrlHelper::esc_url($facebook); ?>" target="_blank" rel="nofollow" aria-label="Facebook">
                                    <span class="fa fa-brands fa-facebook"></span>
                                </a>
                            </li>
                        <?php
                        endif;

                        if (!empty($twitter)) :
                        ?>
                            <li>
                                <a href="<?= UrlHelper::esc_url($twitter); ?>" target="_blank" rel="nofollow" aria-label="Twitter">
                                    <span class="fa fa-brands fa-twitter"></span>
                                </a>
                            </li>
                        <?php
                        endif;
                        ?>
                    </ul>
                </div>
            </nav>
        <?php
        endif;
        ?>
        <div class="mobile-language-area <?= !has_nav_menu('footer') ? ' mobile-language-mt' : ''; ?>">
            <?php base_theme_language_switcher(true); ?>
        </div>
        <div class="clearfix"></div>
    </div>
</footer>

<?php
$firstVisitPopupExists = false;
$firstVisitPopupAttributes = "";
$firstVisitPopupContent = get_posts(
    array(
        'name'      => 'popup',
        'post_type' => 'page'
    )
);

if (isset($firstVisitPopupContent[0]) && !empty(trim($firstVisitPopupContent[0]->post_content))) {
    $firstVisitPopupTimeout  = $whitelabel['welcome_popup_timeout'] ?? 30;
    $firstVisitPopupAttributes = " data-first-visit='true' data-first-visit-popup-timeout='$firstVisitPopupTimeout'";
    $firstVisitPopupExists = true;
}
?>
<div id="dialog-wrapper" <?= $dialog_wrapper_class; ?>
     data-wrong-line-title="<?= _("Wrong line") ?>"
     data-wrong-line-content="<?= _("At least one of your lines is not properly filled in. Do you want to proceed without incorrect lines?") ?>"
     data-minimum-title="<?= _("Too few lines") ?>"
     data-minimum-content="<?= _("You have chosen too few lines. The minimal amount for this lottery is %1s lines! Do you want to quick pick the lines for you?") ?>"
     data-multiplier-title="<?= _("Incorrect order") ?>"
     data-multiplier-content="<?= _("You have chosen a wrong amount of lines. The chosen lines count should be a multiple of %1s. Do you want to quick pick the rest of the lines for you?") ?>"
     data-minbets-title="<?= _("Minimum bets") ?>"
     data-minbets-content="<?= sprintf(_('You need to choose at least %1d lines for every %2d-lines ticket!'), $min_bets, $max_bets); ?>"
     <?php if (!empty($firstVisitPopupExists)): ?>
       data-first-visit-title="<?= Security::htmlentities($firstVisitPopupContent[0]->post_title); ?>"
       data-first-visit-content="<?= Security::htmlentities($firstVisitPopupContent[0]->post_content); ?>"
       <?= $firstVisitPopupAttributes; ?>
     <?php endif ?>
>

    <?php
    $siteId = get_current_blog_id();
    $newsletterImage = Helpers_File::get_welcome_popup_image($siteId);
    $newsletterImageVisible = !empty($newsletterImage) && $firstVisitPopupExists;
    $dialogClass = $firstVisitPopupExists ? ' class="dialog newsletter"' : ' class="dialog"';
    ?>
    <div id="dialog" <?= $dialogClass; ?>>
        <?php if ($newsletterImageVisible) : ?>
            <div class="dialog-image">
                <img src="<?= UrlHelper::esc_url($newsletterImage); ?>" alt="newsletter">
            </div>
        <?php endif; ?>
        <div>
            <div class="dialog-title">
                <div id="dialog-title-div" class="pull-left set-width hidden-normal"></div>
                <div class="pull-left set-width <?php if (empty(Input::post('myaccount_remove')) && Lotto_Platform::is_page('account')) : echo ' hidden-normal';
                                                endif; ?>">
                    <?php
                    if ($is_user) :
                        echo _("Delete account");
                    endif;
                    ?>
                </div>
                <div class="pull-right">
                    <a id="dialog-close" class="dialog-close" href="#"><span></span></a>
                </div>
            </div>
            <div class="dialog-content hidden-normal">
                <p id="dialog-message"></p>
                <div class="dialog-buttons">
                    <div id="dialog-button-close" class="pull-left">
                        <button type="button" class="btn btn-sm btn-tertiary btn-dialog-close"><?= _("Back") ?></button>
                    </div>
                    <div id="dialog-button-continue" class="pull-right">
                        <button type="button" class="btn btn-sm btn-primary dialog-continue"><?= _("Continue") ?></button>
                    </div>
                    <div id="dialog-button-quickpick" class="pull-right">
                        <button type="button" class="btn btn-sm btn-primary dialog-quickpick"><?= _("Quick Pick") ?></button>
                    </div>
                    <div id="dialog-button-confirm" class="pull-right">
                        <a href="#" class="btn btn-sm btn-primary dialog-confirm"><?= _("OK") ?></a>
                    </div>
                    <?php if ($whitelabel['show_ok_in_welcome_popup']) : ?>
                        <div id="dialog-button-ok" class="pull-right">
                            <button type="button" class="btn btn-sm btn-primary btn-dialog-close"><?= _("OK") ?></button>
                        </div>
                    <?php endif; ?>
                    <div id="dialog-button-url" class="pull-right">
                        <button data-url="<?= lotto_platform_get_permalink_by_slug('account'); ?>tickets/awaiting/" class="btn btn-sm btn-primary dialog-url">
                            <?= _("Claim your bonus ticket here") ?>
                        </button>
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="dialog-content<?= empty(Input::post('myaccount_remove')) && $isAccountPage ? ' hidden-normal' : '' ?>">
                <?php
                $shouldShowRemoveAccountForm = $isAccountPage && $is_user && function_exists("lotto_platform_myaccount_remove");
                if ($shouldShowRemoveAccountForm) {
                    echo lotto_platform_myaccount_remove();
                }
                ?>
            </div>
        </div>
    </div> <!-- #dialog -->
</div>

<?php
// Geobanner for IP in Cameroon on Lottopark
$isGeobannerCameroonEnabled = $whitelabel['name'] === 'LottoPark' && Lotto_Helper::get_best_match_user_country() === 'CM' && !Cookie::get('GeobannerCameroon');
if ($isGeobannerCameroonEnabled):
?>
    <div id="geobanner-cameroon" class="geobanner">
        <div class="geobanner-dialog">
            <div class="geobanner-content">
                <button id="geobanner-close" class="geobanner-close"></button>
                <img class="geobanner-img" src="<?= AssetHelper::mix('images/geobanner/cameroon.png', AssetHelper::TYPE_WORDPRESS, true) ?>" alt="Premierloto">
                <a class="geobanner-link-stretched" href="https://premierloto.cm/?src=PARK" target="_blank"></a>
            </div>
        </div>
    </div>
<?php endif;?>

<?php
// Geobanner for IP in USA on Lottopark
$isGeobannerUsaEnabled = false && (int)$whitelabel['type'] === Whitelabel::TYPE_V1 && Lotto_Helper::get_best_match_user_country() === 'US' && !Cookie::get('GeobannerUSA');
if ($isGeobannerUsaEnabled):
?>
    <div id="geobanner-usa" class="geobanner">
        <div class="geobanner-dialog">
            <div class="geobanner-content">
                <button id="geobanner-close" class="geobanner-close"></button>
                <img class="geobanner-img" src="<?= AssetHelper::mix('images/geobanner/usa-stake.png', AssetHelper::TYPE_WORDPRESS, true) ?>" alt="<?= _('Play now') ?>">
                <a id="geobanner-usa-link" class="geobanner-link-stretched" href="https://stake.us/?c=Wck3lZWK" target="_blank"></a>
            </div>
        </div>
    </div>
<?php endif;?>

<?php
if (!IS_CASINO) {
    echo InfoBoxHelper::generateHtml($title ?? '');
}
wp_footer();
Lotto_Helper::hook("footer-body-end");
?>
</body>
</html>