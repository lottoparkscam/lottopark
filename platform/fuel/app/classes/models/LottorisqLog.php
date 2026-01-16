<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use Orm\BelongsTo;

/**
 * @property string $message
 * @property int $id
 * @property int $whitelabelId
 * @property int $whitelabelUserTicketId
 * @property int $whitelabelUserTicketSlipId
 * @property int $whitelabelLtechId
 * @property Carbon $date
 * @property int $type
 * @property array $data
 * @property BelongsTo|WhitelabelUserTicket|null $whitelabel_user_ticket
 */
class LottorisqLog extends AbstractOrmModel
{
    protected static string $_table_name = 'lottorisq_log';

    public const TYPE_INFO = 0;
    public const TYPE_SUCCESS = 1;
    public const TYPE_WARNING = 2;
    public const TYPE_ERROR = 3;

    public const MESSAGE_SUCCESS_DRAW_DOWNLOAD = 'Draws have been downloaded successfully.';

    protected static array $_properties = [
        'message',
        'id',
        'whitelabel_id',
        'whitelabel_user_ticket_id',
        'whitelabel_user_ticket_slip_id',
        'whitelabel_ltech_id',
        'date',
        'type',
        'data'
    ];

    protected $casts = [
        'message' => self::CAST_STRING,
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_user_ticket_id' => self::CAST_INT,
        'whitelabel_user_ticket_slip_id' => self::CAST_INT,
        'whitelabel_ltech_id' => self::CAST_INT,
        'date' => self::CAST_CARBON,
        'type' => self::CAST_INT,
        'data' => self::CAST_ARRAY
    ];

    protected array $relations = [
        WhitelabelUserTicket::class => self::BELONGS_TO
    ];

    protected array $timezones = [
        'date' => 'UTC'
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
