<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelSlotProviderId
 * @property int $slotGameId
 * @property int $whitelabelUserId
 * @property string $action see ACTION_* constants
 * @property bool $isError
 * @property array $request
 * @property array $response
 * @property Carbon $createdAt
 * 
 * @property BelongsTo|SlotGame $slotGame
 * @property BelongsTo|WhitelabelSlotProvider $whitelabelSlotProvider
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 */
class SlotLog extends AbstractOrmModel
{
    public const ACTION_INIT = 'init';
    public const ACTION_BET = 'bet';
    public const ACTION_BALANCE = 'balance';
    public const ACTION_WIN = 'win';
    public const ACTION_REFUND = 'refund';
    public const ACTION_ROLLBACK = 'rollback';

    protected static string $_table_name = 'slot_log';

    protected static array $_properties = [
        'id',
        'whitelabel_slot_provider_id',
        'slot_game_id',
        'whitelabel_user_id',
        'action',
        'is_error' => ['default' => false],
        'request',
        'response',
        'created_at'
    ];

    protected $casts = [
        'id' => 'integer',
        'whitelabel_slot_provider_id' => 'integer',
        'slot_game_id' => 'integer',
        'whitelabel_user_id' => 'integer',
        'action' => 'string',
        'is_error' => 'boolean',
        'request' => 'array',
        'response' => 'array',
        'created_at' => 'carbon'
    ];

    protected array $relations = [
        WhitelabelSlotProvider::class => self::BELONGS_TO,
        SlotGame::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
        'created_at' => 'UTC'
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
