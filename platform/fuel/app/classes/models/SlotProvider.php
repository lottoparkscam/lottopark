<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * Example
 *      slug slotegrator
 *      apiUrl https://api.games.com/v1
 *      initGamePath /init/game
 *      apiCredentials: {"merchant_id": "id", "merchant_key": "key"}
 *      gameListPath /games
 *
 * @property int $id
 * @property string $slug
 * @property string $apiUrl
 * @property string $initGamePath
 * @property string $initDemoGamePath
 * @property ?string $gameListPath
 *
 * @property array $apiCredentials
 *
 * @property HasMany|WhitelabelSlotProvider[] $whitelabelSlotProviders
 * @property HasMany|SlotWhitelistIp[] $slotWhitelistIps
 * @property HasMany|SlotGame[] $slotGames
 */
class SlotProvider extends AbstractOrmModel
{
    protected static string $_table_name = 'slot_provider';

    protected static array $_properties = [
        'id',
        'slug',
        'api_url',
        'init_game_path',
        'init_demo_game_path',
        'api_credentials',
        'game_list_path'
    ];

    protected $casts = [
        'id' => 'integer',
        'slug' => 'string',
        'api_url' => 'string',
        'init_game_path' => 'string',
        'init_demo_game_path' => 'string',
        'game_list_path' => 'string',
        'api_credentials' => 'array',
    ];

    protected array $relations = [
        WhitelabelSlotProvider::class => self::HAS_MANY,
        SlotWhitelistIp::class => self::HAS_MANY,
        SlotGame::class => self::HAS_MANY,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
