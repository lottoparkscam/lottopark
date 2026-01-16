<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;
use Models\Whitelabel;

/**
 * @property int $id
 * @property int $whitelabel_id
 * @property int $raffle_id
 * @property float $income
 * @property int $income_type
 * @property bool $isEnabled
 * @property bool $is_margin_calculation_enabled
 * @property int $raffle_provider_id
 * @property bool $is_bonus_balance_in_use
 *
 * @property BelongsTo|Raffle $raffle
 * @property BelongsTo|RaffleProvider $provider
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WhitelabelRaffle extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_raffle';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'raffle_id',
        'income',
        'income_type',
        'is_enabled',
        'is_margin_calculation_enabled' => ['default' => 1],
        'raffle_provider_id',
        'is_bonus_balance_in_use'
    ];

    protected static array $_belongs_to = [
        'raffle' => [
            'key_from' => 'raffle_id',
            'model_to' => Raffle::class,
            'key_to'   => 'id',
        ],
        'provider' => [
            'key_from' => 'raffle_provider_id',
            'model_to' => RaffleProvider::class,
            'key_to'   => 'id',
        ],
        'whitelabel' => [
            'key_from' => 'whitelabel_id',
            'model_to' => Whitelabel::class,
            'key_to'   => 'id',
        ],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'raffle_id' => self::CAST_INT,
        'income' => self::CAST_FLOAT,
        'income_type' => self::CAST_INT,
        'is_enabled' => self::CAST_BOOL,
        'is_margin_calculation_enabled' => self::CAST_BOOL,
        'raffle_provider_id' => self::CAST_INT,
        'is_bonus_balance_in_use' => self::CAST_BOOL
    ];

    protected static array $_has_many = [];
    protected static array $_has_one = [];
}
