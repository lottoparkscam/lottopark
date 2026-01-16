<?php

use Helpers\UrlHelper;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

/** @var Whitelabel */
$whitelabel = Container::get('whitelabel');

get_header();

$categories = get_the_category();
if (!IS_CASINO) {
    $sidebar_add = '-default';
    $lotteries_t = lotto_platform_get_lotteries();
    $lotteries = array_keys($lotteries_t['__by_slug']);

    if (!empty($categories)) {
        foreach ($categories as $key => $category) {
            if (in_array($category->slug, $lotteries) &&
                $sidebar_add == '-default'
            ) {
                $sidebar_add = '';
            }
        }
    }

    $key_text = 'lottery-news' . $sidebar_add . '-content-sidebar-id';
}

list(
    $social_share_rows,
    $counter_socials,
    $current_url
    ) = Helpers_General::get_prepared_social_share_links();
?>
    <div class="content-area">
        <?php get_active_sidebar('single-news-top-sidebar-id');?>
        <div class="main-width content-width">
            <div class="content-box news-box">
                <section class="post-content">
                    <article class="post">
                        <h1>
                            <?php the_title(); ?>
                        </h1>
                    </article>
                </section>

                <?php
                if (!IS_CASINO && is_active_sidebar($key_text)):
                ?>
                <div class="content-box-main">
                    <?php
                    endif;
                    ?>
                    <section class="post-content">
                        <article class="post">
                            <div class="article-info" >
                                <?php
                                base_theme_social_share_top(
                                    $social_share_rows,
                                    $counter_socials,
                                    $current_url,
                                    false
                                );
                                ?>
                                <?php if ($whitelabel->isNotTheme(Whitelabel::LOTOKING_THEME)):?>
                                    <time datetime="<?php echo htmlspecialchars(get_post_time('c', true)); ?>">
                                        <span class="fa fa-clock-o" aria-hidden="true"></span> <?php
                                        echo Security::htmlentities(human_time_diff(get_post_time('U', true)));
                                        ?>
                                    </time>
                                <?php endif;?>

                                <?php
                                $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
                                $showCategories = (int)$whitelabel['show_categories'];
                                if ($showCategories && !empty($categories)):
                                    ?>
                                    <div class="categories">
                                        <span class="fa fa-tag" aria-hidden="true"></span>
                                        <?php
                                        foreach ($categories as $key => $category):
                                            $link = UrlHelper::changeAbsoluteUrlToCasinoUrl(get_category_link($category->term_id));
                                            ?>
                                            <a href="<?php echo UrlHelper::esc_url($link); ?>">
                                                <?php echo Security::htmlentities($category->name); ?>
                                            </a>
                                            <?php
                                            if ($key != count($categories)-1):
                                                echo ', ';
                                            endif;
                                        endforeach;
                                        ?>
                                    </div>
                                <?php
                                endif;
                                ?>
                            </div>

                            <div class="clearfix"></div>

                            <?php
                            if (has_post_thumbnail()):
                                the_post_thumbnail("post-thumbnail", array('class' => 'alignleft'));
                            endif;

                            if (in_array(get_post_status(), array('publish'))):
                            endif;

                            the_content();

                            base_theme_social_share_bottom(
                                $social_share_rows,
                                $counter_socials,
                                $current_url
                            );

                            $page_posts = apply_filters(
                                'wpml_object_id',
                                get_option('page_for_posts'),
                                'page',
                                false
                            );
                            ?>
                        </article>
                    </section>

                    <?php
                    if (!IS_CASINO && is_active_sidebar($key_text)):
                    ?>
                </div>

                <div class="content-box-sidebar">
                    <?php
                    Lotto_Helper::widget_before_area($key_text);
                    dynamic_sidebar($key_text);
                    Lotto_Helper::widget_after_area($key_text);
                    ?>
                </div>
                <div class="clearfix"></div>
            <?php
            endif;
            ?>
            </div>

            <div class="content-box news-more-box">
                <?php
                if (!empty($page_posts)):
                    ?>
                    <a href="<?php echo UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($page_posts)); ?>"
                       class="post-content-list-link">
                        <?php echo Security::htmlentities(_('&laquo; back to the news list')); ?>
                    </a>
                <?php
                endif;
                ?>
            </div>
        </div>
    </div>
<?php

get_footer();
