<?php

use Models\WhitelabelRaffle;

if (!defined('WPINC')) {
    die;
}

class Lotto_Widget_Sidebar extends WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_sidebar', // Base ID
            _('Lotto Sidebar'), // Name
            array(
                'description' => _('Display lottery playable widget in sidebar')
            ) // Args
        );
    }

    public function widget($args, $instance): void
    {
        $lottery = isset($instance['lottery']) ? htmlspecialchars($instance['lottery']) : null;

        Lotto_Helper::widget_before(false, $args);
        echo $args['before_widget'];

        if (file_exists(get_stylesheet_directory() . '/widget/sidebar/widget.php')) {
            include(get_stylesheet_directory() . '/widget/sidebar/widget.php');
        } else {
            include(get_template_directory() . '/widget/sidebar/widget.php');
        }

        echo $args['after_widget'];
        Lotto_Helper::widget_after(false, $args);

        wp_reset_postdata();
    }

    public function form($instance): void
    {
        $lottery = isset($instance['lottery']) ? htmlspecialchars($instance['lottery']) : null;

        include(LOTTO_PLUGIN_DIR . 'views/widget/sidebar/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['lottery'] = sanitize_text_field($new_instance['lottery']);

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = Model_Whitelabel::get_lotteries_by_custom_order_for_whitelabel($whitelabel['id'], ['name']);
        $whitelabel_raffle_lotteries = WhitelabelRaffle::find("all", [
            'where' => [
                'whitelabel_id' => $whitelabel['id']
            ]
        ]);

        /** @var WhitelabelRaffle $whitelabel_raffle */
        foreach ($whitelabel_raffle_lotteries as $whitelabel_raffle) {
            $lotteries[] = $whitelabel_raffle->raffle;
        }

        $slugs = [];

        foreach ($lotteries as $lottery) {
            $slugs[] = $lottery['slug'];
        }

        $lottery_explode = explode('_', $new_instance['lottery']);
        $lottery_type = $lottery_explode[0];
        $lottery_slug = $lottery_explode[1];

        if (in_array($lottery_slug, $slugs)) {
            $instance['lottery'] =  $new_instance['lottery'];
        }

        return $instance;
    }
}
