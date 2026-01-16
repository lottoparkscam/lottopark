<?php

use Helpers\UrlHelper;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

get_header();

$page_posts = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', false);

get_template_part('content', 'login-register-box-mobile');
?>
<div class="content-area home-area">
    <div class="main-width">
        <div class="content-nav-wrapper mobile-only">
            <select class="content-nav">
                <?php if (!IS_CASINO): ?>
                <option value="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("play")); ?>"><?= Security::htmlentities(_("Play")); ?></option>
                <option value="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("results")); ?>"><?= Security::htmlentities(_("Results")); ?></option>
                <option value="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("lotteries")); ?>"><?= Security::htmlentities(_("Information")); ?></option>
                <?php endif; ?>
                <option selected value="<?= UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($page_posts)); ?>"><?= Security::htmlentities(_("News")); ?></option>
            </select>
        </div>
        <nav class="content-nav mobile-hide">
            <ul>
                <?php if (!IS_CASINO): ?>
                <li><a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("play")); ?>"><?= Security::htmlentities(_("Play")); ?></a></li>
                <li><a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("results")); ?>"><?= Security::htmlentities(_("Results")); ?></a></li>
                <li><a href="<?= UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("lotteries")); ?>"><?= Security::htmlentities(_("Information")); ?></a></li>
                <?php endif; ?>
                <li class="content-nav-active"><a href="<?= UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($page_posts)); ?>"><?= Security::htmlentities(_("News")); ?></a></li>
                <div class="clearfix"></div>
            </ul>
        </nav>
        <section class="latest-news latest-news-columns-2">
            <?php $page_data = get_page($page_posts); ?>
            <h1 class="news-title"><?= apply_filters('the_title', $page_data->post_title); ?></h1>
            <div class="pull-right news-category-select">
                <?php
                    $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
                    $showCategories = (int)$whitelabel['show_categories'];
                    if ($showCategories && !IS_CASINO) {
                        $translatedCategoryId = 0;
                        $casinoNewsCategory = get_term_by('slug', Lotto_Widget_News::CASINO_NEWS_CATEGORY_SLUG, 'category');
                        if (!empty($casinoNewsCategory->term_id)) {
                            $translatedCategoryId = apply_filters('wpml_object_id', $casinoNewsCategory->term_id, 'category');
                        }

                        $news_cats = get_categories(array(
                                                        'orderby' => 'name',
                                                        'hide_empty' => true,
                                                        'exclude' => [$translatedCategoryId]
                                                    ));

                        if (!empty($news_cats)) {
                            echo '<select name="newscategory" id="newsCategorySelect" class="news-categories">';
                            echo '<option value="' . UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink(
                                    $page_posts
                                )) . '" selected>' . Security::htmlentities(_("show all")) . '</option>';
                            foreach ($news_cats as $cat) {
                                echo '<option value="' . htmlspecialchars(
                                        get_category_link($cat->term_id)
                                    ) . '">' . Security::htmlentities($cat->name) . '</option>';
                            }
                            echo '</select>';
                        }
                    }
                ?>
            </div>
            <?php
                // Base argument needs to be fully qualified url - e.g. https://lottopark.loc/news/%_%
                $preparedBaseUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl(get_pagenum_link(1)) . '%_%';
                echo apply_filters('the_content', $page_data->post_content);
                $links = paginate_links(array(
                    'base' => $preparedBaseUrl,
                    'current' => max(1, get_query_var('paged')),
                    'total' => $wp_query->max_num_pages,
                    'type' => 'array',
                    'prev_text' => '<span class="mobile-only"><span class="fa fa-long-arrow-left" aria-hidden="true"></span></span><span class="mobile-hide">'.Security::htmlentities(_('previous')).'</span>',
                    'next_text' => '<span class="mobile-only"><span class="fa fa-long-arrow-right" aria-hidden="true"></span></span><span class="mobile-hide">'.Security::htmlentities(_('next')).'</span>',
                ));
            ?>
            <div class="latest-news-content">
                <?php
                    $i = 0;
                    while (have_posts()):
                        $i++;
                        the_post();
                ?>
                        <div class="news-container <?php if ($i != 1): echo ' latest-news-mobile-hide'; endif; ?>">
                            <article class="news">
                                <h2><a href="<?= UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($post->ID)); ?>"><?php the_title(); ?></a></h2>
                                <?php 
                                /** @var Whitelabel */
                                $whitelabel = Container::get('whitelabel');
                                if ($whitelabel->isNotTheme(Whitelabel::LOTOKING_THEME)):?>
                                    <time datetime="<?= htmlspecialchars(get_post_time('c', true)); ?>"><span class="fa fa-clock-o" aria-hidden="true"></span> <?= Security::htmlentities(human_time_diff(get_post_time('U', true))); ?></time>
                                <?php endif;?>
                                <?php
                                    if (has_post_thumbnail()):
                                        echo '<a href="'. UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($post->ID)) .'" class="news-thumbnail">';
                                        the_post_thumbnail("thumbnail");
                                        echo '</a>';
                                    endif; ?>
                                <?php
                                    if (strpos($post->post_content, '<!--more-->')):
                                        echo apply_filters('the_content', lotto_platform_filter_news_headers(get_the_content(false)));
                                    else:
                                        echo apply_filters('the_excerpt', lotto_platform_filter_news_headers(get_the_excerpt()));
                                    endif;
                                ?>
                                <div class="clearfix"></div>
                            </article>
                        </div>
                <?php
                    endwhile;
                ?>
                <div class="grid-sizer"></div>
                <div class="gutter-sizer"></div>
                <div class="clearfix"></div>
            </div>

            <?php
                if ($links != null):
            ?>
                    <nav class="pagination">
                        <?= implode("\n", $links); ?>
                    </nav>
            <?php
                endif;
            ?>
        </section>
    </div>
</div>
<?php

get_footer();
