<?php

if (!defined('WPINC')) {
    die;
}

class Lotto_Widget_Promo extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_promo',
            _('Lotto Promo'),
            [
                'description' => _('Widget explaining how to play lotteries online')
            ]
        );
    }

    /** @global array $post */
    public function widget($args, $instance): void
    {
        global $post;

        $visibility = (!empty($instance['visibility'])) ? $instance['visibility'] : '';
        $video = (!empty($instance['video'])) ? $instance['video'] : '';
        $image = (!empty($instance['image'])) ? $instance['image'] : '';

        Lotto_Helper::widget_before(false, $args);

        echo $args['before_widget'];

        if (file_exists(get_stylesheet_directory() . '/widget/promo/widget.php')) {
            include(get_stylesheet_directory() . '/widget/promo/widget.php');
        } else {
            include(get_template_directory() . '/widget/promo/widget.php');
        }

        echo $args['after_widget'];

        Lotto_Helper::widget_after(false, $args);
    }

    public function form($instance): void
    {
        $visibility = isset($instance['visibility']) ? htmlspecialchars($instance['visibility']) : '';
        $video = isset($instance['video']) ? htmlspecialchars($instance['video']) : '';
        $image = isset($instance['image']) ? htmlspecialchars($instance['image']) : '';
        include(LOTTO_PLUGIN_DIR . 'views/widget/promo/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['visibility'] = sanitize_text_field($new_instance['visibility']);
        $instance['video'] = sanitize_text_field($new_instance['video']);
        $instance['image'] = sanitize_text_field($new_instance['image']);
        return $instance;
    }
}
