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
class CartTicketLine extends AbstractOrmModel
{
    protected static string $_table_name = 'cart_ticket_lines';

    protected static array $_properties = [
        'id',
        'cart_ticket_id',
        'numbers',
        'bnumbers',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'cart_ticket_id' => self::CAST_INT,
        'numbers' => self::CAST_ARRAY,
        'bnumbers' => self::CAST_ARRAY,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [
        'cart_ticket_lines' => [
            'key_from' => 'cart_ticket_id',
            'model_to' => CartTicket::class,
            'key_to' => 'id'
        ]
    ];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
