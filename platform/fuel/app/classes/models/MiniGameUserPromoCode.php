<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\HasOne;

/**
 * @property int $id
 * @property int $miniGamePromoCodeId
 * @property int $miniGameId
 * @property int $whitelabelUserId
 * @property int $usedFreeSpinCount
 * @property bool $hasUsedAllSpins
 * @property Carbon $createdAt
 *
 * @property HasOne|MiniGamePromoCode|null $miniGamePromoCode
 */
class MiniGameUserPromoCode extends AbstractOrmModel
{
    protected static string $_table_name = 'mini_game_user_promo_code';

    protected static array $_properties = [
        'id',
        'mini_game_promo_code_id',
        'mini_game_id',
        'whitelabel_user_id',
        'used_free_spin_count' => ['default' => 0],
        'has_used_all_spins'=> ['default' => false],
        'created_at',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'mini_game_promo_code_id' => self::CAST_INT,
        'mini_game_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'used_free_spin_count' => self::CAST_INT,
        'has_used_all_spins' => self::CAST_BOOL,
    ];

    protected array $relations = [
        MiniGameTransaction::class => self::HAS_ONE,
        MiniGamePromoCode::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
