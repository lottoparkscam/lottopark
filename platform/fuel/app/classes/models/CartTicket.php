<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * @property int $id
 * @property int $cartId
 * @property int $lotteryId
 * @property string $lines
 * @property int $ticketMultiplier
 * @property int $numbersPerLine
 * @property string $multidraw
 * @property Carbon $createdAt
 *
 */
class CartTicket extends AbstractOrmModel
{
    protected static string $_table_name = 'cart_tickets';

    protected static array $_properties = [
        'id',
        'cart_id',
        'lottery_id',
        'ticket_multiplier',
        'numbers_per_line',
        'multidraw',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'cart_id' => self::CAST_INT,
        'lottery_id' => self::CAST_INT,
        'ticket_multiplier' => self::CAST_INT,
        'numbers_per_line' => self::CAST_INT,
        'multidraw' => self::CAST_ARRAY,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [
        'lottery' => [
            'key_from' => 'lottery_id',
            'model_to' => Lottery::class,
            'key_to' => 'id'
        ],
        'carts' => [
            'key_from' => 'cart_id',
            'model_to' => Cart::class,
            'key_to' => 'id'
        ]
    ];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
