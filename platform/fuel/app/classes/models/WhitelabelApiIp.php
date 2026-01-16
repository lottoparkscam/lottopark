<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 *
 * @property string $ip
 *
 * @property int $whitelabel_id
 */
class WhitelabelApiIp extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_api_ip';
    
    protected static array $_properties = [
        'id',
                
        'ip',
                
        'whitelabel_id'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
                
        'ip' => self::CAST_STRING,
                
        'whitelabel_id' => self::CAST_INT,
    ];
    
    protected array $relations = [
    ];
    
    protected array $timezones = [
    ];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
