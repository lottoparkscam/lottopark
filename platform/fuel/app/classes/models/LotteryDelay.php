<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $lotteryId
 * @property Carbon $dateLocal
 * @property Carbon $dateDelay
 *
 * @property BelongsTo|Lottery $lottery
 */
class LotteryDelay extends AbstractOrmModel
{
    protected static string $_table_name = 'lottery_delay';

    protected static array $_properties = [
        'id',
        'lottery_id',
        'date_local',
        'date_delay'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'lottery_id' => self::CAST_INT,
        'date_local' => self::CAST_CARBON,
        'date_delay' => self::CAST_CARBON
    ];

    protected array $relations = [
        Lottery::class => self::BELONGS_TO
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
