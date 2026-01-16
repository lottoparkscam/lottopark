<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $slug
 * @property array $config - some custom configuration for specific cases casted to json in db and to array here
 * @property float $per_user
 */
class RaffleRuleTierInKindPrize extends AbstractOrmModel
{
    protected static $_table_name = 'raffle_rule_tier_in_kind_prize';

    protected static $_properties = [
        'id',
        'type',
        'name',
        'slug',
        'config',
        'per_user'
    ];

    protected $casts = [
        'per_user' => self::CAST_FLOAT,
        'config' => self::CAST_ARRAY
    ];
}
