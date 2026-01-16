<?php

namespace Fuel\Tasks\Seeders;

use Helpers\StringHelper;
use Modules\Account\Reward\PrizeType;


final class Faireum_Raffle_Two_Closed_Prize_In_Kind extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production;

    public const POWERBALL = 7;
    public const MEGAMILIONS = 8;
    public const GGWORLDX = 9;
    public const GGWORLDMILION = 10;

    # id, name, slug, per_user, config - values
    # lottery slug must be the same as in lotteries db!
    public const TYPES = [
        [self::POWERBALL, '100 x Powerball tickets', 'powerball', 325, ['count' => 100]],
        [self::MEGAMILIONS, '10 x Mega Millions tickets', 'mega-millions', 31.5, ['count' => 10]],
        [self::GGWORLDX, '5 x GG World X tickets', 'gg-world-x', 9, ['count' => 5]],
        [self::GGWORLDMILION, '1 x GG World Million ticket', 'gg-world-million', 1.4, ['count' => 1]],
    ];

    protected function columnsStaging(): array
    {
        return [
            'raffle_rule_tier_in_kind_prize' => ['id', 'name', 'slug', 'type', 'per_user', 'config']
        ];
    }

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
