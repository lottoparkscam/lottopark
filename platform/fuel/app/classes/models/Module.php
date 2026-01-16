<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property string $name
 */
class Module extends AbstractOrmModel
{
    protected static string $_table_name = 'module';
    
    protected static array $_properties = [
        'id',
        'name'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'name' => self::CAST_STRING
    ];
    
    protected array $relations = [
        CrmLog::class => self::HAS_MANY
    ];
    
    protected array $timezones = [
    ];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}