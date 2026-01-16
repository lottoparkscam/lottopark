<?php

use Fuel\Tasks\Seeders\Wordpress\FaireumDepositAndWithdrawalInstructionsPage;
use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Fuel\Core\Security;
use Models\Whitelabel;
use Repositories\WhitelabelOAuthClientRepository;
use Repositories\WordpressWhitelistUnfilteredHtmlEditorRepository;
use Services\Logs\FileLoggerService;

if (!defined('WPINC')) {
    die;
}

/** @see wordpress/wp-content/themes/base/ShortCodes.php - this file was created in other to avoid creating monolith here */
require_once('ShortCodes.php');
define('ICL_DONT_LOAD_NAVIGATION_CSS', true);
define('ICL_DONT_LOAD_LANGUAGE_SELECTOR_CSS', true);
define('ICL_DONT_LOAD_LANGUAGES_JS', true);

Lotto_Settings::getInstance()->set("ballwidth", 39);

if (!function_exists('base_theme_setup')) {
    function base_theme_setup()
    {
        if (file_exists(get_stylesheet_directory() . '/languages')) {
            load_theme_textdomain('base-theme', get_stylesheet_directory() . '/languages');
        }
        load_theme_textdomain('base-theme', get_template_directory() . '/languages');

        add_theme_support('title-tag');
        add_theme_support('custom-background');
        add_theme_support('post-thumbnails', array('post', 'slider'));
        set_post_thumbnail_size(379, 379);

        // we do not need large_size for now
        update_option('large_size_w', '0');
        update_option('large_size_h', '0');

        update_option('medium_large_size_w', '0');

        register_nav_menus(array(
            'primary' => Security::htmlentities(_('Primary')),
            'footer' => Security::htmlentities(_('Footer')),
            'casino-primary' => Security::htmlentities(_('Casino Primary')),
            'casino-footer' => Security::htmlentities(_('Casino Footer')),
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Front Page')),
            'id'            => 'frontpage-sidebar-id',
            'description'   => Security::htmlentities(_('Front Page widget area.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Play - List Page - Top')),
            'id'            => 'play-sidebar-id',
            'description'   => Security::htmlentities(_('Play Lottery Page (with lottery list) widget area. Widgets from this area will display before "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Play - List Page - Bottom')),
            'id'            => 'play-more-sidebar-id',
            'description'   => Security::htmlentities(_('Play Lottery Page (with lottery list) widget area. Widgets from this area will display after "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Play - Lottery Page - Top')),
            'id'            => 'play-lottery-sidebar-id',
            'description'   => Security::htmlentities(_('Play Lottery Page (with ticket purchase) widget area. Widgets from this area will display before content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Play - Lottery Page - Bottom')),
            'id'            => 'play-lottery-more-sidebar-id',
            'description'   => Security::htmlentities(_('Play Lottery Page (with ticket purchase) widget area. Widgets from this area will display after content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Results - List Page - Top')),
            'id'            => 'results-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Results - List Page widget area. Widgets from this area will display before "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Results - List Page - Bottom')),
            'id'            => 'results-more-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Results - List Page widget area. Widgets from this area will display after "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Results - Lottery Page - Top')),
            'id'            => 'lottery-results-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Results - Specific Lottery Page widget area. Widgets from this area will display before "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Results - Lottery Page - Bottom')),
            'id'            => 'lottery-results-more-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Results - Specific Lottery Page widget area. Widgets from this area will display after "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Results - Lottery Page - Content')),
            'id'            => 'lottery-results-content-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Results - Specific Lottery Page widget area. Widgets from this area will display next to content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar([
            'name'          => Security::htmlentities(_('Results - Raffle Page - Content')),
            'id'            => 'raffle-results-content-sidebar-id',
            'description'   => Security::htmlentities(_('Raffle Results - Specific Raffle Page widget area. Widgets from this area will display next to content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ]);
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Info - List Page - Top')),
            'id'            => 'info-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Info - List Page widget area. Widgets from this area will display before "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Info - List Page - Bottom')),
            'id'            => 'info-more-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Info - List Page widget area. Widgets from this area will display after "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Info - Lottery Page - Top')),
            'id'            => 'lottery-info-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Info - Specific Lottery Page widget area. Widgets from this area will display before "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Info - Lottery Page - Bottom')),
            'id'            => 'lottery-info-more-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Info - Specific Lottery Page widget area. Widgets from this area will display after "more" content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar(array(
            'name'          => Security::htmlentities(_('Info - Lottery Page - Content')),
            'id'            => 'lottery-info-content-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery Info - Specific Lottery Page widget area. Widgets from this area will display next to content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
        register_sidebar([
            'name'          => Security::htmlentities(_('Info - Raffle Page - Content')),
            'id'            => 'raffle-info-content-sidebar-id',
            'description'   => Security::htmlentities(_('Raffle Info - Specific Raffle Page widget area. Widgets from this area will display next to content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ]);

        register_sidebar(array(
            'name'          => Security::htmlentities(_('News - Default Page - Content')),
            'id'            => 'lottery-news-default-content-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery News - Default Page widget area. Widgets from this area will display next to content.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));

        register_sidebar(array(
            'name'          => Security::htmlentities(_('News - Lottery Page - Content')),
            'id'            => 'lottery-news-content-sidebar-id',
            'description'   => Security::htmlentities(_('Lottery News - Specific Lottery Page widget area. Widgets from this area will display next to content related to the lottery.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));

        register_sidebar([
            'name'          => Security::htmlentities(_('Single News - Top')),
            'id'            => 'single-news-top-sidebar-id',
            'description'   => Security::htmlentities(_('Single News - widgets from this area will display at the top of the single news page.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ]);

        register_sidebar(array(
            'name'          => Security::htmlentities(_('Casino Front Page Bottom')),
            'id'            => 'casino-frontpage-sidebar-bottom-id',
            'description'   => Security::htmlentities(_('Front Page casino bottom widget area.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));

        register_sidebar(array(
            'name'          => Security::htmlentities(_('Casino Front Page Top')),
            'id'            => 'casino-frontpage-sidebar-top-id',
            'description'   => Security::htmlentities(_('Front Page casino top widget area.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));

        register_sidebar(array(
            'name'          => Security::htmlentities(_('Casino Front Page Content')),
            'id'            => 'casino-frontpage-sidebar-content-id',
            'description'   => Security::htmlentities(_('Front Page casino content widget area.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));

        register_sidebar(array(
            'name'          => Security::htmlentities(_('Page Raffle Top')),
            'id'            => 'page-raffle-sidebar-top-id',
            'description'   => Security::htmlentities(_('Page Raffle top widget area.')),
            'before_widget' => '<div id="%1$s" class="widget %2$s">',
            'after_widget'  => '</div>'
        ));
    }
}
add_action('after_setup_theme', 'base_theme_setup');

function base_theme_content_width()
{
    $GLOBALS['content_width'] = apply_filters('base_theme_content_width', 1140);
}
add_action('after_setup_theme', 'base_theme_content_width', 0);

function base_theme_scripts()
{
    $family = "Source Sans Pro:400,600,700";

    if (Lotto_Settings::getInstance()->get("load_lightbox") === true) {
        wp_enqueue_style(
            'base-lightbox-style',
            get_template_directory_uri() . '/css/Lightbox.min.css'
        );
    }

    Lotto_Settings::getInstance()->set("google_font_family", $family);

    if (Lotto_Settings::getInstance()->get("load_datepicker") == true) {
        $jqueryUiCss = AssetHelper::mix('css/jquery-ui.min.css', AssetHelper::TYPE_WORDPRESS, true);
        wp_enqueue_style('jquery-ui-theme', $jqueryUiCss);
    }

    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('classic-theme-styles');

    if (class_exists('WPML\BlockEditor\Loader')) {
        wp_deregister_style(WPML\BlockEditor\Loader::SCRIPT_NAME);
    }
}
add_action('wp_enqueue_scripts', 'base_theme_scripts');

function base_theme_admin_scripts()
{
    $font = get_theme_mod("base_fonts_scheme");
    $colors = get_theme_mod("base_colors_scheme");
    if (in_array($font, array("ptsans", "signika", "source"))) {
        add_editor_style(get_template_directory_uri() . '/css/admin-font-' . $font . '.css');
    }
    add_editor_style(get_template_directory_uri() . '/css/admin-color-default.css');
    /*if(in_array($colors, array("orange-green", "orange-blue")))
    {
        add_editor_style(get_template_directory_uri().'/css/admin-color-'.$colors.'.css');
    }*/
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');
}
add_action('admin_enqueue_scripts', 'base_theme_admin_scripts');

require get_template_directory() . '/inc/template-tags.php';

/** @param WP_Post[] $items */
function rewriteNavMenus($items, $args)
{
    /** @var string $domain */
    $domain = Container::get('domain');
    $homepageUrl = rtrim(get_home_url(), '/');

    foreach ($items as $item) {
        $isHomeButton = $item->title === _('Home'); // we must distinguish between casino and lottery homepage  
        if ($isHomeButton) {
            $casinoPrefix = UrlHelper::getCasinoPrefixForWhitelabel($domain);
            $item->url = str_replace($domain, "$casinoPrefix.$domain", $item->url);
            continue;
        }

        $iNotUrlToLotteriesHomepage = $homepageUrl !== rtrim($item->url, '/');
        if ($iNotUrlToLotteriesHomepage) {
            $item->url = UrlHelper::changeAbsoluteUrlToCasinoUrl($item->url);
        }
    }

    return $items;
}

if (IS_CASINO) {
    add_filter('wp_nav_menu_objects', 'rewriteNavMenus', 10, 2);
}

/**
 *
 * @param string $items
 * @param object $args
 * @return string
 */
function base_theme_nav_menu($items, $args)
{
    $whitelabelOAuthClientRepository = Container::get(WhitelabelOAuthClientRepository::class);
    $whitelabel = Lotto_Settings::getInstance()->get('whitelabel');
    $homepageCssClass = 'menu-item-home';
    $homepageUrl = lotto_platform_home_url('/');
    $homepageTranslation = _('Home');
    $casinoUrl = lotto_platform_get_permalink_by_slug('casino', 'page', $siteExists);

    $shouldChangeUrlForCasino = !IS_CASINO && $siteExists && str_contains($items, $casinoUrl);
    if ($shouldChangeUrlForCasino) {
        $language = ICL_LANGUAGE_CODE ?? 'en';
        $isNotEnglish = $language !== 'en';
        $casinoHomepage = rtrim(UrlHelper::getCasinoHomeUrl(), '/');
        if ($isNotEnglish) {
            $casinoHomepage .= '/' . $language;
        }
        $items = str_replace($casinoUrl, $casinoHomepage, $items);
    }

    if ($args->theme_location == "primary" || $args->theme_location == "casino-primary") {
        $menu = <<<HOMEPAGE_BUTTON
        <li class="$homepageCssClass">
            <a href="$homepageUrl">
                <span class="fa fa-home" title="$homepageTranslation"></span>
            </a>
        </li>
        HOMEPAGE_BUTTON;

        // define deposit button
        $isFaireumCasino = $whitelabel['theme'] === Whitelabel::FAIREUM_THEME && IS_CASINO;
        if ($isFaireumCasino) {
            $depositButtonUrl = lotto_platform_get_permalink_by_slug(FaireumDepositAndWithdrawalInstructionsPage::SLUG);
            $depositButtonText = _("Deposit & Withdrawal");
        } else {
            $depositButtonUrl = lotto_platform_get_permalink_by_slug('deposit');
            $depositButtonText = _("Deposit");
        }

        $user = lotto_platform_user();
        $isPromoteAccess = isset($user['connected_aff_id']) && $user['connected_aff_id'] > 0;

        $deposit_button = '<a href="' .
            $depositButtonUrl .
            '" class="btn btn-primary btn-deposit-mobile only-logged-user">' .
            $depositButtonText .
            '</a>';

        // Deposit button with menu item
        $deposit_button_menu = '<li class="menu-item-mobile only-logged-user">
            <div class="menu-item-mobile-button">' .
            $deposit_button .
            '</div>
        </li>';

        $menu .= '<li class="menu-item-mobile menu-item-mobile-highlight menu-item-mobile-user only-logged-user">
            <a href="#" class="mobile-user-menu">
                <span class="fa fa-solid fa-circle-user" aria-hidden="true"></span> ' .
            _("My account") .
            ' <span class="mobile-user-menu-balance"><span>' .
            _("Balance") . ': </span>' .
            '<div style="display: inline" id="mobile-user-balance-amount"></div>' .
            '</span>';

        if (!IS_CASINO) {
            $menu .= '<span class="mobile-user-menu-balance"><span>' .
                _("Bonus balance") . ': </span>' .
                '<div style="display: inline" id="mobile-user-bonus-balance-amount"></div></span>' .
                '</span>';
        }

        $menu .= '</a>';
        $menu .= '<div class="mobile-user-menu-container">
            <ul>';

        $menu .= '<li>
            <a href="' . lotto_platform_get_permalink_by_slug('account') . '">' .
            _("My details") .
            '</a>
        </li>';

        if (!IS_CASINO) {
            $menu .= '<li>
            <a href="' . lotto_platform_get_permalink_by_slug('account') . 'tickets/">' .
                _("My tickets") .
                '</a>
        </li>';
        }

        $menu .= '<li>
            <a href="' . lotto_platform_get_permalink_by_slug('account') . 'transactions/">' .
            _("My transactions") .
            '</a>
        </li>';

        if($isPromoteAccess) {
            $menu .= '<li>
            <a href="' . lotto_platform_get_permalink_by_slug('account') . 'promote/">' .
                _('Promote and earn') .
                '</a>
            </li>';
        }

        $menu .= '<li>
            <a href="' . lotto_platform_get_permalink_by_slug('account') . 'logout/">' .
            _("Log out") .
            '</a>
        </li>';

        try {
            $autologinLink = $whitelabelOAuthClientRepository->getWhitelabelAutologinLink($whitelabel['id']);
        } catch (Throwable) {
            $autologinLink = false;
        }

        if ($autologinLink !== false) {
            $menu .= '<li>
                <div class="menu-item-mobile-button">
                    <a href="' . $autologinLink['uri'] . '" class="btn btn-primary btn-login-second-mobile">' .
                    _('Login to') . ' ' . $autologinLink['text'] .
                    '</a>
                </div>
            </li>';
        }

        $menu .= '</ul>' .
            '</div>';

        $menu .= '</li>';

        $menu .= $deposit_button_menu;

        $menu .= str_replace($homepageCssClass, '', $items);

        return $menu;
    }

    return $items;
}
add_filter('wp_nav_menu_items', 'base_theme_nav_menu', 10, 2);

/**
 * TODO: CONSIDER MOVING TO PLUGIN FOR OVERALL
 */
function base_theme_remove_admin_items()
{
    $user = wp_get_current_user();

    remove_submenu_page('tools.php', 'ms-delete-site.php');
    remove_submenu_page('options-general.php', 'installer');
    remove_submenu_page('wpseo_dashboard', 'wpseo_licenses');
    remove_menu_page('edit-comments.php');
    remove_menu_page('w3tc_dashboard');
    if ($user->user_login != "whitelotto") {
        remove_menu_page('plugins.php');
    }
}
add_action('admin_menu', 'base_theme_remove_admin_items', 11, 0);

/**
 * REMOVE WPML LANGUAGES SUBPAGE FROM ALL USERS EXCEPT WHITELOTTO with high priority
 */
function base_theme_remove_wpml_menu_items()
{
    $user = wp_get_current_user();

    /* CHECK IF USER IS WHITELOTTO, IF NOT - REMOVE ITEM FROM SIDEBAR */
    if ($user->user_login != "whitelotto") {
        remove_submenu_page('sitepress-multilingual-cms/menu/languages.php', 'sitepress-multilingual-cms/menu/languages.php');
    }
}
add_action('admin_menu', 'base_theme_remove_wpml_menu_items', 99);

/**
 *
 */
function base_theme_unregister_taxonomies()
{
    register_taxonomy('post_tag', array());
    //register_taxonomy('category', array());
}
add_action('init', 'base_theme_unregister_taxonomies');

/**
 *
 * @return void
 */
function base_theme_add_editor_style()
{
    add_theme_support('editor-style');
    if (!is_admin()) {
        return;
    }
    add_editor_style('css/editor-style.css');
}
add_action('init', 'base_theme_add_editor_style');

/**
 *
 * @param array $arr
 * @return array
 */
function base_theme_tiny_mce($arr)
{
    $arr['block_formats'] = "Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Heading 5=h5;Heading 6=h6;Preformatted=pre";
    $arr['plugins'] .= ',table';
    $arr['toolbar3'] = 'table';
    $arr['table_toolbar'] = '';
    return $arr;
}
add_filter('tiny_mce_before_init', 'base_theme_tiny_mce');

/**
 *
 * @param array $plugins
 * @return array
 */
function base_theme_tiny_mce_plugins($plugins)
{
    $plugins['table'] = get_template_directory_uri() . '/js/tinymce/plugins/table/plugin.min.js';
    return $plugins;
}
add_filter('mce_external_plugins', 'base_theme_tiny_mce_plugins');

/**
 * Create control parameters.
 *
 * @param string $type type of the control.
 * @param string $section section, where control should be placed.
 * @param string $label label of the control.
 * @param array $additional_parameters additional parameters or none.
 * @return array mostly string[] but can also contain sub arrays in case of select.
 */
function create_control_parameters(
    string $type,
    string $section,
    string $label,
    array $additional_parameters = []
): array {
    return array_merge([
        'type' => $type,
        'section' => $section,
        'label' => $label,
    ], $additional_parameters);
}

/**
 *
 * @global Object $wp_registered_sidebars
 * @param Object|WP_Customize_Manager $wp_customize
 */
function base_customize_register($wp_customize)
{
    global $wp_registered_sidebars;

    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
    $base_options = [
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'transport' => 'refresh',
    ];
    $wp_customize->add_setting('base_colors_scheme', array_merge($base_options, [
        'default' => "default"
    ]));
    $wp_customize->add_setting('base_fonts_scheme', array_merge($base_options, [
        'default' => "source"
    ]));
    $wp_customize->add_setting('base_deposit_button_place', array_merge($base_options, [
        'default' => "outside"
    ]));
    $wp_customize->add_setting('base_logotype_h1_removed', array_merge($base_options, [
        'default' => false
    ]));
    $wp_customize->add_setting('base_header_type', array_merge($base_options, [
        'default' => "bright"
    ]));
    $wp_customize->add_setting('base_page_bg_image', $base_options);

    // THEME OPTIONS
    $wp_customize->add_section('base_theme_options', array(
        'title' => _('Theme Options'),
        'description' => _('Change your theme options.'),
        'capability' => 'edit_theme_options'
    ));
    $wp_customize->add_control('base_header_type', create_control_parameters('select', 'base_theme_options', _('Choose header color scheme'), [
        'choices' => ["bright" => "Bright", "dark" => "Dark"],
    ]));
    $wp_customize->add_control('base_fonts_scheme', create_control_parameters('select', 'base_theme_options', _('Choose font'), [
        'choices' => ["source" => "Source Sans Pro", "ptsans" => "PT Sans", "signika" => "Signika"],
    ]));
    $wp_customize->add_control('base_deposit_button_place', create_control_parameters('select', 'base_theme_options', _('Deposit button place'), [
        'choices' => ["inside" => "Inside the user account box", "outside" => "Outside the user account box"],
    ]));
    $wp_customize->add_control('base_logotype_h1_removed', create_control_parameters('checkbox', 'base_theme_options', _('Remove H1 from the logotype.')));

    $wp_customize->add_control(
        new WP_Customize_Image_Control(
            $wp_customize,
            'base_page_bg_image',
            array(
                'section' => 'base_theme_options',
                'label' => _('Choose single page header image')
            )
        )
    );

    //Get lotto featured widgets settings
    $lotto_featured_settings = get_option('widget_lotto_platform_widget_featured');

    $total_widgets = wp_get_sidebars_widgets();
    foreach ($total_widgets as $sidebar => $widgets) {
        if (
            !isset($wp_registered_sidebars[$sidebar]) ||
            !isset($wp_registered_sidebars[$sidebar]['name'])
        ) {
            continue;
        }

        $sidebar_name = $wp_registered_sidebars[$sidebar]['name'];
        $counter = array();
        foreach ($widgets as $widget) {
            // todo: refactor - get "real object" instance and determine it is Customizable_Widget (Interface)
            // and then call customize method.
            if (is_active_widget(false, $widget, Lotto_Widget_Raffle_Promo::ID, true)) {
                Lotto_Widget_Raffle_Promo::customize($wp_customize);
            }

            $type = explode("-", $widget);
            if (!isset($counter[$sidebar][$type[0]])) {
                $counter[$sidebar][$type[0]] = 0;
            }
            $counter[$sidebar][$type[0]]++;

            if (is_active_widget(false, $widget, 'lotto_platform_widget_featured', true)) {
                $lotto_featured_id = end($type);
                $lotto_featured_type = $lotto_featured_settings[$lotto_featured_id]['type'];
                $wp_customize->add_section('base_' . $widget, array(
                    'title' => sprintf(_('%s: Lotto Featured #%d'), $sidebar_name, $counter[$sidebar][$type[0]]),
                    'description' => _('Change your lotto featured widget style.'),
                    'capability' => 'edit_theme_options'
                ));

                // Add background files select if type is Large Background
                if ((int) $lotto_featured_type === Lotto_Widget_Featured::TYPE_WITH_BACKGROUND) {
                    $wp_customize->add_setting('base_' . $widget . '_large_bg_image', array(
                        'type' => 'theme_mod',
                        'capability' => 'edit_theme_options',
                        'transport' => 'refresh'
                    ));

                    $wp_customize->add_control(
                        new WP_Customize_Image_Control(
                            $wp_customize,
                            'base_' . $widget . '_large_bg_image',
                            array(
                                'section' => 'base_' . $widget,
                                'label' => _('Choose large background image')
                            )
                        )
                    );

                    $wp_customize->add_setting('base_' . $widget . '_large_bg_image_mobile', array(
                        'type' => 'theme_mod',
                        'capability' => 'edit_theme_options',
                        'transport' => 'refresh'
                    ));

                    $wp_customize->add_control(
                        new WP_Customize_Image_Control(
                            $wp_customize,
                            'base_' . $widget . '_large_bg_image_mobile',
                            array(
                                'section' => 'base_' . $widget,
                                'label' => _('Choose mobile background image for "large background" option')
                            )
                        )
                    );
                }

                // SMALL OPTIONS

                $wp_customize->add_setting('base_' . $widget . '_bg_image', array(
                    'type' => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'transport' => 'refresh'
                ));

                $wp_customize->add_control(
                    new WP_Customize_Image_Control(
                        $wp_customize,
                        'base_' . $widget . '_bg_image',
                        array(
                            'section' => 'base_' . $widget,
                            'label' => _('Choose background image')
                        )
                    )
                );

                $wp_customize->add_setting('base_' . $widget . '_bg_image_mobile', array(
                    'type' => 'theme_mod',
                    'capability' => 'edit_theme_options',
                    'transport' => 'refresh'
                ));

                $wp_customize->add_control(
                    new WP_Customize_Image_Control(
                        $wp_customize,
                        'base_' . $widget . '_bg_image_mobile',
                        array(
                            'section' => 'base_' . $widget,
                            'label' => _('Choose mobile background image')
                        )
                    )
                );
            }
        }
    }
    $languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);

    foreach ($languages as $language) {
        $lshort = substr($language['code'], 0, 2);
        //$lname = Lotto_View::get_language_name($lshort);
        $wp_customize->add_setting('base_facebook_' . $lshort, array(
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'refresh'
        ));
        $wp_customize->add_setting('base_twitter_' . $lshort, array(
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'refresh'
        ));
    }

    $wp_customize->add_section('base_social', array(
        'title' => _('Social Links'),
        'description' => _('Add your social links.'),
        'capability' => 'edit_theme_options'
    ));

    foreach ($languages as $language) {
        $lshort = substr($language['code'], 0, 2);
        $lname = Lotto_View::get_language_name($lshort);
        $wp_customize->add_control('base_facebook_' . $lshort, array(
            'type' => 'url',
            'section' => 'base_social',
            'label' => sprintf(_('Facebook URL (%s)'), $lname),
        ));
        $wp_customize->add_control('base_twitter_' . $lshort, array(
            'type' => 'url',
            'section' => 'base_social',
            'label' => sprintf(_('Twitter URL (%s)'), $lname),
        ));
    }

    $social_share_rows = Helpers_General::get_socials_share_data();

    $wp_customize->add_section('base_social_share', array(
        'title' => _('Social Share Links'),
        'description' => _('Add social share links to articles.'),
        'capability' => 'edit_theme_options',
    ));

    foreach ($social_share_rows as $social) {
        $wp_customize->add_setting('base_social_share_' . $social[0], array(
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'refresh'
        ));

        $wp_customize->add_control('base_social_share_' . $social[0], array(
            'label' => _($social[1]),
            'type' => 'checkbox',
            'section' => 'base_social_share'
        ));
    }

    // Casino Promo Slider
    customize_promo_slider($wp_customize, [
        'id' => 'casino_promo_slider',
        'title' => _('Casino Promo Slider'),
        'description' => _('Change casino promo slider'),
        'checkbox_label' => _('Display casino promo banner')
    ]);

    // Lotto Promo Slider
    customize_promo_slider($wp_customize, [
        'id' => 'lotto_promo_slider',
        'title' => _('Lotto Promo Slider'),
        'description' => _('Change lotto promo slider'),
        'checkbox_label' => _('Display lotto promo banner')
    ]);
}

/**
 * @param Object|WP_Customize_Manager $wp_customize
 * @return void
 */
function customize_promo_slider(WP_Customize_Manager $wp_customize, array $config): void
{
    $wp_customize->add_section($config['id'], array(
        'title' => $config['title'],
        'description' => $config['description']
    ));
    $wp_customize->add_setting('display_' . $config['id'], array(
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'transport' => 'refresh'
    ));
    $wp_customize->add_control('display_' . $config['id'], array(
        'type' => 'checkbox',
        'section' => $config['id'],
        'label' => $config['checkbox_label'],
    ));
    $wp_customize->add_setting($config['id'] . '_slides_count', array(
        'type' => 'theme_mod',
        'capability' => 'edit_theme_options',
        'transport' => 'refresh'
    ));
    $wp_customize->add_control($config['id'] . '_slides_count', array(
        'type' => 'number',
        'section' => $config['id'],
        'label' => _('Slides count: '),
        'description' => _('You have to Publish and refresh site after change')
    ));
    $slidesCount = get_theme_mod($config['id'] . "_slides_count");
    if ($slidesCount < 1) {
        $slidesCount = 1;
    }
    for ($i = 1; $i <= $slidesCount; $i++) {
        $wp_customize->add_setting($config['id'] . '_' . $i, array(
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'refresh'
        ));
        $wp_customize->add_control(
            new WP_Customize_Image_Control($wp_customize, $config['id'] . '_' . $i, [
                'section' => $config['id'],
                'label'   => sprintf(_('Choose %s. slide\'s image'), $i)
            ])
        );

        $wp_customize->add_setting($config['id'] . '_slug_' . $i, array(
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'refresh'
        ));
        $wp_customize->add_control($config['id'] . '_slug_' . $i, array(
            'type' => 'url',
            'section' => $config['id'],
            'label' => sprintf(_('Provide %s. slide\'s link slug: '), $i),
            'description' => _('If wrong slug is provided it will link to homepage')
        ));

        $wp_customize->add_setting($config['id'] . '_url_target_' . $i, array(
            'type' => 'theme_mod',
            'capability' => 'edit_theme_options',
            'transport' => 'refresh'
        ));
        $wp_customize->add_control($config['id'] . '_url_target_' . $i,
            create_control_parameters(
                'select',
                'base_theme_options',
                sprintf(_('Choose %s. slide\'s url target: '), $i),
                [
                    'choices' => ['casino' => 'Casino', 'lottery' => 'Lottery'], 'section' => $config['id']
                ],
            )
        );
    }
}
add_action('customize_register', 'base_customize_register');

/**
 *
 * @param array $links
 * @param string $url
 * @param string $prev_link
 * @param string $next_link
 * @return array
 */
function base_get_raw_pagination_links($links, $url, &$prev_link, &$next_link)
{
    $out_links = array();
    $next_link = null;
    $prev_link = null;
    foreach ($links as $link) {
        $title = strip_tags($link);
        preg_match('/class=[\'"]([a-z-\s]+)[\'"]/', $link, $m);
        $classes = explode(" ", $m[1]);
        if (in_array("next", $classes)) {
            preg_match('/href=[\'"](.+?)[\'"]/', $link, $m);
            $next_link = $m[1];
        } elseif (in_array("prev", $classes)) {
            preg_match('/href=[\'"](.+?)[\'"]/', $link, $m);
            $prev_link = $m[1];
        } elseif (in_array("current", $classes)) {
            $out_link = array();
            $out_link['type'] = 'current';
            $out_link['title'] = $title;
            if ($title == "1") {
                $out_link['url'] = $url;
            } else {
                $out_link['url'] = $url . 'page/' . $title;
            }
            $out_links[] = $out_link;
        } else {
            $out_link = array();
            $out_link['type'] = 'normal';
            $out_link['title'] = $title;
            if ($title == "1") {
                $out_link['url'] = $url;
            } else {
                $out_link['url'] = $url . 'page/' . $title;
            }
            $out_links[] = $out_link;
        }
    }
    return $out_links;
}


if (!function_exists('base_theme_translations_files')) {
    add_filter('load_textdomain_mofile', 'base_theme_translations_files', 10, 2);

    /**
     * This filter allows us to change .mo files loading paths
     * In this case:
     * from wp-content/themes/base/languages/{locale}.mo
     * to wp-content/themes/base/languages/gettext/{locale}/LC_MESSAGES/{domain}.mo
     *
     * @param string $mofile
     * @param string $domain
     *
     * @return string
     */
    function base_theme_translations_files(string $mofile, string $domain)
    {
        $result = $mofile;

        // Adjust mofile path only for selected domains
        // base-theme should not be used any more, lotto-platform is main domain
        $domains = ['base-theme', 'lotto-platform'];

        if (in_array($domain, $domains) && strpos($mofile, 'wp-content/languages') === false) {
            $locale = apply_filters('theme_locale', determine_locale(), $domain);

            $lotto_platform_mofile =
                WP_PLUGIN_DIR . '/lotto-platform/languages/gettext/' . $locale . '/LC_MESSAGES/lotto-platform.mo';

            $mo_file_path = substr($mofile, 0, strpos($mofile, 'languages/'));
            $mo_file_path .= 'languages/gettext/' . $locale . '/LC_MESSAGES/' . $domain . '.mo';

            // First check if child theme translations exists
            // If not, check if lotto-platform plugin translations exists
            if (file_exists($mo_file_path)) {
                $result = $mo_file_path;
            } elseif (file_exists($lotto_platform_mofile)) {
                $result = $lotto_platform_mofile;
            }
        }
        return $result;
    }
}

if (!function_exists('specific_theme_translations_initialization')) {
    add_action('wp_loaded', 'specific_theme_translations_initialization');

    /**
     * base-theme translations are placed in lotto-platform to keep it in one place
     * and child-theme can override original translations
     */
    function specific_theme_translations_initialization()
    {
        $locale = apply_filters('theme_locale', determine_locale());
        if (file_exists(get_stylesheet_directory() . '/languages/gettext/' . $locale . '/LC_MESSAGES/lotto-platform.mo')) {
            bindtextdomain('lotto-platform', get_stylesheet_directory() . '/languages/gettext');
        }
    }
}

add_filter('onesignal_initialize_sdk', 'oneSignalInitializeSdkFilter', 10, 0);
function oneSignalInitializeSdkFilter(): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';

    $isWidgetPreview = str_contains($uri, '/wp-json/wp/v2/widget-types/');
    if ($isWidgetPreview) {
        return false;
    }

    if (IS_CASINO) {
        return false;
    }

    return true;
}

/**
 * we can't call unregister_widgets on these widgets because
 * we'd have to remove the WP_Block_Widget we need
 */
// add_filter('sidebars_widgets', 'disableSpecificBlockWidgets');
function disableSpecificBlockWidgets($sidebarWidgets): array
{
    if (!is_admin() && is_front_page()) {
        $disableBlockWidgetNames = [
            'block-2', // WP_Widget_Recent_Search
            'block-3', // WP_Widget_Recent_Posts
            'block-4', // WP_Widget_Recent_Comments
        ];

        foreach ($disableBlockWidgetNames as $widgetName) {
            $index = array_search($widgetName, $sidebarWidgets['frontpage-sidebar-id']);
            unset($sidebarWidgets['frontpage-sidebar-id'][$index]);
        }
    }

    return $sidebarWidgets;
}

/*
 * Adds a "main-width" wrapper to "Block" widgets for better RWD
 * and match alignment with other Lotto widgets (especially on mobile).
 * Unfortunately, seems to be difficult to hook to a specific widget.
 */
add_filter('dynamic_sidebar_params', 'widgetContentWrap');
function widgetContentWrap($widgetParams): array
{
    $wrapper = '<div class="main-width">';

    if (
        !empty($widgetParams[0]['id']) && !empty($widgetParams[0]['widget_name'])
        && $widgetParams[0]['id'] === 'frontpage-sidebar-id'
        && $widgetParams[0]['widget_name'] === 'Block'
        && strpos($widgetParams[0]['before_widget'], $wrapper) === false
    ) {
        $widgetParams[0]['before_widget'] .= $wrapper;
        $widgetParams[0]['after_widget'] .= '</div>';
    }

    return $widgetParams;
}

/**
 * Enable unfiltered_html capability for whitelisted users.
 * {@link https://developer.wordpress.org/reference/hooks/map_meta_cap/}
 *
 * @param  array  $caps     Primitive capabilities required of the user
 * @param  string $cap      Capability name
 * @return array  $caps     User capabilities, with 'unfiltered_html' potentially added
 */
function add_unfiltered_html_capability_to_whitelisted_users(array $caps, string $cap): array
{
    try {
        if ('unfiltered_html' === $cap) {
            $user = wp_get_current_user();

            if (!empty(array_intersect(['administrator', 'editor'], $user->roles))) {
                /** @var WordpressWhitelistUnfilteredHtmlEditorRepository $whitelistRepository */
                $whitelistRepository = Container::get(WordpressWhitelistUnfilteredHtmlEditorRepository::class);

                $isUserWhitelisted = $whitelistRepository->exists($user->user_email, 'email');

                if ($isUserWhitelisted) {
                    $caps = ['unfiltered_html'];
                }
            }
        }
    } catch(Throwable $e) {
        $errorMsg = 'There is a problem with Wordpress Unfiltered HTML Whitelist for users. Error description: ' . $e->getMessage();

        /** @var FileLoggerService $fileLoggerService */
        $fileLoggerService = Container::get(FileLoggerService::class);
        $fileLoggerService->warning($errorMsg);
    }

    return $caps;
}
add_filter('map_meta_cap', 'add_unfiltered_html_capability_to_whitelisted_users', 1, 3);

/**
 * Loads Slick plugin. Allows multiple usage in the Project script and provides one-time loading of assets while page loads.
 */
function wp_enqueue_script_slick_plugin(): void
{
    $slickJs = AssetHelper::mix('js/slick.min.js', AssetHelper::TYPE_WORDPRESS, true);
    $slickCss = AssetHelper::mix('css/slick.min.css', AssetHelper::TYPE_WORDPRESS, true);

    wp_enqueue_script('slick-js', $slickJs, ['jquery'], false, true);
    wp_enqueue_style('slick-css', $slickCss);
}