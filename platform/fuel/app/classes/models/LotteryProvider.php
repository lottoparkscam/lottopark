<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;
use Orm\HasMany;

/**
 * @property int $id
 * @property int $lottery_id
 * @property int $provider
 * @property int $min_bets
 * @property int $max_bets
 * @property int $multiplier
 * @property int $offset
 *
 * @property string $closing_time
 * @property string $timezone
 * @property string|null $data
 *
 * @property float $tax
 * @property float $tax_min
 * @property float|null $fee
 * @property float|null $scan_cost
 * @property float|null $scan_fee
 * @property float $max_payout
 *
 * @property array $closing_times
 *
 * @property BelongsTo|Lottery|null $lottery
 *
 * @property HasMany|WhitelabelLottery[]|null $whitelabel_lotteries
 * @property HasMany|WhitelabelUserTicket[]|null $whitelabel_user_tickets
 */
class LotteryProvider extends AbstractOrmModel
{
    protected static $_table_name = 'lottery_provider';

    protected static $_properties = [
        'id',
        'lottery_id',
        'provider',
        'min_bets',
        'max_bets',
        'multiplier',
        'closing_time',
        'timezone',
        'offset',
        'tax',
        'tax_min',
        'data',
        'fee',
        'scan_cost',
        'scan_fee',
        'max_payout',
        'closing_times'
    ];

    protected $casts = [
        'id'            => self::CAST_INT,
        'lottery_id'    => self::CAST_INT,
        'provider'      => self::CAST_INT,
        'min_bets'      => self::CAST_INT,
        'max_bets'      => self::CAST_INT,
        'multiplier'    => self::CAST_INT,
        'offset'        => self::CAST_INT,

        'tax'           => self::CAST_FLOAT,
        'tax_min'       => self::CAST_FLOAT,
        'fee'           => self::CAST_FLOAT,
        'scan_cost'     => self::CAST_FLOAT,
        'scan_fee'      => self::CAST_FLOAT,
        'max_payout'    => self::CAST_FLOAT,

        'closing_times' => self::CAST_ARRAY
    ];

    protected static array $_belongs_to = [
        'lottery' => [
            'key_from' => 'lottery_id',
            'model_to' => Lottery::class,
            'key_to' => 'id'
        ]
    ];

    protected static array $_has_many = [
        'whitelabel_user_tickets' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserTicket::class,
            'key_to' => 'lottery_provider_id'
        ],
        'whitelabel_lotteries' => [
            'key_from' => 'id',
            'model_to' => WhitelabelLottery::class,
            'key_to' => 'lottery_provider_id'
        ]
    ];
}
