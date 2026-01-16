<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property int $drawRangeStart
 * @property int $drawRangeEnd
 * @property int $numDraws
 * @property int $multiplier
 * @property array $availableBets
 * @property float $defaultBet
 * @property bool $isDeleted
 *
 * @property HasMany|MiniGameTransaction[] $miniGameTransactions
 */
class MiniGame extends AbstractOrmModel
{
    public const GG_WORLD_COINFLIP_SLUG = 'gg-world-coinflip';
    public const GG_WORLD_TIC_TAC_BOO_SLUG = 'gg-world-tic-tac-boo';
    public const GG_WORLD_SANTA_IN_DA_HOUSE_SLUG = 'gg-world-santa-in-da-house';
    public const GG_WORLD_RED_OR_BLUE_SLUG = 'gg-world-red-or-blue';

    public const GAMES_SLUG_LIST = [
        self::GG_WORLD_COINFLIP_SLUG,
        self::GG_WORLD_TIC_TAC_BOO_SLUG,
        self::GG_WORLD_SANTA_IN_DA_HOUSE_SLUG,
        self::GG_WORLD_RED_OR_BLUE_SLUG,
    ];

    protected static string $_table_name = 'mini_game';

    protected static array $_properties = [
        'id',
        'slug',
        'name',
        'draw_range_start',
        'draw_range_end',
        'num_draws',
        'multiplier',
        'available_bets',
        'default_bet',
        'is_enabled' => ['default' => false],
        'is_deleted' => ['default' => false],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'slug' => self::CAST_STRING,
        'name' => self::CAST_STRING,
        'draw_range_start' => self::CAST_INT,
        'draw_range_end' => self::CAST_INT,
        'num_draws' => self::CAST_INT,
        'multiplier' => self::CAST_FLOAT,
        'available_bets' => self::CAST_ARRAY,
        'default_bet' => self::CAST_FLOAT,
        'is_enabled' => self::CAST_BOOL,
        'is_deleted' => self::CAST_BOOL,
    ];

    protected array $relations = [
        MiniGameTransaction::class => self::HAS_MANY,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
