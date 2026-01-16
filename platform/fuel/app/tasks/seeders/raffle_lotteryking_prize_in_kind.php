<?php

namespace Fuel\Tasks\Seeders;

use Helpers\StringHelper;
use Modules\Account\Reward\PrizeType;


final class Raffle_Lotteryking_Prize_In_Kind extends Seeder
{
    public const TESLA_ID = 1;
    public const CHAIR_ID = 2;
    public const GINSENG_ID = 3;
    public const HAIR_FORMULA_ID = 4;
    public const VITAMIN_SET_ID = 5;
    public const PORK_CUTLET_ID = 6;

    # id, type, slug, per_user - values
    public const TYPES = [
        [self::TESLA_ID, 'Tesla 3', 'tesla', 45000],
        [self::CHAIR_ID, 'Massage Chair', null, 3400],
        [self::GINSENG_ID, 'RED Ginseng Set', null, 300],
        [self::HAIR_FORMULA_ID, 'Hair Formula Set', null, 200],
        [self::VITAMIN_SET_ID, 'Vitamin Set', null, 150],
        [self::PORK_CUTLET_ID, 'Pork Cutlet Set', null, 32],
    ];

    protected function columnsStaging(): array
    {
        return [
            'raffle_rule_tier_in_kind_prize' => ['id', 'name', 'slug', 'type', 'per_user']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'raffle_rule_tier_in_kind_prize' => array_map(function (array $data) {
                [$id, $name, $slug, $perUser] = $data;
                return [
                    $id,
                    $name,
                    $slug ?? StringHelper::slugify($name),
                    PrizeType::IN_KIND,
                    $perUser,
                ];
            }, self::TYPES)
        ];
    }

    public static function get_slugs(): array
    {
        return array_map(function (array $data) {
            [$id, $type, $slug, $perUser] = $data;
            return $slug ?? StringHelper::slugify($type);
        }, self::TYPES);
    }
}
