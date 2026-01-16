<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $miniGameId
 * @property int $whitelabelUserId
 * @property int $currencyId
 * @property string $token
 * @property float $amount
 * @property float $amountUsd
 * @property float $amountManager
 * @property float $prize
 * @property float $prizeUsd
 * @property float $prizeManager
 * @property string $type ['win', 'loss']
 * @property int $userSelectedNumber
 * @property int $systemDrawnNumber
 * @property int $miniGameUserPromoCodeId
 * @property boolean $isBonusBalancePaid
 * @property Carbon $createdAt
 *
 * @property BelongsTo|MiniGame|null $miniGame
 * @property BelongsTo|MiniGameUserPromoCode|null $miniGameUserPromoCode
 */
class MiniGameTransaction extends AbstractOrmModel
{
    public const TRANSACTION_TYPE_WIN = 'win';
    public const TRANSACTION_TYPE_LOSS = 'loss';

    protected static string $_table_name = 'mini_game_transaction';

    protected static array $_properties = [
        'id',
        'mini_game_id',
        'whitelabel_user_id',
        'currency_id',
        'token',
        'amount',
        'amount_usd',
        'amount_manager',
        'prize',
        'prize_usd',
        'prize_manager',
        'type',
        'user_selected_number',
        'system_drawn_number',
        'mini_game_user_promo_code_id',
        'is_bonus_balance_paid',
        'created_at',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'mini_game_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'token' => self::CAST_STRING,
        'amount' => self::CAST_FLOAT,
        'amount_usd' => self::CAST_FLOAT,
        'amount_manager' => self::CAST_FLOAT,
        'prize' => self::CAST_FLOAT,
        'prize_usd' => self::CAST_FLOAT,
        'prize_manager' => self::CAST_FLOAT,
        'type' => self::CAST_STRING,
        'user_selected_number' => self::CAST_INT,
        'system_drawn_number' => self::CAST_INT,
        'mini_game_user_promo_code_id' => self::CAST_INT,
        'is_bonus_balance_paid' => self::CAST_BOOL,
    ];

    protected array $relations = [
        MiniGame::class => self::BELONGS_TO,
        MiniGameUserPromoCode::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
