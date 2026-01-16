<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\HasOne;

/**
 * @property int $id
 * @property string $code
 * @property int $miniGameId
 * @property int $whitelabelId
 * @property int $freeSpinCount
 * @property float $freeSpinValue
 * @property int $usageLimit
 * @property int $userUsageLimit
 * @property Carbon $dateStart
 * @property Carbon $dateEnd
 * @property bool $isActive
 * @property Carbon $createdAt
 *
 * @property HasOne|MiniGame|null $miniGame
 */
class MiniGamePromoCode extends AbstractOrmModel
{
    protected static string $_table_name = 'mini_game_promo_code';

    protected static array $_properties = [
        'id',
        'code',
        'mini_game_id',
        'whitelabel_id',
        'free_spin_count',
        'free_spin_value',
        'usage_limit' => ['default' => 1],
        'user_usage_limit' => ['default' => 1],
        'date_start',
        'date_end',
        'is_active' => ['default' => true],
        'created_at',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'code' => self::CAST_STRING,
        'mini_game_id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'free_spin_count' => self::CAST_INT,
        'free_spin_value' => self::CAST_FLOAT,
        'usage_limit' => self::CAST_INT,
        'user_usage_limit' => self::CAST_INT,
        'is_active' => self::CAST_BOOL,
    ];

    protected array $relations = [
        MiniGame::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
