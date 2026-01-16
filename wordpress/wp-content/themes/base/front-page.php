<?php

use Helpers\AssetHelper;
use Helpers\UrlHelper;
use Models\Whitelabel;
use Services\Logs\FileLoggerService;

if (!defined('WPINC')) {
    die;
}

/** @var Whitelabel */
$whitelabel = Container::get('whitelabel');

get_header();

?>
<script>
    window.resendLink = '<?= lotto_platform_home_url('/') . 'gresend/' ?>';
    window.activationText = <?= json_encode(_(
                                    'We have sent you an e-mail with the activation link. ' .
                                        'Please activate your e-mail for better website experience. ' .
                                        'You can resend the activation e-mail <a href="%s">here</a>.'
                                )) ?>;
</script>
<?php
if (IS_CASINO) :
    require_once('template-casino.php');
elseif (is_front_page()) :
?>
<div id="flashmessages"></div>
<?php
    get_template_part('content', 'login-register-box-mobile');

    if (is_active_sidebar('frontpage-sidebar-id')) :
        Lotto_Helper::widget_before_area('frontpage-sidebar-id');
        ?>
        <div class="home-widget-area<?php echo Lotto_Helper::get_widget_home_area_classes('frontpage-sidebar-id'); ?>">
            <div class="main-width content-width">
                <?php
                    try {
                        // Note: we check this setting here, as we do not want to load multiple javascript files without need
                        $isPromoSliderEnabled = get_theme_mod('display_lotto_promo_slider');
                        if ($isPromoSliderEnabled):
                            // Note: include_once can be caught by Throwable, require_once cannot be caught
                            include_once('template-lotto-promo-slider.php');
                        endif;
                    } catch (Throwable $exception) {
                        /** @var FileLoggerService $fileLoggerService */
                        $fileLoggerService = Container::get(FileLoggerService::class);
                        $fileLoggerService->error('Something went wrong while trying to display promo slider in lotto home page. Detailed message: ' . $exception->getMessage());
                    }
                ?>
            </div>
            <?php
            dynamic_sidebar('frontpage-sidebar-id');
            Lotto_Helper::widget_after_area('frontpage-sidebar-id');
            if (is_home() && have_posts()) :
                ?>
                <div class="main-width">
                    <section class="latest-news latest-news-columns-2">
                        <div class="widget-latest-news-title">
                            <?= _("Latest news") ?>
                        </div>
                        <div class="latest-news-content">
                            <?php
                            $i = 0;
                            while (have_posts()) :
                                the_post();
                                $i++;
                                ?>
                                <div class="news-container<?php if ($i != 1) :
                                    echo ' latest-news-mobile-hide';
                                                          endif; ?>">
                                    <article class="news">
                                        <h2><a href="<?php echo UrlHelper::esc_url(get_permalink($post->ID)); ?>"><?php the_title(); ?></a></h2>
                                        <?php if ($whitelabel->isNotTheme(Whitelabel::LOTOKING_THEME)):?>
                                            <time datetime="<?php echo htmlspecialchars(get_post_time('c', true)); ?>">
                                                <span class="fa fa-clock-o" aria-hidden="true"></span> <?php
                                                                                                        echo Security::htmlentities(human_time_diff(get_post_time('U', true)));
                                                ?>
                                            </time>
                                        <?php endif;?>
                                        <?php
                                        if (has_post_thumbnail()) :
                                            echo '<a href="' . get_permalink($post->ID) . '" class="news-thumbnail">';
                                            the_post_thumbnail("thumbnail");
                                            echo '</a>';
                                        endif;

                                        if (strpos($post->post_content, '<!--more-->')) :
                                            echo apply_filters('the_content', lotto_platform_filter_news_headers(get_the_content(false)));
                                        else :
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
                        if ($wp_query->post_count > 0) :
                            ?>
                            <nav class="pagination latest-news-mobile-pagination" data-act="1">
                                <a href="#" class="prev page-numbers page-numbers-inactive">
                                    <span class="fa fa-long-arrow-left" aria-hidden="true"></span>
                                </a>
                                <?php
                                for ($i = 1; $i <= $wp_query->post_count; $i++) :
                                    ?>
                                    <a href="#" class="page-numbers<?php
                                    if ($i == 1) :
                                        echo ' current';
                                    endif;
                                    if ($i > 5) :
                                        echo ' hidden-normal';
                                    endif;
                                    ?>">
                                        <?php echo $i; ?>
                                    </a>
                                    <?php
                                endfor;
                                ?>
                                <a href="#" class="next page-numbers">
                                    <span class="fa fa-long-arrow-right" aria-hidden="true"></span>
                                </a>
                            </nav>
                            <?php
                        endif;
                        ?>
                    </section>
                </div>
            <?php endif; ?>
        </div>
    <?php
    endif;
endif;

get_footer();
