<?php

use Helpers\UrlHelper;

if (!defined('WPINC')) {
    die;
}

$page_posts = apply_filters('wpml_object_id', get_option('page_for_posts'), 'page', false);

list(
    $social_share_rows,
    $counter_socials,
    $current_url
) = Helpers_General::get_prepared_social_share_links();

$widget_main_area_classes = Lotto_Helper::get_widget_main_area_classes(
    null,
    "play-more-sidebar-id"
);
?>
<div class="content-area <?= $widget_main_area_classes; ?> <?php echo (basename(get_permalink()) === basename(UrlHelper::esc_url(lotto_platform_get_permalink_by_slug("keno")))) ? 'page-keno-show-only-keno' : null; ?>">
    <?php
        get_content_main_menu($page_posts, 'play');
    
        get_active_sidebar('play-sidebar-id');
    
        if (!empty(get_the_content())):
            $top_bottom_area_classes = Lotto_Helper::get_widget_top_area_classes("play-sidebar-id");
            $top_bottom_area_classes .= Lotto_Helper::get_widget_bottom_area_classes("play-more-sidebar-id");
    ?>
            <div class="main-width content-width">
		<div class="content-box <?= $top_bottom_area_classes; ?>">
                    <section class="page-content">
                        <article class="page">
                            <h1><?php the_title(); ?></h1>
                            <?php
                                the_content();

                                base_theme_social_share_bottom(
                                    $social_share_rows,
                                    $counter_socials,
                                    $current_url
                                );
                            ?>
                        </article>
                    </section>
		</div>
            </div>
    <?php
        endif;
    
        get_active_sidebar('play-more-sidebar-id');
    ?>
</div>