<?php

if (!defined('WPINC')) {
    die;
}

final class Lotto_Widget_Small_Results extends Lotto_Widget_Abstract_Widget implements Lotto_Widget_Interface_Title
{
    const TARGET_RESULTS = 1;
    const TARGET_PLAY = 2;

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_small_results', // Base ID
            _('Lotto Small Results'), // Name
            array(
                'description' => _("Display small results widget &bull; LIMIT: none")
            ) // Args
        );
    }

    public function widget($args, $instance): void
    {
        global $post;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");

        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);
        $width = (!empty($instance['width'])) ? $instance['width'] : '50';

        // set title_container value, use div as default
        $title_container = $instance['title_container'] ?? self::TITLE_CONTAINER_H2;
        // set target value, use results as default
        $slug = (isset($instance['target']) && ($instance['target'] == self::TARGET_PLAY)) ? 'play' : 'results';
        // based on title_container create opening and ending tag for title
        $title_start_tag = $title_container === self::TITLE_CONTAINER_DIV ? '<div class="widget-div-title">' : '<h2>';
        $title_end_tag = $title_container === self::TITLE_CONTAINER_DIV ? '</div>' : '</h2>';

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

        if (file_exists(get_stylesheet_directory() . '/widget/small/results/widget.php')) {
            include(get_stylesheet_directory() . '/widget/small/results/widget.php');
        } else {
            include(get_template_directory() . '/widget/small/results/widget.php');
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
        $width = isset($instance['width']) ? $instance['width'] : '';
        $title_container = $this->form_option($instance, 'title_container');
        $target = $this->form_option($instance, 'target');

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/results/settings.php');
    }

    /**
     * @param array $new_instance
     * @param array $old_instance
     * @return array
     */
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

        // update widget title_container setting, if option doesn't exist use div as default
        $this->update_option(
            $instance,
            $new_instance,
            [self::TITLE_CONTAINER_H2, self::TITLE_CONTAINER_DIV],
            'title_container'
        );

        // update widget target setting, if option doesn't exist use 'results' as default
        $this->update_option(
            $instance,
            $new_instance,
            [self::TARGET_PLAY, self::TARGET_RESULTS],
            'target'
        );

        return $instance;
    }
}
