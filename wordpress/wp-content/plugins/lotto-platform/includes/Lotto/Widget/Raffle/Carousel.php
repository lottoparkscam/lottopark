<?php

use Models\Whitelabel;

if (!defined('WPINC')) {
    die;
}

final class Lotto_Widget_Raffle_Carousel extends Lotto_Widget_Abstract_Widget implements Lotto_Widget_Interface_Title
{
    public function __construct()
    {
        parent::__construct(
            'lotto_platform_widget_raffle_carousel',
            _('Lotto Raffle Carousel'),
            [
                'description' => _('Displays the carousel of raffle &bull; LIMIT: none')
            ]
        );
    }

    public function widget($args, $instance): void
    {
        $whitelabel = Lotto_Settings::getInstance()->get('whitelabel');

        $lotteries = Model_Raffle::getActiveRaffleForWhitelabel($whitelabel['id']);
        $lotteries = $lotteries['__by_slug'];
        $count = count($lotteries);

        Lotto_Helper::widget_before(false, $args);

        echo $args['before_widget'];

        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');

        if (file_exists(get_stylesheet_directory() . '/widget/raffle/carousel/widget.php')) {
            include(get_stylesheet_directory() . '/widget/raffle/carousel/widget.php');
        } else {
            include(get_template_directory() . '/widget/raffle/carousel/widget.php');
        }

        echo $args['after_widget'];

        Lotto_Helper::widget_after(false, $args);
    }

    public function form($instance): void
    {
        // include(LOTTO_PLUGIN_DIR . 'views/widget/raffle/carousel/settings.php');
    }

    public function update($new_instance, $old_instance): array
    {
        return $old_instance;
    }
}
