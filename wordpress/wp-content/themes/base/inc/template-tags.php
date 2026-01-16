<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

/**
 *
 * @param array $a
 * @param array $b
 * @return int
 */
function base_theme_language_sort($a, $b)
{
    // the second level of sort needs to be locale-dependend (sort by name of subdivision) to prevent displaying e.g. Łódzkie in the end
    $collator = new Collator(Lotto_Settings::getInstance()->get("locale_default"));
    $collator->setAttribute(Collator::NUMERIC_COLLATION, Collator::ON); // sort e.g. Praha 2 before Praha 10
    return $collator->compare($a['native_name'], $b['native_name']);
}

/**
 *
 * @param boolean $mobile
 */
function base_theme_language_switcher($mobile = false)
{
    $languages = apply_filters('wpml_active_languages', null, array('skip_missing' => 0));
    $is_user = Lotto_Settings::getInstance()->get("is_user");

    usort($languages, "base_theme_language_sort");

    if (!empty($languages)) {
        $lotto_path = WP_PLUGIN_DIR . '/lotto-platform';
        if ($mobile) {
            echo '<select class="mobile-language">';
            foreach ($languages as $language) {
                echo '<option value="' . UrlHelper::esc_url(
                    UrlHelper::changeAbsoluteUrlToCasinoUrl($language['url'])
                    ) . '"' . ($language['active'] ? ' selected="selected"' : '') . '>' .
                    $language['native_name'] . '</option>';
            }
            echo '</select>';
        } else {
            if (file_exists($lotto_path)) {
                echo '<div class="menu-trigger">';
                
                foreach ($languages as $language) {
                    if ($language['active']) {
                        $flag_table = explode("_", $language['default_locale']);
                        if (empty($flag_table[1])) {
                            continue;
                        }
                        $flag = $flag_table[1];
                        if  ($flag === 'ET') {
                            $flag = 'EE';
                        }
                        echo '<a href="#" class="actual-lang">';
                        echo '<i class="sprite-lang sprite-lang-' . strtolower($flag) . '"></i> ';
                        echo '<strong class="lang-select-text">' . $language['native_name'] . '</strong>';
                        echo '<span class="fa fa-chevron-down" aria-hidden="true"></span>';
                        echo '</a>';
                        break;
                    }
                }
                echo '</div>';
                
                echo '<div class="menu-wrapper' .
                    ((!empty($languages) && count($languages) > 6) ? ' language-large' : '') . '">';
                
                echo '<ul>';
                
                foreach ($languages as $language) {
                    if (!$language['active']) {
                        $flag_table = explode("_", $language['default_locale']);
                        if (empty($flag_table[1])) {
                            continue;
                        }
                        $flag = $flag_table[1];
                        if  ($flag === 'ET') {
                            $flag = 'EE';
                        }

                        echo '<li><a href="' . UrlHelper::esc_url(
                            UrlHelper::changeAbsoluteUrlToCasinoUrl($language['url'])
                            ) . '"><i class="sprite-lang sprite-lang-' .
                            strtolower($flag) . '"></i> ' .
                            $language['native_name'] . '</a></li>';
                    }
                }

                echo '</ul>';
                echo '</div>';
            }
        }
    }
}

/**
 *
 * @param array $social_share_rows
 * @param int $counter_socials
 * @param string $current_url,
 * @param boolean $clearfix
 */
function base_theme_social_share_top(
    $social_share_rows,
    $counter_socials,
    $current_url,
    $clearfix = true
) {
    if (isset($social_share_rows) && isset($counter_socials) &&
        count($social_share_rows) > 0 && $counter_socials > 0
    ) {
        $elements = '<div class="socials-share-box-top">';

        foreach ($social_share_rows as $social) {
            $show_social = get_theme_mod('base_social_share_' . $social[0]);
            if ($show_social && !empty($current_url)) {
                $social_link_temp = $social[4];
                $social_link_temp .= $current_url;
                $social_link = UrlHelper::esc_url($social_link_temp);

                $elements .= '<div class="social-share-top">';
                $elements .= '<a href="' . $social_link . '" target="_blank">';
                $elements .= '<i class="' . $social[2] . ' social-share-awesome"></i>';
                $elements .= '</a>';
                $elements .= '</div>';
            }
        }

        $elements .= '</div>';

        if ($clearfix) {
            $elements .= '<div class="clearfix"></div>';
        }

        echo $elements;
    }
}

/**
 *
 * @param array $social_share_rows
 * @param int $counter_socials
 * @param string $current_url
 * @param boolean $clearfix
 */
function base_theme_social_share_bottom(
    $social_share_rows,
    $counter_socials,
    $current_url,
    $clearfix = true
) {
    if (isset($social_share_rows) && isset($counter_socials) &&
        count($social_share_rows) > 0 && $counter_socials > 0
    ) {
        $elements = '<div class="socials-share-box-bottom">';
        
        $elements .= '<span class="social-share-text">';
        $elements .= Security::htmlentities(_('Share with')).': ';
        $elements .= '</span>';
        
        foreach ($social_share_rows as $social) {
            $show_social = get_theme_mod('base_social_share_' . $social[0]);
            
            if ($show_social && !empty($current_url)) {
                $social_link_temp = $social[4];
                $social_link_temp .= $current_url;
                $social_link = UrlHelper::esc_url($social_link_temp);

                $elements .= '<div class="social-share-bottom">';
                $elements .= '<a href="' . $social_link .'" target="_blank">';
                $elements .= '<i class="' . $social[2] . ' social-share-awesome"></i>';
                $elements .= '</a>';
                $elements .= '</div>';
            }
        }
                
        $elements .= '</div>';
        
        if ($clearfix) {
            $elements .= '<div class="clearfix"></div>';
        }

        echo $elements;
    }
}


/**
 *
 * @param mixed $page_posts
 * @param string $selected
 */
function get_content_main_menu($page_posts, $selected)
{
    $kenoPagesSlugs = [
        basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno"))),
        basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno-results"))),
        basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno-lotteries"))),
    ];

    if (in_array(basename(get_permalink()), $kenoPagesSlugs)) {
        $permalinks = [
            'play' => [
                'link' => UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno")),
                'text' => _("Play"),
            ],
            'results' => [
                'link' => UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno-results")),
                'text' => _("Results"),
            ],
            'lotteries' => [
                'link' => UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno-lotteries")),
                'text' => _("Information"),
            ],
            'news' => [
                'link' => UrlHelper::esc_url(get_permalink($page_posts)),
                'text' => _("News"),
            ]
        ];
    } else {
        $permalinks = [
            'play' => [
                'link' => UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("play")),
                'text' => _("Play"),
            ],
            'results' => [
                'link' => UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("results")),
                'text' => _("Results"),
            ],
            'lotteries' => [
                'link' => UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("lotteries")),
                'text' => _("Information"),
            ],
            'news' => [
                'link' => UrlHelper::esc_url(get_permalink($page_posts)),
                'text' => _("News"),
            ]
        ];
    }

    $menu_content = '<div class="main-width">';
    
    $menu_content .= '<div class="content-nav-wrapper mobile-only">';
    $menu_content .= '<select class="content-nav">';
    
    foreach ($permalinks as $key => $permalink) {
        $selected_text = '';
    
        if ($selected == $key) {
            $selected_text = 'selected';
        }
        
        $menu_content .= '<option ' . $selected_text . ' value="' . $permalink['link'] . '">';
        $menu_content .= $permalink['text'];
        $menu_content .= '</option>';
    }
    
    $menu_content .= '</select>';
    $menu_content .= '</div>';
    
    $menu_content .= '<nav class="content-nav mobile-hide">';
    $menu_content .= '<ul>';
    
    foreach ($permalinks as $key => $permalink) {
        $selected_class = '';
    
        if ($selected == $key) {
            $selected_class = ' class="content-nav-active"';
        }
        
        $menu_content .= '<li' . $selected_class . '>';
        $menu_content .= '<a href="' . $permalink['link'] . '">';
        $menu_content .= $permalink['text'];
        $menu_content .= '</a>';
        $menu_content .= '</li>';
    }
    
    $menu_content .= '<div class="clearfix"></div>';
    $menu_content .= '</ul>';
    $menu_content .= '</nav>';
    
    $menu_content .= '</div>';
    
    echo $menu_content;
}

/**
 *
 * @param string $id_of_sidebar
 */
function get_active_sidebar(string $id_of_sidebar)
{
    if (is_active_sidebar($id_of_sidebar)) {
        Lotto_Helper::widget_before_area($id_of_sidebar);
        dynamic_sidebar($id_of_sidebar);
        Lotto_Helper::widget_after_area($id_of_sidebar);
    }
}

/**
 * This is additional function to get term based on category.
 *
 * @param string $lottery_slug The category slug.
 * @param object $category Category data object
 */
function get_term_by_lottery_slug(string $lottery_slug = "", $category = null)
{
    $term = null;
    if (empty($category) && !empty($lottery_slug)) {
        $category = get_category_by_slug($lottery_slug);
    }
    
    if (!empty($category) && !empty($category->term_id)) {
        $term = apply_filters('wpml_object_id', $category->term_id, 'category', false);
    }
    
    return $term;
}

/**
 *
 * @return string
 */
function get_user_timezone(): string
{
    $user_timezone = "UTC";
    
    $user = Lotto_Settings::getInstance()->get("user");
    
    if (!empty($user) && !empty($user['timezone'])) {
        $user_timezone = (string)$user['timezone'];
    }

    return $user_timezone;
}

/**
 *
 * @param string $lotto_page_background_image
 * @return array|null
 */
function get_lotto_image_size(string $lotto_page_background_image):? array
{
    $image_size_result = null;
    
    if (!empty($lotto_page_background_image)) {
        $replaced_image_url = str_replace(
            Lotto_Helper::get_URL() .'/',
            '',
            $lotto_page_background_image
        );

        // Not processed further
        if (empty($replaced_image_url)) {
            return null;
        }

        $realpath = realpath($replaced_image_url);

        // Not processed further
        if (empty($realpath)) {
            return null;
        }

        $image_size_result = getimagesize($realpath);

        // Not processed further
        if ($image_size_result === false) {
            return null;
        }
    }
    
    return $image_size_result;
}
