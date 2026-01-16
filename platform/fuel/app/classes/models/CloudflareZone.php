<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property string $identifier
 * 
 * @property BelongsTo|Whitelabel $whitelabel
 */
class CloudflareZone extends AbstractOrmModel
{
    protected static string $_table_name = 'cloudflare_zone';
    
    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'identifier'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'identifier' => self::CAST_STRING,
    ];
    
    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
    ];
    
    protected array $timezones = [];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}