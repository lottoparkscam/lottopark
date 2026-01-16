<?php

use Helpers\UrlHelper;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

/** @var Whitelabel */
$whitelabel = Container::get('whitelabel');

get_header();

$page_posts = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', false);
$category = get_category(get_query_var('cat'));
$cat_id = $category->cat_ID;
$lottery = lotto_platform_get_lottery_by_slug($category->slug);

$page_data = get_page($page_posts);

$post_title = $category->name;

if (!empty($page_data) && !empty($page_data->post_title)) {
    $post_title = $page_data->post_title.' - '.$post_title;
}
get_template_part('content', 'login-register-box-mobile');
?>
<div class="content-area home-area">
    <div class="main-width">
        <?php
            // These are settings important for showing lottery submenu properly
            $show_main_width_div = false;
            $relative_class_value = '';
            $selected_class_values = [
                0 => false,             // Play submenu
                1 => false,             // Results submenu
                2 => false,             // Information submenu
                3 => true,              // News submenu
            ];
            include('box/lottery/submenu.php');
        ?>
            <section class="latest-news latest-news-columns-2">
		<h1 class="news-title"><?= apply_filters('the_title', $post_title); ?></h1>
		<div class="pull-right news-category-select">
                    <?php
                        $translatedCategoryId = 0;
                        $casinoNewsCategory = get_term_by('slug', Lotto_Widget_News::CASINO_NEWS_CATEGORY_SLUG, 'category');
                        if (!empty($casinoNewsCategory->term_id)) {
                            $translatedCategoryId = apply_filters('wpml_object_id', $casinoNewsCategory->term_id, 'category');
                        }
                        $news_cats = get_categories(array(
                            'orderby' => 'name',
                            'hide_empty' => true,
                            'exclude' => [$translatedCategoryId],
                        ));

                        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
                        $showCategories = (int)$whitelabel['show_categories'];
                        if ($showCategories && !IS_CASINO && !empty($news_cats)) {
                            echo '<select name="newscategory" id="newsCategorySelect" class="news-categories">';
                            echo '<option value="' .
                                UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($page_posts)) . '">'.
                                Security::htmlentities(_("show all")) . '</option>';
                            foreach ($news_cats as $cat) {
                                echo '<option' . ((
                                    (isset($term) && $term == $cat->term_id) ||
                                        (!isset($term) && $cat_id == $cat->term_id)
                                ) ? ' selected' : '') . ' value="' .
                                    UrlHelper::changeAbsoluteUrlToCasinoUrl(get_category_link($cat->term_id)) . '">'.
                                    Security::htmlentities($cat->name) .
                                    '</option>';
                            }
                            echo '</select>';
                        }
                    ?>
		</div>
		<?php
                    if (!empty($page_data) && !empty($page_data->post_content)) {
                        echo apply_filters('the_content', $page_data->post_content);
                    }

                    // Base argument needs to be fully qualified url - e.g. https://lottopark.loc/news/%_%
                    $preparedBaseUrl = UrlHelper::changeAbsoluteUrlToCasinoUrl(get_pagenum_link(1)) . '%_%';
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
                                <div class="news-container<?php if ($i != 1): echo ' latest-news-mobile-hide'; endif; ?>">
                                    <article class="news">
                                        <h2><a href="<?= UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($post->ID)); ?>"><?php the_title(); ?></a></h2>
                                        <?php if ($whitelabel->isNotTheme(Whitelabel::LOTOKING_THEME)):?>
                                            <time datetime="<?= htmlspecialchars(get_post_time('c', true)); ?>"><span class="fa fa-clock-o" aria-hidden="true"></span> <?= Security::htmlentities(human_time_diff(get_post_time('U', true))); ?></time>
                                        <?php endif;?>
                                        <?php
                                            if (has_post_thumbnail()):
                                                echo '<a href="'.UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($post->ID)).'" class="news-thumbnail">';
                                                the_post_thumbnail("thumbnail");
                                                echo '</a>';
                                            endif;

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
<?php get_footer(); ?>