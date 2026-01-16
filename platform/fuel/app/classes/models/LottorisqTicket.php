<?php

namespace Models;

use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int|null $whitelabel_user_ticket_slip_id
 *
 * @property string $lottorisqid
 * @property string $confirm_data
 *
 * @property BelongsTo|WhitelabelUserTicketSlip|null $whitelabel_user_ticket_slip
 */
class LottorisqTicket extends AbstractOrmModel
{
    protected static $_table_name = 'lottorisq_ticket';

    protected static $_properties = [
        'id',
        'whitelabel_user_ticket_slip_id',
        'lottorisqid',
        'confirm_data'
    ];

    protected $casts = [
        'id'                             => self::CAST_INT,
        'whitelabel_user_ticket_slip_id' => self::CAST_INT,
    ];

    protected static array $_belongs_to = [
        'whitelabel_user_ticket_slip' => [
            'key_from' => 'whitelabel_user_ticket_slip_id',
            'model_to' => WhitelabelUserTicketSlip::class,
            'key_to' => 'id'
        ]
    ];
}
