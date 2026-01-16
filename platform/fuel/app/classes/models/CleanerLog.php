<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use DateTime;
use Models\Whitelabel;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $whitelabelTransactionId
 * @property string $message
 * @property string $type
 * @property dateTime|Carbon $createdAt
 * 
 * @property BelongsTo|WhitelabelTransaction $whitelabelTransaction
 * @property BelongsTo|Whitelabel $whitelabel
 */
class CleanerLog extends AbstractOrmModel
{
    protected static string $_table_name = 'cleaner_log';
    
    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'whitelabel_transaction_id',
        'message',
        'created_at'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_transaction_id' => self::CAST_INT,
        'message' => self::CAST_STRING,
        'created_at' => self::CAST_CARBON
    ];
    
    protected array $relations = [
        WhitelabelTransaction::class => self::BELONGS_TO,
        Whitelabel::class => self::BELONGS_TO,
    ];
    
    protected array $timezones = [
        'created_at' => 'UTC'
    ];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}