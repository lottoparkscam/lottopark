<?php

namespace Models;

use Orm\BelongsTo;
use Orm\HasOne;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $lottery_id
 * @property int $ncount
 * @property int $bcount
 * @property int $nrange
 * @property int $brange
 * @property int $bextra
 * @property int $def_insured_tiers
 *
 * @property float $odds
 *
 * @property string $date_start
 * @property string $additional_data
 *
 * @property BelongsTo|Lottery|null $lottery
 * @property HasOne|LotteryTypeData|null $lottery_type_data
 */
class LotteryType extends AbstractOrmModel
{
    protected static $_table_name = 'lottery_type';

    protected static $_properties = [
        'id',
        'lottery_id',
        'odds',
        'ncount',
        'bcount',
        'nrange',
        'brange',
        'bextra',
        'date_start',
        'def_insured_tiers',
        'additional_data'
    ];

    protected $casts = [
        'id'                    => self::CAST_INT,
        'lottery_id'            => self::CAST_INT,
        'ncount'                => self::CAST_INT,
        'bcount'                => self::CAST_INT,
        'nrange'                => self::CAST_INT,
        'brange'                => self::CAST_INT,
        'bextra'                => self::CAST_INT,
        'def_insured_tiers'     => self::CAST_INT,

        'odds'                  => self::CAST_FLOAT
    ];

    protected static array $_belongs_to = [
        'lottery' => [
            'key_from' => 'lottery_id',
            'model_to' => Lottery::class,
            'key_to' => 'id'
        ]
    ];

    protected static array $_has_one = [
        'lottery_type_data' => [
            'key_from' => 'id',
            'model_to' => LotteryTypeData::class,
            'key_to' => 'lottery_type_id'
        ]
    ];
}
