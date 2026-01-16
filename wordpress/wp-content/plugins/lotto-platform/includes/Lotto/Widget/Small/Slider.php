<?php

if (!defined('WPINC')) {
    die;
}

class Lotto_Widget_Small_Slider extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_small_slider', // Base ID
            _('Lotto Small Slider'), // Name
            array(
                'description' => _("Display small slider widget &bull; LIMIT: none")
            ) // Args
        );

        if (is_active_widget(false, false, $this->id_base, true)) {
            add_action('init', array($this, 'init'));
        }
    }

    public function init(): void
    {
        $args = array(
            'public' => true,
            'publicly_queryable' => false,
            'rewrite' => false,
            'label' => _('Small Slider'),
            'menu_position' => 20,
            'menu_icon' => 'dashicons-admin-page',
            'supports' => array('title', 'editor', 'thumbnail', 'page-attributes'), // custom-fields ?
            'show_in_nav_menus' => false
        );
        register_post_type('slider', $args);
    }

    public function widget($args, $instance): void
    {
        global $post;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        $width = (!empty($instance['width'])) ? $instance['width'] : '50';

        $query = array(
            'post_type' => 'slider',
            'order' => 'asc',
            'orderby' => 'menu_order',
            'posts_per_page' => -1
        );
        $slider = new WP_Query($query);

        Lotto_Helper::widget_before(true, $args);

        list(
            $width,
            $margin_left,
            $margin_right,
            $full
        ) = (Lotto_Helper::calculate_widget_width($width, $args));

        $style_text = ' style="width: ' . $width .
            '; margin-left: ' . $margin_left .
            '; margin-right: ' . $margin_right . ';">';
        echo str_replace('>', $style_text, $args['before_widget']);

        if (file_exists(get_stylesheet_directory() . '/widget/small/slider/widget.php')) {
            include(get_stylesheet_directory() . '/widget/small/slider/widget.php');
        } else {
            include(get_template_directory() . '/widget/small/slider/widget.php');
        }

        echo $args['after_widget'];

        if ($full) {
            echo '<div class="clearfix"></div>';
        }

        Lotto_Helper::widget_after(true, $args);
        wp_reset_postdata();
    }

    public function form($instance): void
    {
        $title = isset($instance['title']) ? htmlspecialchars($instance['title']) : '';
        $width = isset($instance['width']) ? $instance['width'] : '';

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/slider/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
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
