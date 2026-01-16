<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabel_id
 *
 * @property string $api_key
 * @property string $api_secret
 *
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WhitelabelApi extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_api';
    
    protected static array $_properties = [
        'id',
        'whitelabel_id',
                
        'api_key',
        'api_secret'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
                
        'api_key' => self::CAST_STRING,
        'api_secret' => self::CAST_STRING
    ];
    
    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO
    ];
    
    protected array $timezones = [
    ];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}