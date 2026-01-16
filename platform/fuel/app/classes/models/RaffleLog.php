<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use Orm\BelongsTo;

/**
 * @property int $id
 *
 * @property string $message
 *
 * @property int $type
 * @property int $raffle_id
 *
 * @property Carbon $date
 *
 * @property BelongsTo|Raffle $raffle
 */
class RaffleLog extends AbstractOrmModel
{
    protected static string $_table_name = 'raffle_log';
    
    protected static array $_properties = [
        'id',
                
        'message',
                
        'type',
        'raffle_id',
                
        'date'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
                
        'message' => self::CAST_STRING,
                
        'type' => self::CAST_INT,
        'raffle_id' => self::CAST_INT,
                
        'date' => self::CAST_CARBON,
    ];
    
    protected array $relations = [
        Raffle::class => self::BELONGS_TO
    ];
    
    protected array $timezones = [
    ];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}