<?php

namespace Models;

use Orm\HasOne;
use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $whitelabel_user_ticket_id
 * @property int $whitelabel_ltech_id
 * @property int $whitelabel_lottery_id
 *
 * @property string $ticket_scan_url
 *
 * @link https://gginternational.slite.com/app/docs/sInA2VidvzeD_A?source=search
 * @property string $additional_data
 *
 * @property BelongsTo|WhitelabelUserTicket|null $whitelabel_user_ticket
 * @property BelongsTo|WhitelabelLtech|null $whitelabel_ltech
 * @property HasOne|LottorisqTicket|null $lottorisq_ticket
 * @property BelongsTo|WhitelabelLottery|null $whitelabel_lottery
 * @property HasMany|WhitelabelUserTicketLine[]|null $whitelabel_user_ticket_lines
 * @property HasOne|LcsTicket|null $lcsTicket
 */
class WhitelabelUserTicketSlip extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_user_ticket_slip';

    protected static $_properties = [
        'id',
        'whitelabel_user_ticket_id',
        'ticket_scan_url',
        'additional_data',
        'whitelabel_ltech_id',
        'whitelabel_lottery_id'
    ];

    protected $casts = [
        'id'                        => self::CAST_INT,
        'whitelabel_user_ticket_id' => self::CAST_INT,
        'whitelabel_ltech_id'       => self::CAST_INT,
        'whitelabel_lottery_id'     => self::CAST_INT
    ];

    protected static array $_belongs_to = [
        'whitelabel_user_ticket' => [
            'key_from'  => 'whitelabel_user_ticket_id',
            'model_to'  => WhitelabelUserTicket::class,
            'key_to'    => 'id'
        ],
        'whitelabel_ltech' => [
            'key_from'  => 'whitelabel_ltech_id',
            'model_to'  => WhitelabelLtech::class,
            'key_to'    => 'id'
        ],
        'whitelabel_lottery' => [
            'key_from'  => 'whitelabel_lottery_id',
            'model_to'  => WhitelabelLottery::class,
            'key_to'    => 'id'
        ]
    ];

    protected static array $_has_many = [
        'whitelabel_user_ticket_lines' => [
            'key_from'  => 'id',
            'model_to'  => WhitelabelUserTicketLine::class,
            'key_to'    => 'whitelabel_user_ticket_slip_id'
        ]
    ];

    protected static array $_has_one = [
        'lottorisq_ticket' => [
            'key_from'  => 'id',
            'model_to'  => LottorisqTicket::class,
            'key_to'    => 'whitelabel_user_ticket_slip_id'
        ],
        'lcs_ticket' => [
            'key_from'  => 'id',
            'model_to'  => LcsTicket::class,
            'key_to'    => 'whitelabel_user_ticket_slip_id'
        ]
    ];
}
