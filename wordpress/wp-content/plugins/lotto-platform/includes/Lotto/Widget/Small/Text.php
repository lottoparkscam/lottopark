<?php

if (!defined('WPINC')) {
    die;
}

final class Lotto_Widget_Small_Text extends Lotto_Widget_Abstract_Widget
{
    // 27.03.2019 16:06 Vordis TODO: Small_Text, List and Small_Results use container it could be abstracted into better shape (widgets would share all aside from container values)
    /**
     * Value for h2 option in title_container select.
     */
    const TITLE_CONTAINER_H2 = 1;

    // Value for h1 option in title_container select.
    const TITLE_CONTAINER_H1 = 2;

    protected $registered = false;

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_small_text', // Base ID
            _('Lotto Small Text'), // Name
            array(
                'description' => _("Display small text &bull; LIMIT: none")
            ) // Args
        );
    }

    protected function get_lotteries_to_show(): bool
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        $sidebars = wp_get_sidebars_widgets();
        $widget_sidebar = null;
        foreach ($sidebars as $sidebar => $widgets) {
            foreach ($widgets as $key => $widget) {
                if ($this->id == $widget) {
                    $widget_sidebar = $sidebar;
                }
            }
        }

        $widget_ids = array(
            'lottery-info-content-sidebar-id',
            'lottery-info-sidebar-id',
            'lottery-info-more-sidebar-id',
            'lottery-results-sidebar-id',
            'lottery-results-more-sidebar-id',
            'lottery-results-content-sidebar-id',
            'play-lottery-sidebar-id'
        );

        $show_lotteries = false;
        if (!empty($widget_sidebar) && in_array($widget_sidebar, $widget_ids)) {
            $show_lotteries = true;
        }

        return $show_lotteries;
    }

    public function widget($args, $instance): void
    {
        if (!isset($instance['wpml_language'])) {
            return;
        }

        if ($instance['wpml_language'] !== ICL_LANGUAGE_CODE) {
            return;
        }

        global $post;
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        $title = "";
        $content = "";

        if (isset($post) && isset($lotteries['__by_slug'][$post->post_name])) {
            $id = $lotteries['__by_slug'][$post->post_name]['id'];
            $title = '';
            if (isset($instance['settings']['lotteries'][$id]['title'])) {
                $title = $instance['settings']['lotteries'][$id]['title'];
            }
            $content = '';
            if (isset($instance['settings']['lotteries'][$id]['content'])) {
                $content = $instance['settings']['lotteries'][$id]['content'];
            }
        } else {
            $title = '';
            if (!empty($instance['title'])) {
                $title = $instance['title'];
            }
            $content = '';
            if (isset($instance['content'])) {
                $content = wp_kses_post($instance['content']);
            }
        }

        // set title_container value, use h2 as default
        $title_container = $instance['title_container'] ?? self::TITLE_CONTAINER_H2;
        // based on title_container create opening and ending tag for title
        $title_start_tag = $title_container === self::TITLE_CONTAINER_H1 ? '<h1 class="small-widget-title">' : '<h2 class="small-widget-title">';
        $title_end_tag = $title_container === self::TITLE_CONTAINER_H1 ? '</h1>' : '</h2>';

        Lotto_Helper::widget_before(true, $args);

        $width = (!empty($instance['width'])) ? $instance['width'] : '50';

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

        if (file_exists(get_stylesheet_directory() . '/widget/small/text/widget.php')) {
            include(get_stylesheet_directory() . '/widget/small/text/widget.php');
        } else {
            include(get_template_directory() . '/widget/small/text/widget.php');
        }

        echo $args['after_widget'];

        if ($full) {
            echo '<div class="clearfix"></div>';
        }

        Lotto_Helper::widget_after(true, $args);
    }

    public function form($instance)
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        $show_lotteries = $this->get_lotteries_to_show();
        $settings = isset($instance['settings']) ? $instance['settings'] : '';
        $title = isset($instance['title']) ? Security::htmlentities($instance['title']) : '';
        $content = isset($instance['content']) ? wp_kses_post($instance['content']) : '';
        $width = isset($instance['width']) ? $instance['width'] : '';

        $title_container = $instance['title_container'] ?? self::TITLE_CONTAINER_H2;

        include(LOTTO_PLUGIN_DIR . 'views/widget/small/text/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $show_lotteries = $this->get_lotteries_to_show();
        $instance = $old_instance;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Lottery::get_lotteries_for_whitelabel($whitelabel);

        if (!$show_lotteries) {
            $instance['title'] = sanitize_text_field($new_instance['title'] ?? '');
            $instance['content'] = wp_kses_post($new_instance['content'] ?? '');

            $action_text = 'Lotto Small Text - Content';

            do_action(
                'wpml_register_single_string',
                'Widgets',
                $action_text,
                $instance['content']
            );
        } elseif (isset($new_instance['settings'])) {
            foreach ($new_instance['settings'] as $key => &$setting) {
                if (in_array($key, array('lotteries'))) {
                    foreach ($setting as $key2 => &$lottery) {
                        $lottery['title'] = sanitize_text_field($lottery['title']);
                        $lottery['content'] = wp_kses_post($lottery['content']);
                        $id = explode("-", $this->id);

                        $action_text = 'Lotto Small Text - ID' . $id[1] .
                            ' - Content ' . $lotteries['__by_id'][$key2]['name'];

                        do_action(
                            'wpml_register_single_string',
                            'Widgets',
                            $action_text,
                            $lottery['content']
                        );
                    }
                }
            }
        }

        if (isset($new_instance['settings'])) {
            $instance['settings'] = $new_instance['settings'];
        }

        $instance['width'] = intval($new_instance['width'] ?? 100);
        if ($instance['width'] > 100) {
            $instance['width'] = 100;
        }
        if ($instance['width'] <= 0) {
            $instance['width'] = 50;
        }

        // update widget title_container setting, if option doesn't exist use div as default
        $this->update_option($instance, $new_instance, [self::TITLE_CONTAINER_H2, self::TITLE_CONTAINER_H1], 'title_container');

        return $instance;
    }
}
