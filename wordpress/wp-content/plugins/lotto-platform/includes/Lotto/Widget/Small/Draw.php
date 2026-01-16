<?php

if (!defined('WPINC')) {
    die;
}

class Lotto_Widget_Small_Draw extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_small_lottery_draw', // Base ID
            _('Lotto Small Next Draw'), // Name
            array(
                'description' => _("Display small lottery next draw widget &bull; LIMIT: none")
            ) // Args
        );
    }

    public function widget($args, $instance): void
    {
        global $post;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        $klotteries = array_keys($lotteries['__by_slug']);
        $categories = get_the_category();
        $setlottery = '';

        if (!empty($categories)) {
            foreach ($categories as $key => $category) {
                if (in_array($category->slug, $klotteries) && $setlottery == '') {
                    $setlottery = $category->slug;
                }
            }
        }

        //$title = (!empty($instance['title'])) ? $instance['title'] : '';
        $content = isset($instance['content']) ? Security::htmlentities($instance['content']) : '';
        if (isset($post) && isset($lotteries['__by_slug'][$post->post_name])) {
            $lottery = $lotteries['__by_slug'][$post->post_name];
        } elseif (!empty($setlottery)) {
            $lottery = $lotteries['__by_slug'][$setlottery];
        } else {
            $lottery = null;
            if (
                isset($instance['lottery']) &&
                isset($lotteries['__by_id'][$instance['lottery']])
            ) {
                $lottery = $lotteries['__by_id'][$instance['lottery']];
            }
        }

        $width = (!empty($instance['width'])) ? $instance['width'] : '50';

        if (empty($instance['inline'])) {
            Lotto_Helper::widget_before(true, $args);
        }

        $full = false;
        $margin_left = 0;
        $margin_right = 0;

        if (empty($instance['inline'])) {
            list(
                $width,
                $margin_left,
                $margin_right,
                $full
            ) = (Lotto_Helper::calculate_widget_width($width, $args));
        }

        if (empty($instance['inline'])) {
            $style_text = ' style="width: ' . $width .
                '; margin-left: ' . $margin_left .
                '; margin-right: ' . $margin_right . ';">';

            echo str_replace('>', $style_text, $args['before_widget']);
        } else {
            echo $args['before_widget'];
        }

        if (file_exists(get_stylesheet_directory() . '/widget/small/draw/widget.php')) {
            include(get_stylesheet_directory() . '/widget/small/draw/widget.php');
        } else {
            include(get_template_directory() . '/widget/small/draw/widget.php');
        }

        echo $args['after_widget'];

        if ($full) {
            echo '<div class="clearfix"></div>';
        }

        if (empty($instance['inline'])) {
            Lotto_Helper::widget_after(true, $args);
        }
    }

    public function form($instance): void
    {
        $content = isset($instance['content']) ? Security::htmlentities($instance['content']) : '';
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        $lottery = 1;
        if (
            isset($instance['lottery']) &&
            isset($lotteries['__by_id'][$instance['lottery']])
        ) {
            // TODO: I really dont know if this is OK, but I left that as it was before
            $lottery = $instance['lottery'];
        }

        //$mobilehide = (isset($instance['mobilehide']) && $instance['mobilehide'] == 1) ? 1 : 0;
        $width = isset($instance['width']) ? $instance['width'] : '';

        $sidebars = wp_get_sidebars_widgets();
        $widget_sidebar = null;
        foreach ($sidebars as $sidebar => $widgets) {
            foreach ($widgets as $key => $widget) {
                if ($this->id == $widget) {
                    $widget_sidebar = $sidebar;
                }
            }
        }
        $show_lotteries = false;

        $widget_ids = array(
            'lottery-info-content-sidebar-id',
            'lottery-info-sidebar-id',
            'lottery-info-more-sidebar-id',
            'lottery-results-sidebar-id',
            'lottery-results-more-sidebar-id',
            'lottery-results-content-sidebar-id',
            'play-lottery-sidebar-id',
            'lottery-news-content-sidebar-id'
        );

        if (!empty($widget_sidebar) && !in_array($widget_sidebar, $widget_ids)) {
            $show_lotteries = true;
        }

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/draw/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;

        if (!isset($new_instance['content'])) {
            $new_instance['content'] = "";
        }

        if (!isset($new_instance['lottery'])) {
            $new_instance['lottery'] = 1;
        }

        //$instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['content'] = sanitize_text_field($new_instance['content']);
        //$instance['mobilehide'] = (isset($new_instance['mobilehide'])
        //&& $new_instance['mobilehide']) == 1 ? 1 : 0;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        $instance['lottery'] = 1;
        if (
            isset($new_instance['lottery']) &&
            $lotteries['__by_id'][$new_instance['lottery']]
        ) {
            // TODO: I really dont know if this is OK, but I left that as it was before
            $instance['lottery'] = $new_instance['lottery'];
        }

        $instance['width'] = intval($new_instance['width']);
        if ($instance['width'] > 100) {
            $instance['width'] = 100;
        }
        if ($instance['width'] <= 0) {
            $instance['width'] = 50;
        }

        return $instance;
    }
}
