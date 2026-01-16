<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $whitelabelAffId
 * @property int $whitelabelUserId
 * @property string $type
 * @property int $tier commission line
 * @property float $dailyCommissionUsd
 * @property float $ggrUsd
 * @property Carbon $createdAt Y-m-d format
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 * @property BelongsTo|WhitelabelUserAff $whitelabelUserAff
 */
class WhitelabelAffSlotCommission extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_aff_slot_commission';

    protected static array $_properties = [
        'id',
        'whitelabel_aff_id',
        'whitelabel_user_id',
        'tier',
        'daily_commission_usd',
        'ggr_usd',
        'created_at',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_aff_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'tier' => self::CAST_INT,
        'daily_commission_usd' => self::CAST_FLOAT,
        'ggr_usd' => self::CAST_FLOAT,
        'created_at' => self::CAST_CARBON,
    ];

    protected array $relations = [
        WhitelabelUser::class => self::BELONGS_TO,
        WhitelabelAff::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
        'created_at' => 'UTC',
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}