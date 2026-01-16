<?php

use Helpers\UrlHelper;
use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

/** @var Whitelabel */
$whitelabel = Container::get('whitelabel');

?>

<div class="home-widget-area">
    <div class="main-width">
        <?php
            if ($news_data->post_count > 0):
        ?>
                <section class="latest-news latest-news-columns-<?= $columns; ?>">
                    <?php
                        if (!empty($news_title)):
                    ?>
                            <div class="widget-latest-news-title"><?= Security::htmlentities($news_title); ?></div>
                    <?php
                        endif;
                    ?>
                    <div class="latest-news-content">
                        <?php
                            $i = 0;
                            while ($news_data->have_posts()):
                                $news_data->the_post();
                                $i++;
                        ?>
                                <div class="news-container<?php if ($i != 1): echo ' latest-news-mobile-hide'; endif; ?>">
                                    <article class="news">
                                        <h2><a href="<?= UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($news_data->post->ID)); ?>"><?php the_title(); ?></a></h2>
                                        <?php if ($whitelabel->isNotTheme(Whitelabel::LOTOKING_THEME)):?>
                                            <time datetime="<?= htmlspecialchars(get_post_time('c', true)); ?>"><span class="fa fa-clock-o" aria-hidden="true"></span> <?= Security::htmlentities(human_time_diff(get_post_time('U', true))); ?></time>
                                        <?php endif;?>
                                        <?php
                                            if (has_post_thumbnail()):
                                                echo '<a href="'. UrlHelper::changeAbsoluteUrlToCasinoUrl(get_permalink($news_data->post->ID)) .'" class="news-thumbnail">';
                                                the_post_thumbnail($columns == 1 ? "medium" : "thumbnail");
                                                echo '</a>';
                                            endif;

                                            if (strpos($news_data->post->post_content, '<!--more-->')):
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
                        if ($news_data->post_count > 0):
                    ?>
                            <nav class="pagination latest-news-mobile-pagination" data-act="1">
                                <a href="#" class="prev page-numbers page-numbers-inactive"><span class="fa fa-long-arrow-left" aria-hidden="true"></span></a>
                                <?php
                                    for ($i = 1; $i <= $news_data->post_count; $i++):
                                ?>
                                        <a href="#" class="page-numbers<?php if ($i == 1): echo ' current'; endif; ?><?php if ($i > 5): echo ' hidden-normal'; endif; ?>"><?= $i; ?></a>
                                <?php
                                    endfor;
                                ?>
                                <a href="#" class="next page-numbers"><span class="fa fa-long-arrow-right" aria-hidden="true"></span></a>
                            </nav>
                    <?php
                        endif;
                    ?>
                </section>
        <?php
            else:
        ?>
                <div class="widget-news-nonews text-center"><?= _('No latest news.') ?></div>
        <?php
            endif;
        ?>
    </div>
</div>