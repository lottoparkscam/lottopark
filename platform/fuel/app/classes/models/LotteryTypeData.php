<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $lottery_type_id
 * @property int $match_n
 * @property int $match_b
 * @property int $type
 * @property int $is_jackpot
 *
 * @property string $additional_data
 * @property string $prize
 *
 * @property float $odds
 * @property float $estimated
 *
 * @property BelongsTo|LotteryType|null $lottery_type
 */
class LotteryTypeData extends AbstractOrmModel
{
    protected static $_table_name = 'lottery_type_data';

    protected static $_properties = [
        'id',
        'lottery_type_id',
        'match_n',
        'match_b',
        'type',
        'is_jackpot',

        'additional_data',
        'prize',

        'odds',
        'estimated',
    ];

    protected $casts = [
        'id'                => self::CAST_INT,
        'lottery_type_id'   => self::CAST_INT,
        'match_n'           => self::CAST_INT,
        'match_b'           => self::CAST_INT,
        'type'              => self::CAST_INT,
        'is_jackpot'        => self::CAST_INT,

        'odds'              => self::CAST_FLOAT,
        'estimated'         => self::CAST_FLOAT
    ];

    protected static array $_belongs_to = [
        'lottery_type' => [
            'key_from' => 'lottery_type_id',
            'model_to' => LotteryType::class,
            'key_to' => 'id'
        ]
    ];
}
