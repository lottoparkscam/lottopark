<?php

use Helpers\CurrencyHelper;

if (!defined('WPINC')) {
    die;
}

/**
 *
 */
class Lotto_Widget_Ticket extends WP_Widget
{
    const ENTITIES_COUNT = 25;

    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_ticket', // Base ID
            _('Lotto Ticket'), // Name
            [
                'description' => _("Display lottery ticket (user can choose his numbers) " .
                    "&bull; LIMIT: Results/Info/Play - Lottery Page")
            ] // Args
        );
    }

    /** @global array $post */
    public function widget($args, $instance): void
    {
        global $post;

        $whitelabel = Lotto_Settings::getInstance()->get("whitelabel");
        $lotteries = lotto_platform_get_lotteries();
        $postName = (isset($post) && $post->post_name) ? $post->post_name : '';

        $lottery = false;
        if (!empty($postName)) {
            $lottery = lotto_platform_get_lottery_by_slug($postName);
        }

        $lottery_multi_draws_options = null;
        if ($lottery) {
            if ($lottery['is_multidraw_enabled'] != 0 && $lottery['multidraws_enabled'] != 0) {
                $lottery_multi_draws_options = Model_Whitelabel_Multidraw_Option::get_whitelabel_options(
                    $whitelabel['id']
                );
            }
        }

        // Lottery type - load extending template file according to lottery type
        $widget_extension_file = 'lottery' . DIRECTORY_SEPARATOR . 'default.php';
        if ($lottery && $lottery['type'] !== 'lottery') {
            $widget_extension_file = 'lottery' . DIRECTORY_SEPARATOR . $lottery['type'] . '.php';
        }

        $lottery_type = null;

        $lottery_min_bets = -1;
        $lottery_max_bets = -1;

        if (isset($lottery) && is_array($lottery)) {
            $lottery_min_bets = $lottery['min_bets'];
            $lottery_max_bets = $lottery['max_bets'];

            $lottery_type = Lotto_Helper::get_next_lottery_type($lottery);
        }

        $title = (!empty($instance['title'])) ? $instance['title'] : '';
        $title = apply_filters('widget_title', $title, $instance, $this->id_base);

        if ($title) {
            $title = $args['before_title'] . $title . $args['after_title'];
        }

        Lotto_Helper::widget_before(false, $args);

        echo $args['before_widget'];

        if (file_exists(get_stylesheet_directory() . '/widget/ticket/widget.php')) {
            include(get_stylesheet_directory() . '/widget/ticket/widget.php');
        } else {
            include(get_template_directory() . '/widget/ticket/widget.php');
        }

        echo $args['after_widget'];

        Lotto_Helper::widget_after(false, $args);

        $lottery_bets['min_bets'] = $lottery_min_bets;
        $lottery_bets['max_bets'] = $lottery_max_bets;

        Lotto_Settings::getInstance()->set('lottery_bets', $lottery_bets);
    }

    public function form($instance): void
    {
        $title = isset($instance['title']) ? htmlspecialchars($instance['title']) : '';
        include(LOTTO_PLUGIN_DIR . 'views/widget/ticket/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        $instance = $old_instance;
        $instance['title'] = sanitize_text_field($new_instance['title']);
        return $instance;
    }
}
