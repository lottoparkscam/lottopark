<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $module_id
 * @property int $admin_user_id
 * @property int $whitelabel_id
 *
 * @property string $type
 * @property string $message
 * @property Carbon $date
 * @property array $data
 * @property string|null $ip
 * @property string|null $browser
 * @property string|null $operation_system
 *
 * @property BelongsTo|AdminUser $admin_user
 * @property BelongsTo|Module $module
 * @property BelongsTo|Whitelabel $whitelabel
 */
class CrmLog extends AbstractOrmModel
{
    protected static string $_table_name = 'crm_log';
    
    protected static array $_properties = [
        'id',
        'module_id',
        'admin_user_id',
        'whitelabel_id',
        'message',
        'date',
        'data',
        'ip',
        'browser',
        'operation_system'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'module_id' => self::CAST_INT,
        'admin_user_id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'message' => self::CAST_STRING,
        'date' => self::CAST_CARBON,
        'data' => self::CAST_ARRAY,
        'ip' => self::CAST_STRING,
        'browser' => self::CAST_STRING,
        'operation_system' => self::CAST_STRING,
    ];
    
    protected array $relations = [
        AdminUser::class => self::BELONGS_TO,
        Module::class => self::BELONGS_TO,
        Whitelabel::class => self::BELONGS_TO
    ];
    
    protected array $timezones = [
        'date'  => 'UTC'
    ];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}