<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;

/**
 * @property BelongsTo|WhitelabelPlugin $whitelabelPlugin
 * @property int $id
 * @property int $whitelabelPluginId
 * @property Carbon $date
 * @property int $type
 * @property string $message
 */
class WhitelabelPluginLog extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_plugin_log';

    protected static array $_properties = [
        'id',
        'whitelabel_plugin_id',
        'date',
        'type',
        'message'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_plugin_id' => self::CAST_INT,
        'date' => self::CAST_CARBON,
        'type' => self::CAST_INT,
        'message' => self::CAST_STRING
    ];

    protected array $relations = [
        WhitelabelPlugin::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
        'date' => 'UTC'
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
