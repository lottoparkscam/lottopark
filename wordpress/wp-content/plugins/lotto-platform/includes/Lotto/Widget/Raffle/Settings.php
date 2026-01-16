<?php

use Models\Raffle;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;

if (!defined('WPINC')) {
    die;
}
/**
 * @property-read string background_image
 * @property-read string size
 * @property-read string type - slug of the lottery
 */
final class Lotto_Widget_Raffle_Settings extends Lotto_Widget_Abstract_Settings
{
    public const DB_SETTING_NAME = 'theme_mods_lottopark';

    public const WIDGET_SMALL_SIZE = 'small';
    public const WIDGET_LARGE_SIZE = 'large';
    private const LOTTOPARK_GREEN = '#41934c';
    private const WHITE = '#fff';

    protected function defaults(): array
    {
        return [
            'size' => self::WIDGET_SMALL_SIZE,
            'type' => 'gg-world-raffle',
            'useCustomColors' => false,
            'buttonTextColor' => self::LOTTOPARK_GREEN,
            'buttonTextColorOnHover' => self::WHITE,
            'buttonBackgroundColor' => self::WHITE,
            'buttonBackgroundColorOnHover' => self::LOTTOPARK_GREEN,
            'backgroundColor' => self::LOTTOPARK_GREEN,
            static::as_db_unique_key('background_image') => null,
        ];
    }

    public static function get_sizes(): array
    {
        return [self::WIDGET_SMALL_SIZE, self::WIDGET_LARGE_SIZE];
    }

    /**
     * @return array|Raffle[]
     */
    public static function get_types(): array
    {
        /** @var Raffle $dao */
        $dao = Container::get(Raffle::class);
        return $dao->push_criteria(new Model_Orm_Criteria_Where('is_enabled', true))->get_results();
    }

    public function button_play_url(): string
    {
        return lotto_platform_get_permalink_by_slug('play-raffle/' . $this->type);
    }

    public static function as_db_unique_key(string $name): string
    {
        return Lotto_Widget_Raffle_Promo::ID . '_' .$name;
    }

    public function is_small(): bool
    {
        return $this->size === self::WIDGET_SMALL_SIZE;
    }
}
