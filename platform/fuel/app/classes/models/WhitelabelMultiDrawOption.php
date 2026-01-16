<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $tickets
 * @property float $discount
 *
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WhitelabelMultiDrawOption extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_multi_draw_option';
    
    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'tickets',
        'discount'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'tickets' => self::CAST_INT,
        'discount' => self::CAST_FLOAT
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