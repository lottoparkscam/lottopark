<?php

use Fuel\Core\File;
use Fuel\Tasks\Seeders\Wordpress\AddGgWorldCoinFlipPage;
use Fuel\Tasks\Seeders\Wordpress\FaireumDepositAndWithdrawalInstructionsPage;
use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Models\Whitelabel;
use Helpers\Wordpress\LanguageHelper;
use Repositories\WhitelabelOAuthClientRepository;
use Services\Logs\FileLoggerService;

if (!defined('WPINC')) {
    die;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <?php
    $gtm_key = 'analytics' . (IS_CASINO ? '_casino' : '');
    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
    if (!empty($whitelabel[$gtm_key])) {
        ?>
        <!-- Google Tag Manager -->
        <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo $whitelabel[$gtm_key] ?>');</script>
        <!-- End Google Tag Manager -->
        <?php
    }
    ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:400,600,700&subset=latin-ext&display=optional" crossorigin>
    <?php if (str_contains($_SERVER['REQUEST_URI'], AddGgWorldCoinFlipPage::COINFLIP_SLUG)): ?>
      <link href="https://fonts.googleapis.com/css2?family=Rubik:ital,wght@0,300..900;1,300..900&display=swap" rel="stylesheet">
    <?php endif; ?>
    <meta http-equiv="Content-Type" content="<?php bloginfo('html_type'); ?>;charset=<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="format-detection" content="telephone=no">
    <meta name="ahrefs-site-verification" content="d72505b8831567460a1973a65feda87291c88a59f9c2dbe87df04f98610c64f5">
    <link rel="alternate" type="application/rss+xml" title="<?php bloginfo('name'); ?> &raquo; Feed" href="<?= UrlHelper::esc_url(lotto_platform_home_url('/')); ?>?feed=rss2" />
    <?php
    $font = get_theme_mod("base_fonts_scheme");
    wp_head();
    ?>
    <style type="text/css">
        <?php
        $total_widgets = wp_get_sidebars_widgets();

        foreach ($total_widgets as $sidebar => $widgets) {
            $counter = [];

            if (empty($widgets) || count($widgets) == 0) {
                continue;
            }

            foreach ($widgets as $widget) {
                $type = explode("-", $widget);

                if ($type[0] != 'lotto_platform_widget_featured') {
                    continue;
                }

                $lotto_feature_background_image = get_theme_mod("base_" . $widget . "_bg_image");
                $lotto_feature_background_image_mobile = get_theme_mod("base_" . $widget . "_bg_image_mobile");
                $lotto_feature_background_image_large = get_theme_mod("base_" . $widget . "_large_bg_image");
                $lotto_feature_background_image_mobile_large = get_theme_mod("base_" . $widget . "_large_bg_image_mobile");
                $settings_featured = new Lotto_Widget_Featured();
                $settings_table = $settings_featured->get_settings();
                $settings = $settings_table[$type[1]];
                $lotto_feature_type = !empty($settings['type']) ? $settings['type'] : Lotto_Widget_Featured::TYPE_SMALL;

                if (empty($lotto_feature_background_image) && empty($lotto_feature_background_image_large)) {
                    continue;
                }

                $lotto_feature_background_image_url = 'none';
                if (!empty($lotto_feature_background_image)) {
                    $lotto_feature_background_image_url = "url('" . $lotto_feature_background_image . "')";
                }

                $lotto_feature_background_image_large_url = 'none';
                if (!empty($lotto_feature_background_image_large)) {
                    $lotto_feature_background_image_large_url = "url('" . $lotto_feature_background_image_large . "')";
                }

                $lotto_feature_background_image_mobile_url = 'none';
                if (!empty($lotto_feature_background_image_mobile)) {
                    $lotto_feature_background_image_mobile_url = "url('" . $lotto_feature_background_image_mobile . "')";
                }

                $lotto_feature_background_image_mobile_large_url = 'none';
                if (!empty($lotto_feature_background_image_mobile_large)) {
                    $lotto_feature_background_image_mobile_large_url = "url('" . $lotto_feature_background_image_mobile_large . "')";
                }

                if (
                    (int)$lotto_feature_type === Lotto_Widget_Featured::TYPE_SMALL ||
                    (int)$lotto_feature_type === Lotto_Widget_Featured::TYPE_WITH_BACKGROUND
                ) :
        ?>#<?= $widget; ?> .widget-featured-wrapper-small .widget-featured-content {
            background-image: <?= $lotto_feature_background_image_url; ?>;
        }

        #<?= $widget; ?> .widget-featured-wrapper-small.widget-featured-bg-type-large {
            background-image: <?= $lotto_feature_background_image_large_url; ?>;
        }

        @media screen and (max-width: 800px) {
            #<?= $widget; ?> .widget-featured-wrapper-small .widget-featured-content {
                background-image: <?= $lotto_feature_background_image_mobile_url; ?>;
            }

            #<?= $widget; ?> .widget-featured-wrapper-small.widget-featured-bg-type-large {
                background-image: <?= $lotto_feature_background_image_mobile_large_url; ?>;
            }
        }

        <?php
                else :
        ?>#<?= $widget; ?> .widget-featured-wrapper-large {
            background-image: <?= $lotto_feature_background_image_url; ?>;
        }

        @media screen and (max-width: 800px) {
            #<?= $widget; ?> .widget-featured-wrapper-large {
                background-image: <?= $lotto_feature_background_image_mobile_url; ?>;
            }
        }

        <?php
                endif;
            }
        }

        $lotto_page_background_image = get_theme_mod("base_page_bg_image");

        $lotto_page_background_image_size = get_lotto_image_size($lotto_page_background_image);

        if (!empty($lotto_page_background_image_size)) :
            $lotto_page_background_image_url = "url('" .
                $lotto_page_background_image .
                "')";
        ?>div.page-header,
        div.post-header {
            background-image: <?= $lotto_page_background_image_url; ?>;
            height: <?= $lotto_page_background_image_size[1]; ?>px;
        }

        <?php
        endif;
        ?>
    </style>
    <?php
    $trackingJs = AssetHelper::mix('js/Tracking.min.js', AssetHelper::TYPE_WORDPRESS, true);
    wp_enqueue_script(
        'Tracking',
        $trackingJs,
        ['jquery'],
        false,
        true
    );
    $whitelabelOAuthClientRepository = Container::get(WhitelabelOAuthClientRepository::class);

    if ($whitelabel['theme'] !== 'lottopark' && Lotto_Platform::is_page('how-to-buy-gg-token')) {
        echo '<meta name="robots" content="noindex, nofollow">';
    }

    $isTermsPage = Lotto_Platform::is_page('terms');
    $isPrivacyPage = Lotto_Platform::is_page('privacy');

    if ($isTermsPage || $isPrivacyPage):
    ?>
        <style>
            @media print {
                body > header,
                body > nav,
                body > footer,
                .btn-print {display: none !important}
            }
        </style>
    <?php 
    endif;

    Lotto_Helper::hook("header-head-end");
    ?>
</head>
<?php
$style = get_theme_mod("base_colors_scheme");

$additional_classes = array();
if (
    !empty($style) &&
    in_array($style, array("orange-green", "orange-blue", "default"))
) {
    $additional_classes[] = $style;
} else {
    $additional_classes[] = "orange-green";
}

if (
    !empty($font) &&
    in_array($font, array("ptsans", "signika", "source"))
) {
    $additional_classes[] = 'font-' . $font;
} else {
    $additional_classes[] = 'font-source';
}

$currentLanguageWithLocale = Lotto_Settings::getInstance()->get("locale_default");
?>

<body <?php
        body_class($additional_classes);
        echo ' data-family="' . Lotto_Settings::getInstance()->get("google_font_family") . '"';
        echo ' data-theme="' . $whitelabel['theme'] . '"';
        ?>>
    <?php
    if (!empty($whitelabel[$gtm_key])) {
        ?>
            <!-- Google Tag Manager (noscript) -->
            <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo $whitelabel[$gtm_key] ?>"
            height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
            <!-- End Google Tag Manager (noscript) -->
        <?php
    }

    Lotto_Helper::hook("header-body-start");
    $header = get_theme_mod("base_header_type");
    $casinoPrefixes = UrlHelper::getCasinoPrefixes();

    global $shouldTriggerUserViewItemEvent;
    if (!empty($shouldTriggerUserViewItemEvent)):
        global $userViewItemEventPageName;
    ?>
    <script>
        window.shouldTriggerUserViewItemEvent = true;
        window.pageName = '<?= $userViewItemEventPageName ?>';
    </script>
    <?php
    endif;
    $parentPost = !empty($post) ? get_post($post->post_parent) : '';
    $parentPostSlug = $parentPost->post_name ?? '';
    $parentPostDefaultSlug = !empty($parentPost) ?
        get_post(
                apply_filters(
                        'wpml_object_id',
                        $parentPost->ID,
                        'page',
                        true,
                        LanguageHelper::DEFAULT_LANGUAGE_SHORTCODE
                )
        )->post_name :
        '';
    $currentPageDefaultSlug = !empty($post) ? get_post(
            apply_filters(
                    'wpml_object_id',
                    $post->ID,
                    'page',
                    true,
                    LanguageHelper::DEFAULT_LANGUAGE_SHORTCODE
            )
    )->post_name : '';
    ?>
    <script>
        window.lotterySlug = '<?= $post->post_name ?? '' ?>';
        window.orderPathInUserLanguage = '<?= lotto_platform_get_permalink_by_slug('order') ?>';
        window.currentLanguageWithLocale = '<?= $currentLanguageWithLocale ?>';
        window.IS_RIGHT_TO_LEFT_LANGUAGE = <?= (int) is_rtl() ?>;
        window.casinoPrefixes = <?= json_encode($casinoPrefixes) ?>;
        window.casinoPrefix = '<?= UrlHelper::getCurrentCasinoPrefix() ?>';
        window.parentSlug = '<?= $parentPostDefaultSlug ?>';
        window.currentPageSlug = '<?= $currentPageDefaultSlug ?>';
    </script>
    <header class="header-<?= $header; ?>">
        <div class="main-width">

            <?php 
            if (is_front_page()):
                $is_h1_logo_removed = get_theme_mod('base_logotype_h1_removed');
                echo ($is_h1_logo_removed) ? '<div class="header-logo">' : '<h1>';
            else:
                echo '<div class="header-logo">';
            endif;
            ?>

            <?php if (IS_CASINO):?>
                <a href="<?= UrlHelper::esc_url(lotto_platform_home_url('/'));?>" rel="home">
                    <img src="<?= UrlHelper::esc_url(get_stylesheet_directory_uri() . '/images/casino-logo.png');?>" height="53" alt="<?php bloginfo('name');?>" title="<?php bloginfo('name');?> &#8211; <?php bloginfo('description');?>">
                </a>
            <?php else:?>
                <a href="<?= UrlHelper::esc_url(lotto_platform_home_url('/'));?>" rel="home">
                    <img 
                        <?php echo (File::exists(get_stylesheet_directory() . '/images/logo-2x.png')) ? 'srcset="' . UrlHelper::esc_url(get_stylesheet_directory_uri() . '/images/logo-2x.png?v=2.0.2.8') .' 2x"': null;?>
                        src="<?= UrlHelper::esc_url(get_stylesheet_directory_uri() . '/images/logo.png');?>?v=2.0.2.8"
                        height="53"
                        alt="<?php bloginfo('name');?>" 
                        title="<?php bloginfo('name');?> &#8211; <?php bloginfo('description');?>">
                </a>
            <?php endif;?>

            <?php 
            if (is_front_page()):
                echo ($is_h1_logo_removed) ? '</div>' : '</h1>';
            else:
                echo '</div>';
            endif;
            ?>

            <div class="mobile-menu mobile-only pull-right">
                <a href="#" class="mobile-menu-trigger">
                    <span class="fa fa-bars" title="<?= _("Menu"); ?>"></span>
                </a>
            </div>
            <section class="user-area">
                <div class="only-logged-user" style="display: inline">
                <?php
                    // define deposit button
                    $depositButtonUrl = lotto_platform_get_permalink_by_slug('deposit');
                    $depositButtonText = _("Deposit");

                    $isFaireumCasino = $whitelabel['theme'] === Whitelabel::FAIREUM_THEME && IS_CASINO;
                    if ($isFaireumCasino) {
                        $depositButtonUrl = lotto_platform_get_permalink_by_slug(FaireumDepositAndWithdrawalInstructionsPage::SLUG);
                        $depositButtonText = _("Deposit & Withdrawal");
                    }

                    $db_button_part1 = '<a href="' . $depositButtonUrl . '" ';
                    $db_button_part2 = '>' . $depositButtonText . '</a>';

                    // set proper button variables
                    $db_inside = $db_outside = $user_info_with_depbtn = "";
                    if (isset($whitelabel['display_deposit_button']) && (int) $whitelabel['display_deposit_button'] === 1) {
                        switch (get_theme_mod("base_deposit_button_place")) { // todo: redundancy- the same thing in functions.php
                            default:
                            case "outside":
                                $user_info_with_depbtn = " user-info-with-depbtn";
                                $db_outside = $db_button_part1 . 'class="pull-left btn btn-lg btn-primary btn-deposit"' . $db_button_part2;
                                break;
                            case "inside":
                                $db_inside = $db_button_part1 . 'class="btn btn-primary"' . $db_button_part2;
                                break;
                        }
                    }

                    try {
                        $autologinLink = $whitelabelOAuthClientRepository->getWhitelabelAutologinLink($whitelabel['id']);
                    } catch (Throwable $exception) {
                        /** @var FileLoggerService $fileLoggerService */
                        $fileLoggerService = Container::get(FileLoggerService::class);
                        $fileLoggerService->error(
                                'Whitelabel OAuth autologin link is not displaying: '
                                .  $exception->getMessage());

                        $autologinLink = false;
                    }
                ?>
                    <div class="user-info<?= $user_info_with_depbtn ?>">
                        <div class="user-info-area menu-trigger<?php if (Lotto_Platform::is_page('account')) : echo ' user-info-area-active'; endif; ?>">
                            <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('account')); ?>" class="user-info-link">
                                <span class="fa fa-solid fa-circle-user" aria-hidden="true"></span>
                                <script>
                                    window.anonymousUserName = '<?= _('Anonymous') ?>';
                                </script>
                                <span id="user-info-user-name" class="user-name">
                                    <!-- This field will be set by async JS -->
                                </span>
                                <span class="user-balance">
                                    <span id="user-balance-amount" class="user-balance-amount">
                                        <span class="loading"></span>
                                    </span>
                                    <?php if (!IS_CASINO): ?>
                                        <span id="user-bonus-balance-amount" class="user-balance-bonus"></span>
                                    <?php endif; ?>
                                </span>
                                <span class="fa fa-chevron-down" aria-hidden="true"></span>
                            </a>
                        </div>
                        <div class="menu-wrapper <?= IS_CASINO ? 'casino-menu-wrapper' : '' ?>">
                            <div class="user-menu-content">
                                <div class="user-menu-element-container">
                                    <div class="user-menu-item">
                                        <a href="<?= lotto_platform_get_permalink_by_slug('account'); ?>">
                                            <span class="fa fa-cog" aria-hidden="true"></span>
                                            <span class="user-menu-item-link"><?= _("My account") ?></span>
                                        </a>
                                    </div>
                                    <?php
                                    if (!IS_CASINO) :
                                    ?>
                                        <div class="user-menu-item">
                                            <a href="<?= lotto_platform_get_permalink_by_slug('account'); ?>tickets/awaiting">
                                                <span class="fa fa-ticket" aria-hidden="true"></span>
                                                <span class="user-menu-item-link"><?= _("My tickets") ?></span>
                                            </a>
                                        </div>
                                    <?php
                                    endif;
                                    ?>
                                    <div class="user-menu-item">
                                        <a href="<?= lotto_platform_get_permalink_by_slug('account'); ?>transactions/">
                                            <span class="fa fa-money-bill-1" aria-hidden="true"></span>
                                            <span class="user-menu-item-link"><?= _("My transactions") ?></span>
                                        </a>
                                    </div>
                                    <?php
                                    $user = lotto_platform_user();
                                    $isPromoteAccess = isset($user['connected_aff_id']) && $user['connected_aff_id'] > 0;
                                    ?>
                                    <?php if ($isPromoteAccess): ?>
                                    <div class="user-menu-item user-menu-item-nmr">
                                        <a href="<?= lotto_platform_get_permalink_by_slug('account'); ?>promote/">
                                            <span class="fa fa-share-alt" aria-hidden="true"></span>
                                            <span class="user-menu-item-link"><?= _("Promote and earn") ?></span>
                                        </a>
                                    </div>
                                    <?php endif; ?>
                                </div>
                                <div class="user-menu-item-container">
                                    <?= $db_inside ?>
                                    <?php if ($autologinLink !== false): ?>
                                    <a href="<?= $autologinLink['uri'] ?>" class="new-lottopark-login logout pull-left"><?= _('Login to') . ' ' . $autologinLink['text'] ?></a>
                                    <?php endif; ?>
                                    <a href="<?= lotto_platform_get_permalink_by_slug('account'); ?>logout/" class="logout pull-right"><?= _("Log out") ?></a>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                            <?php /*wp_nav_menu(array('theme_location' => 'user', 'container' => null)); */ ?>
                        </div>
                    </div>
                    <?= $db_outside ?>
                </div>
                <!-- We would like to display login/register buttons when JavaScript is off-->
                <noscript>
                    <style type="text/css">
                        #login-section {display: inline !important;}
                    </style>
                </noscript>
                <div class="only-not-logged-user" style="display: inline" id="login-section">
                    <?php
                    $canUserLoginViaSite = ($whitelabel['can_user_login_via_site'] ?? 1);
                    if ($canUserLoginViaSite) :
                    ?>
                        <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login')); ?>" id="btn-login" class="pull-left btn btn-lg btn-secondary btn-login">
                            <?= _("Login") ?>
                        </a>
                    <?php
                    endif;
                    $canUserRegisterViaSite = ($whitelabel['can_user_register_via_site'] ?? 1);
                    if ($canUserRegisterViaSite) :
                    ?>
                        <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('signup')); ?>" id="btn-signup" class="pull-left btn btn-lg btn-secondary btn-signup">
                            <?= _("Sign up") ?>
                        </a>
                    <?php
                    endif;
                ?>
                </div>
                <div class="lang-select">
                    <?php base_theme_language_switcher(); ?>
                </div>
                <?php
                $shouldNotHideBasket = array_search($whitelabel['id'], [36, 37]) === false; // temp: premier & domini
                if (!IS_CASINO && $shouldNotHideBasket) :
                    ?>
                    <div class="pull-left order-info-area">
                        <div id="add-notempty-classes" class="order-info menu-trigger">
                            <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('order')); ?>" class="user-info-link">
                                <span class="fa fa-shopping-cart" aria-hidden="true"></span> <span class="order-count" id="order-count"></span>
                                <div class="order-info-desc pull-right">
                                    <span class="order-info-amount" id="order-info-amount"><span class="loading"></span></span><br>
                                    <span class="order-info-link"><?= _("checkout"); ?></span>
                                </div>
                            </a>
                        </div>
                        <div class="menu-wrapper" id="mainBasketContainer">
                            <div class="menu">
                                <div class="menu-arrow"></div>
                                <script>
                                    window.deleteInUserLanguage = '<?= _('Delete') ?>';
                                </script>
                                <ul id="basketDataContainer">
                                    <span class="loading"></span>
                                    <!-- Loaded asynchronously by wordpress/wp-content/plugins/lotto-platform/public/js/modules/basket.js -->
                                </ul>
                                <div class="order-info-summary">
                                    <div class="order-info-summary-button pull-right">
                                        <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('order')); ?>" class="btn btn-primary"><?= _("Checkout") ?></a>
                                    </div>
                                    <div class="order-info-summary-amount pull-right">
                                        <!-- Sums will be loaded asynchronously by wordpress/wp-content/plugins/lotto-platform/public/js/modules/basket.js -->
                                        <?= _("Sum") ?>: <span id="sum" class="sum"></span><span id="sum-full"></span>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                endif;
                ?>
                <div class="clearfix"></div>
            </section>
            <div class="clearfix"></div>
        </div>
    </header>

    <?php
    $menuSlug = IS_CASINO ? 'casino-primary' : 'primary';
    $menuOptions = ['theme_location' => $menuSlug];

    if (has_nav_menu($menuSlug)) :
    ?>
        <nav id="primary-nav" class="nav-<?= $header; ?>">
            <div class="main-width">
                <div class="mobile-close-area mobile-only">
                    <a href="#" id="mobile-close"><span class="fa fa-times" aria-hidden="true"></span></a>
                </div>
                <div class="login-signup-buttons-nav-mobile only-not-logged-user">
                    <?php if ((int)$whitelabel['can_user_login_via_site'] === 1):?>
                        <div class="login-signup-buttons-login-div">
                            <div class="login-button-main-div">
                                <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('login')); ?>" class="btn btn-secondary btn-login btn-login-mobile">
                                    <?= _("Login"); ?>
                                </a>
                            </div>
                        </div>
                    <?php
                    endif;
                    if ((int)$whitelabel['can_user_register_via_site'] === 1):
                        ?>
                        <div class="login-signup-buttons-signup-div">
                            <div class="signup-button-main-div">
                                <a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug('signup')); ?>" class="btn btn-secondary btn-signup btn-signup-mobile">
                                    <?= _("Sign Up"); ?>
                                </a>
                            </div>
                        </div>
                    <?php endif ?>
                </div>
                <?php wp_nav_menu($menuOptions); ?>
                <div class="lang-select">
                    <?php base_theme_language_switcher(true); ?>
                </div>
            </div>
        </nav>
    <?php
    endif;
