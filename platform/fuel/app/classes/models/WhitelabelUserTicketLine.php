<?php

namespace Models;

use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $whitelabel_user_ticket_id
 * @property int $lottery_type_data_id
 * @property int $whitelabel_user_ticket_slip_id
 * @property int $status
 *
 * @property bool $payout
 *
 * @property string $numbers
 * @property string $bnumbers
 *
 * @property float $amount_local
 * @property float $amount
 * @property float $amount_usd
 * @property float $amount_payment
 * @property float $amount_manager
 * @property float $prize_local
 * @property float $prize_usd
 * @property float $prize
 * @property float $prize_manager
 * @property float $prize_net_local
 * @property float $prize_net_usd
 * @property float $prize_net
 * @property float $prize_net_manager
 * @property float $bonus_amount_local
 * @property float $bonus_amount_payment
 * @property float $bonus_amount_usd
 * @property float $bonus_amount
 * @property float $bonus_amount_manager
 * @property float $uncovered_prize_local
 * @property float $uncovered_prize_usd
 * @property float $uncovered_prize
 * @property float $uncovered_prize_manager
 *
 * @property BelongsTo|WhitelabelUserTicket|null $whitelabel_user_ticket
 */
class WhitelabelUserTicketLine extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_user_ticket_line';

    protected static $_properties = [
        'id',
        'whitelabel_user_ticket_id',
        'lottery_type_data_id',
        'whitelabel_user_ticket_slip_id',
        'numbers',
        'bnumbers',
        'amount_local',
        'amount',
        'amount_usd',
        'amount_payment' => ['default' => 0.00],
        'amount_manager' => ['default' => 0.00],
        'status',
        'payout',
        'prize_local',
        'prize_usd',
        'prize',
        'prize_manager' => ['default' => 0.00],
        'prize_net_local',
        'prize_net_usd',
        'prize_net',
        'prize_net_manager' => ['default' => 0.00],
        'bonus_amount_local',
        'bonus_amount_payment',
        'bonus_amount_usd',
        'bonus_amount',
        'bonus_amount_manager',
        'uncovered_prize_local',
        'uncovered_prize_usd',
        'uncovered_prize',
        'uncovered_prize_manager'
    ];

    protected $casts = [
        'id'                                => self::CAST_INT,
        'whitelabel_user_ticket_id'         => self::CAST_INT,
        'lottery_type_data_id'              => self::CAST_INT,
        'whitelabel_user_ticket_slip_id'    => self::CAST_INT,
        'status'                            => self::CAST_INT,

        'payout'                            => self::CAST_BOOL,

        'amount_local'                      => self::CAST_FLOAT,
        'amount'                            => self::CAST_FLOAT,
        'amount_usd'                        => self::CAST_FLOAT,
        'amount_payment'                    => self::CAST_FLOAT,
        'amount_manager'                    => self::CAST_FLOAT,
        'prize_local'                       => self::CAST_FLOAT,
        'prize_usd'                         => self::CAST_FLOAT,
        'prize'                             => self::CAST_FLOAT,
        'prize_manager'                     => self::CAST_FLOAT,
        'prize_net_local'                   => self::CAST_FLOAT,
        'prize_net_usd'                     => self::CAST_FLOAT,
        'prize_net'                         => self::CAST_FLOAT,
        'prize_net_manager'                 => self::CAST_FLOAT,
        'bonus_amount_local'                => self::CAST_FLOAT,
        'bonus_amount_payment'              => self::CAST_FLOAT,
        'bonus_amount_usd'                  => self::CAST_FLOAT,
        'bonus_amount'                      => self::CAST_FLOAT,
        'bonus_amount_manager'              => self::CAST_FLOAT,
        'uncovered_prize_local'             => self::CAST_FLOAT,
        'uncovered_prize_usd'               => self::CAST_FLOAT,
        'uncovered_prize'                   => self::CAST_FLOAT,
        'uncovered_prize_manager'           => self::CAST_FLOAT
    ];

    protected static array $_belongs_to = [
        'whitelabel_user_ticket' => [
            'key_from' => 'whitelabel_user_ticket_id',
            'model_to' => WhitelabelUserTicket::class,
            'key_to' => 'id'
        ]
    ];
}
