<?php

namespace Fuel\Tasks\Seeders;

use Helpers\StringHelper;
use Modules\Account\Reward\PrizeType;

final class MonksRaffleClosedPrizeInKind extends Seeder
{
    use \Without_Foreign_Key_Checks;

    public const EUROMILLIONS = 11;
    public const MEGAMILIONS = 12;
    public const GGWORLDX = 13;
    public const GGWORLDMILION = 14;

    # id, name, slug, per_user, config - values
    # lottery slug must be the same as in lotteries db!
    public const TYPES = [
        [self::EUROMILLIONS, '100 x EuroMillions', '100x-euromillions', 250, ['count' => 100]],
        [self::MEGAMILIONS, '10 x Mega Millions tickets', '10x-mega-millions', 21.5, ['count' => 10]],
        [self::GGWORLDX, '5 x GG World X tickets', '5x-gg-world-x', 4, ['count' => 5]],
        [self::GGWORLDMILION, '1 x GG World Million ticket', '1x-gg-world-million', 0.4, ['count' => 1]],
    ];

    /**
     * Define columns used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [col1...coln]
     */
    protected function columnsStaging(): array
    {
        return [
            'raffle_rule_tier_in_kind_prize' => ['id', 'name', 'slug', 'type', 'per_user', 'config']
        ];
    }

    /**
     * Define rows used by seeder.
     * NOTE: can be for many tables.
     *
     * @return array format 'table' => [row1[val1...valn]...rown[val1...valn]]
     */
    protected function rowsStaging(): array
    {
        return [
            'raffle_rule_tier_in_kind_prize' => array_map(function (array $data) {
                [$id, $name, $slug, $perUser, $config] = $data;
                return [
                    $id,
                    $name,
                    $slug ?? StringHelper::slugify($name),
                    PrizeType::TICKET,
                    $perUser,
                    json_encode($config)
                ];
            }, self::TYPES)
        ];
    }

    public static function get_slugs(): array
    {
        return array_map(function (array $data) {
            [, $name, $slug, , ] = $data;
            return $slug ?? StringHelper::slugify($name);
        }, self::TYPES);
    }
}
