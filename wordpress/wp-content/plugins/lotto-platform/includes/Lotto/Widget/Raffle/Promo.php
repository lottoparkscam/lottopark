<?php

use Models\Raffle;
use Models\RaffleRuleTier;
use Modules\Account\Reward\PrizeType;

if (!defined('WPINC')) {
    die;
}

final class Lotto_Widget_Raffle_Promo extends Lotto_Widget_Abstract_Featured implements Lotto_Widget_Interface_Customizable
{
    public const ID = 'lotto_platform_widget_raffle';

    /** @var Raffle */
    private $raffle_dao;

    public function __construct()
    {
        parent::__construct(self::ID, _('Lotto Raffle'), ['description' => _('Displays featured raffles.')]);
        $this->raffle_dao = Container::get(Raffle::class);
    }

    /**
     * @param array $args
     * @param array $instance
     *
     * @throws Exception
     */
    public function widget($args, $instance)
    {
        $setting = new Lotto_Widget_Raffle_Settings($instance);

        $raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule($setting->type);
        $raffle_image = Lotto_View::get_lottery_image($raffle->id, null, 'raffle');
        $widgetId = $this->id;

        $is_prize_in_tickets = function (RaffleRuleTier $tier) {
            $tier_prize_in_kind = $tier->tier_prize_in_kind;
            return !empty($tier_prize_in_kind) && $tier_prize_in_kind->type === PrizeType::TICKET;
        };

        if ($is_prize_in_tickets(reset($raffle->getFirstRule()->tiers))) {
            $prize = reset($raffle->getFirstRule()->tiers)->tier_prize_in_kind->name;
        } else {
            $prize = Lotto_View::format_currency($raffle->main_prize, $raffle->currency->code);
        }

        include(get_template_directory() . '/widget/raffle/' . $setting->type . '/raffle_promo_widget_view.php');
    }

    public function form($instance)
    {
        $setting = new Lotto_Widget_Raffle_Settings($instance);
        include(LOTTO_PLUGIN_DIR . 'views/widget/raffle-promo/raffle_promo_widget_settings_view.php');
    }

    public function update($new_instance, $old_instance): array
    {
        return (new Lotto_Widget_Raffle_Settings($new_instance))->toArray();
    }

    /**
     * Adds WP type fields to customize.php.
     * It's triggered from registration function when current Widget is active.
     *
     * @param WP_Customize_Manager $manager
     */
    public static function customize(WP_Customize_Manager $manager): void
    {
        Lotto_Widget_Raffle_Customizer::configure($manager);
    }
}
