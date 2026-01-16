<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;
use Orm\HasOne;

/**
 * @property int $id
 * @property int $raffle_id
 * @property int $provider
 * @property int $multiplier
 * @property string $closing_time
 * @property string $timezone
 * @property int $min_bets
 * @property int $max_bets
 * @property int $offset
 * @property int $tax
 * @property int $tax_min
 * @property string $data
 * @property BelongsTo|Raffle $raffle
 * @property HasOne|WhitelabelRaffle $raffleProvider
 */
class RaffleProvider extends AbstractOrmModel
{
    protected static $_table_name = 'raffle_provider';

    protected static $_properties = [
        'id',
        'raffle_id',
        'provider'   => ['default' => 3], # 3 - is lcs?
        'min_bets',
        'max_bets',
        'multiplier' => ['default' => 0],
        'closing_time',
        'timezone',
        'offset',
        'tax',
        'tax_min',
        'data',
    ];

    protected $casts = [
        'id'         => self::CAST_INT,
        'raffle_id'  => self::CAST_INT,
        'provider'   => self::CAST_INT,
        'min_bets'   => self::CAST_INT,
        'max_bets'   => self::CAST_INT,
        'multiplier' => self::CAST_INT,
        'offset'     => self::CAST_INT,
        'tax'        => self::CAST_FLOAT,
        'tax_min'    => self::CAST_FLOAT,
    ];

    protected static array $_belongs_to = [];
    protected static array $_has_many = [];
    protected static array $_has_one = [];

    protected array $relations = [
        Raffle::class => self::BELONGS_TO,
        WhitelabelRaffle::class => self::HAS_ONE
    ];
}
