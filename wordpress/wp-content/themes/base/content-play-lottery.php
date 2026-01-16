<?php
if (!defined('WPINC')) {
    die;
}

$lottery = lotto_platform_get_lottery_by_slug($post->post_name);
list(
    $social_share_rows,
    $counter_socials,
    $current_url
    ) = Helpers_General::get_prepared_social_share_links();

$add_main_classes = Lotto_Helper::get_widget_main_area_classes(
    "play-lottery-sidebar-id",
    "play-lottery-more-sidebar-id"
);
?>
<div class="content-area <?= $add_main_classes; ?>">
    <?php
    // These are settings important for showing lottery submenu properly
    $category = null;
    $show_main_width_div = true;
    $relative_class_value = 'relative';
    $selected_class_values = [
        0 => true,              // Play submenu
        1 => false,             // Results submenu
        2 => false,             // Information submenu
        3 => false,             // News submenu
    ];
    include('box/lottery/submenu.php');

    get_active_sidebar('play-lottery-sidebar-id');

    if (!empty($lottery) &&
        is_array($lottery) &&
        $lottery['is_temporarily_disabled'] == 0
    ) {
        the_widget("Lotto_Widget_Ticket");
    }

    $bottom_top_classes = Lotto_Helper::get_widget_bottom_area_classes("play-lottery-more-sidebar-id");
    if (empty($lottery)) {
        $bottom_top_classes .= ' play-box-nolottery' .
            Lotto_Helper::get_widget_top_area_classes("play-lottery-sidebar-id");
    }
    ?>
    <div class="main-width content-width">
        <div class="content-box play-box <?= $bottom_top_classes; ?>">
            <section class="page-content page-content-more">
                <article class="page">
                    <?php
                    if (empty($lottery)):
                        ?>
                        <h1><?php the_title(); ?></h1>
                    <?php
                    endif;

                    $group = null;
                    if (!empty($lottery) && !empty($lottery['group_id'])) {
                        $group = Lotto_Helper::get_grouped_lotteries($lottery['group_id']);
                    }
                    if (empty($group)) {
                        the_content();
                    } else {
                        foreach ($group as $group_lottery) {
                            if ($lottery['id'] === $group_lottery['id']) {
                                echo '<div class="group_play_content">';
                                the_content();
                                echo '</div>';
                            } else {
                                $lottery_post_id = lotto_platform_get_post_id_by_slug("play/" . $group_lottery['slug']);
                                $lottery_post = get_post($lottery_post_id);
                                echo '<div class="group_play_content hidden-normal">';
                                if (!empty($lottery_post) && !empty($lottery_post->post_content)) {
                                    $lottery_post_content = apply_filters('the_content', $lottery_post->post_content);
                                    echo $lottery_post_content;
                                }
                                echo '</div>';
                            }
                        }
                    }

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
    get_active_sidebar('play-lottery-more-sidebar-id');
    ?>
</div>
