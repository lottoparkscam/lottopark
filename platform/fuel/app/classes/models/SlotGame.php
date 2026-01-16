<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;
use Orm\HasMany;

/**
 * @property int $id
 * @property int $slotProviderId
 *
 * @property string $uuid
 * @property string $name
 * @property string $image
 * @property string $type
 * @property string $provider
 * @property string $technology
 *
 * @property bool $isDeleted
 * @property bool $hasDemo
 * @property bool $hasLobby
 * @property bool $isMobile
 * @property int  $order
 * @property bool $freespinValidUntilFullDay
 * @property bool $hasFreespins;
 *
 * @property BelongsTo|SlotProvider $slotProvider
 * @property HasMany|SlotLog[] $slotLogs
 * @property HasMany|SlotTransaction[] $slotTransactions
 * @property HasMany|SlotOpenGame[] $slotOpenGames
 */
class SlotGame extends AbstractOrmModel
{
    public const NUMBER_OF_GAMES_PER_PAGE = 32;
    protected static string $_table_name = 'slot_game';

    protected static array $_properties = [
        'id',
        'slot_provider_id',
        'uuid',
        'is_deleted' => ['default' => false],
        'name',
        'image',
        'type',
        'provider',
        'technology',
        'has_demo' => ['default' => false],
        'has_lobby',
        'has_freespins',
        'is_mobile',
        'order' => ['default' => 0],
        'freespin_valid_until_full_day',
    ];

    protected $casts = [
        'id' => 'integer',
        'slot_provider_id' => 'integer',

        'uuid' => 'string',
        'name' => 'string',
        'image' => 'string',
        'type' => 'string',
        'provider' => 'string',
        'technology' => 'string',

        'is_deleted' => 'boolean',
        'status' => 'boolean',
        'has_demo' => 'boolean',
        'has_lobby' => 'boolean',
        'is_mobile' => 'boolean',
        'order'     => 'int',
        'has_freespins' => 'boolean',
        'freespin_valid_until_full_day' => 'boolean',
    ];

    protected array $relations = [
        SlotProvider::class => self::BELONGS_TO,
        SlotLog::class      => self::HAS_MANY,
        SlotTransaction::class => self::HAS_MANY,
        SlotOpenGame::class => self::HAS_MANY,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
