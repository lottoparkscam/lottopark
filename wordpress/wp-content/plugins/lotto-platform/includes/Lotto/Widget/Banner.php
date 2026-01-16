<?php

/**
 * Base widget class.
 */
class Lotto_Widget_Banner extends WP_Widget
{
    protected $registered = false;

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_banner', // Base ID
            _('Lotto Banner'), // Name
            array(
                'description' => _('Displays image on whole available width. Visibility for guests or logged in users can be adjusted.')
            ) // Args
        );

        add_action('admin_enqueue_scripts', 'wp_enqueue_media');
    }

    public function form($instance): void
    {
        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
        $settings = isset($instance['settings']) ? $instance['settings'] : '';

        include(LOTTO_PLUGIN_DIR . 'views/widget/banner/settings.php');
    }

    public function widget($args, $instance): void
    {
        Lotto_Helper::widget_before(false, $args);

        $image = '';

        if (!empty($instance['settings'][ICL_LANGUAGE_CODE]['image'])) {
            $image = $instance['settings'][ICL_LANGUAGE_CODE]['image'];
        }

        if (empty($image) && !empty($instance['settings']['en']['image'])) {
            $image = $instance['settings']['en']['image'];
        }

        $link_to = '';

        if (!empty($instance['settings'][ICL_LANGUAGE_CODE]['url'])) {
            $link_to = $instance['settings'][ICL_LANGUAGE_CODE]['url'];
        }

        if (empty($link_to) && !empty($instance['settings']['en']['url'])) {
            $link_to = $instance['settings']['en']['url'];
        }

        $altText = '';

        if (!empty($instance['settings'][ICL_LANGUAGE_CODE]['altText'])) {
            $altText = $instance['settings'][ICL_LANGUAGE_CODE]['altText'];
        }

        if (empty($altText) && !empty($instance['settings']['en']['altText'])) {
            $altText = $instance['settings']['en']['altText'];
        }

        $height = !empty($instance['height']) && is_numeric($instance['height']) ? $instance['height'] . 'px' : 'auto';
        $width = !empty($instance['width']) && is_numeric($instance['width']) ? $instance['width'] : null;
        $target = !empty($instance['target']) ? '_blank' : '_self';
        $visibility = !empty($instance['visibility']) ? $instance['visibility'] : 'all';

        echo $args['before_widget'];
        if (file_exists(get_stylesheet_directory() . '/widget/banner/widget.php')) {
            include(get_stylesheet_directory() . '/widget/banner/widget.php');
        } else {
            include(get_template_directory() . '/widget/banner/widget.php');
        }
        echo $args['after_widget'];
        Lotto_Helper::widget_after(false, $args);
    }

    public function update($new_instance, $old_instance): array
    {
        return $new_instance;
    }
}
