<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Fuel\Core\Date;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelUserId
 * @property int $whitelabelPluginId
 * @property bool $isActive
 * @property string $data
 * @property Date|null $createdAt
 * @property Date|null $updatedAt
 *
 * @property HasMany|WhitelabelPluginLog $whitelabelPluginLog
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 * @property BelongsTo|WhitelabelPlugin $whitelabelPlugin
 */
class WhitelabelPluginUser extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_plugin_user';

    protected static array $_properties = [
        'id',
        'whitelabel_user_id',
        'whitelabel_plugin_id',
        'is_active' => ['default' => true],
        'data',
        'created_at' => ['default' => null],
        'updated_at' => ['default' => null]
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'whitelabel_plugin_id' => self::CAST_INT,
        'is_active' => self::CAST_BOOL,
        'data' => self::CAST_STRING,
        'created_at' => self::CAST_CARBON,
        'updated_at' => self::CAST_CARBON,
    ];

    protected array $relations = [
        WhitelabelUser::class => self::BELONGS_TO,
        WhitelabelPlugin::class => self::BELONGS_TO,
        WhitelabelPluginLog::class => self::HAS_MANY,
    ];

    protected array $timezones = [];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
