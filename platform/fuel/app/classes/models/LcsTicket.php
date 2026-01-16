<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property-read int $id
 * @property int $whitelabelUserTicketSlipId
 * @property string $uuid
 *
 * @property BelongsTo|WhitelabelUserTicketSlip[] $whitelabelUserTicketSlip
 */
class LcsTicket extends AbstractOrmModel
{
    protected static $_table_name = 'lcs_ticket';

    protected static $_properties = [
        'id',
        'whitelabel_user_ticket_slip_id',
        'uuid',
    ];

    protected $casts = [
        'id'                             => self::CAST_INT,
        'whitelabel_user_ticket_slip_id' => self::CAST_INT,
        'uuid'                           => self::CAST_STRING,
    ];

    protected array $relations = [
        WhitelabelUserTicketSlip::class => self::BELONGS_TO,
    ];

    protected array $timezones = [];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
