<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $lotteryId
 * @property int $type
 * @property string $message
 * @property Carbon $date
 *
 * @property BelongsTo|Lottery $lottery
 */
class LotteryLog extends AbstractOrmModel
{
    protected static string $_table_name = 'lottery_log';

    public const TYPE_INFO = 0;
    public const TYPE_SUCCESS = 1;
    public const TYPE_WARNING = 2;
    public const TYPE_ERROR = 3;

    protected static array $_properties = [
        'id',
        'lottery_id',
        'date',
        'type',
        'message',
    ];

    protected $casts = [
        'message' => self::CAST_STRING,
        'id' => self::CAST_INT,
        'lottery_id' => self::CAST_INT,
        'type' => self::CAST_INT,
        'date' => self::CAST_CARBON
    ];

    protected array $relations = [
        Lottery::class => self::BELONGS_TO
    ];

    protected array $timezones = [
        'date' => 'UTC'
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
