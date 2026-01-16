<?php

if (!defined('WPINC')) {
    die;
}

class Lotto_Widget_Small_Lottery extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_small_lottery', // Base ID
            _('Lotto Small Lottery'), // Name
            array(
                'description' => _("Display small lottery widget &bull; LIMIT: none")
            ) // Args
        );
    }

    public function widget($args, $instance): void
    {
        global $post;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $content = isset($instance['content']) ? Security::htmlentities($instance['content']) : '';
        $lottery = isset($lotteries['__by_id'][$instance['lottery']]) ? $lotteries['__by_id'][$instance['lottery']] : null;
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        $mobilehide = (isset($instance['mobilehide']) && $instance['mobilehide'] == 1) ? 1 : 0;
        $width = /* (!empty($instance['width'])) ? $instance['width'] : */ '50';
        if ($title) {
            // remove h2 from this one
            //$title = $args['before_title'].$title.$args['after_title'];
        }
        Lotto_Helper::widget_before(true, $args);

        list($width, $margin_left, $margin_right, $full) = (Lotto_Helper::calculate_widget_width($width, $args));

        if ($mobilehide) {
            $args['before_widget'] = str_replace(
                "widget widget_lotto_platform_widget_small_lottery",
                "widget widget_lotto_platform_widget_small_lottery small-widget-lottery-mobile-hide",
                $args['before_widget']
            );
        }

        echo str_replace(
            '>',
            ' style="width: ' . $width . '; margin-left: ' . $margin_left . '; margin-right: ' . $margin_right . ';">',
            $args['before_widget']
        );

        if (file_exists(get_stylesheet_directory() . '/widget/small/lottery/widget.php')) {
            include(get_stylesheet_directory() . '/widget/small/lottery/widget.php');
        } else {
            include(get_template_directory() . '/widget/small/lottery/widget.php');
        }
        echo $args['after_widget'];
        if ($full) {
            echo '<div class="clearfix"></div>';
        }
        Lotto_Helper::widget_after(true, $args);
    }

    public function form($instance): void
    {
        $title = isset($instance['title']) ? htmlspecialchars($instance['title']) : '';
        $content = isset($instance['content']) ? Security::htmlentities($instance['content']) : '';
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
        $lottery = (isset($instance['lottery']) && isset($lotteries['__by_id'][$instance['lottery']])) ? $instance['lottery'] : 1;
        $mobilehide = (isset($instance['mobilehide']) && $instance['mobilehide'] == 1) ? 1 : 0;

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/lottery/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        $instance['content'] = sanitize_text_field($new_instance['content']);
        $instance['mobilehide'] = (isset($new_instance['mobilehide']) && $new_instance['mobilehide']) == 1 ? 1 : 0;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);
        $instance['lottery'] = isset($lotteries['__by_id'][$new_instance['lottery']]) ? $new_instance['lottery'] : 1;

        return $instance;
    }
}
