<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $slotGameId
 * @property int $whitelabelUserId
 * @property int $whitelabelSlotProviderId
 * @property int $slotOpenGameId
 * @property int $currencyId
 *
 * Token is created by database side using uuid_short() function in MySQL, it is bigint type
 * @property int $token
 * @property bool $isCanceled
 * @property float $amount
 * @property float $amountUsd
 * @property float $amountManager
 * @property ?string $type see TYPE_* constants
 * @property string $action see ACTION_* constants
 * @property string $providerTransactionId
 * @property array $additionalData
 * @property Carbon $createdAt
 * @property Carbon|null $canceledAt
 * 
 * @property BelongsTo|SlotGame $slotGame
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 * @property BelongsTo|Currency $currency
 * @property BelongsTo|WhitelabelSlotProvider $whitelabelSlotProvider
 * @property BelongsTo|SlotOpenGame $slotOpenGame
 */
class SlotTransaction extends AbstractOrmModel
{
    public const TYPE_BET = 'bet';
    public const TYPE_TIP = 'tip';
    public const TYPE_FREESPIN = 'freespin';
    public const TYPE_WIN = 'win';
    public const TYPE_JACKPOT = 'jackpot';
    public const TYPE_REFUND = 'refund';
    public const TYPE_ROLLBACK = 'rollback';

    public const ACTION_BET = 'bet';
    public const ACTION_WIN = 'win';
    public const ACTION_REFUND = 'refund';
    public const ACTION_ROLLBACK = 'rollback';

    protected static string $_table_name = 'slot_transaction';

    protected static array $_properties = [
        'id',
        'whitelabel_slot_provider_id',
        'slot_game_id',
        'slot_open_game_id',
        'whitelabel_user_id',
        'currency_id',
        'token',
        'is_canceled' => ['default' => false],
        'amount',
        'amount_usd',
        'amount_manager',
        'type',
        'action',
        'provider_transaction_id',
        'additional_data',
        'created_at',
        'canceled_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'whitelabel_slot_provider_id' => 'integer',
        'slot_game_id' => 'integer',
        'slot_open_game_id' => 'integer',
        'whitelabel_user_id' => 'integer',
        'currency_id' => 'integer',
        'token' => 'integer',
        'is_canceled' => 'boolean',
        'amount' => 'float',
        'amount_usd' => 'float',
        'amount_manager' => 'float',
        'type' => 'string',
        'action' => 'string',
        'provider_transaction_id' => 'string',
        'additional_data' => 'array',
        'created_at' => 'carbon',
        'canceled_at' => 'carbon',
    ];

    protected array $relations = [
        WhitelabelSlotProvider::class => self::BELONGS_TO,
        SlotGame::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO,
        Currency::class => self::BELONGS_TO,
        SlotOpenGame::class => self::BELONGS_TO
    ];

    protected array $timezones = [
        'created_at' => 'UTC',
        'canceled_at' => 'UTC',
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function getPrefixedToken(): string
    {
        $whitelabelPrefix = $this->whitelabelSlotProvider->whitelabel->prefix;
        $prefixLetter = 'C';
        $token = $this->token;

        return "{$whitelabelPrefix}{$prefixLetter}{$token}";
    }
}
