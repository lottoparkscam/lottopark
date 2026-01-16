<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $whitelabelUserTicketId
 * @property int $imvalapJobId
 * @property Carbon $date
 * @property int $type
 * @property string $message
 * @deprecated
 */
class ImvalapLog extends AbstractOrmModel
{
    protected static string $_table_name = 'imvalap_log';

    public const TYPE_INFO = 0;
    public const TYPE_SUCCESS = 1;
    public const TYPE_WARNING = 2;
    public const TYPE_ERROR = 3;

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'whitelabel_user_ticket_id',
        'imvalap_job_id',
        'date',
        'type',
        'message',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_user_ticket_id' => self::CAST_INT,
        'imvalap_job_id' => self::CAST_INT,
        'date' => self::CAST_CARBON,
        'type' => self::CAST_INT,
        'message' => self::CAST_STRING,
    ];

    protected array $relations = [];

    protected array $timezones = [
        'date' => 'UTC'
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
