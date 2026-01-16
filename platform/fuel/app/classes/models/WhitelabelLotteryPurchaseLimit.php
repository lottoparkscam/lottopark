<?php

namespace Models;

use Carbon\Carbon;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $whitelabelLotteryId
 * @property int $whitelabelUserId
 * @property int $counter
 * @property Carbon $createdAt
 * @property Carbon $updatedAt
 *
 * @property BelongsTo|WhitelabelLottery $whitelabelLottery
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 */
class WhitelabelLotteryPurchaseLimit extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_lottery_purchase_limit';

    protected static $_properties = [
        'id',
        'whitelabel_lottery_id',
        'whitelabel_user_id',
        'counter',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'id'                        => self::CAST_INT,
        'whitelabel_lottery_id'     => self::CAST_INT,
        'whitelabel_user_id'        => self::CAST_INT,
        'counter'                   => self::CAST_INT,
        'created_at'                => self::CAST_CARBON,
        'updated_at'                => self::CAST_CARBON,
    ];

    protected array $relations = [
        WhitelabelLottery::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
        'created_at' => 'UTC',
        'updated_at' => 'UTC',
    ];

    // It is very important! Do not remove this variables!
    protected static array $_has_one = [];
    protected static array $_has_many = [];
    protected static array $_belongs_to = [];
}
