<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;
use DateTime;
use Models\Whitelabel;
use Orm\BelongsTo;

/**
 * @property int $id
 *
 * @property string $token
 * @property string $data
 *
 * @property int $whitelabel_id
 * @property int $whitelabel_user_id
 * @property int $withdrawal_id
 * @property int $currency_id
 * @property int $status
 *
 * @property bool $isCasino
 *
 * @property float $amount
 * @property float $amount_usd
 * @property float $amount_manager
 *
 * @property DateTime|Carbon $date
 * @property DateTime|Carbon $date_confirmed
 *
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WithdrawalRequest extends AbstractOrmModel
{
    protected static string $_table_name = 'withdrawal_request';
    
    protected static array $_properties = [
        'id',
                
        'token',
        'data',
                
        'whitelabel_id',
        'whitelabel_user_id',
        'withdrawal_id',
        'currency_id',
        'status',

        'is_casino' => ['default' => false],
                
        'amount',
        'amount_usd',
        'amount_manager',
                
        'date',
        'date_confirmed',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
                
        'token' => self::CAST_STRING,
        'data' => self::CAST_STRING,

        'whitelabel_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'withdrawal_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'status' => self::CAST_INT,

        'is_casino' => self::CAST_BOOL,

        'amount' => self::CAST_FLOAT,
        'amount_usd' => self::CAST_FLOAT,
        'amount_manager' => self::CAST_FLOAT,
                
        'date' => self::CAST_CARBON,
        'date_confirmed' => self::CAST_CARBON,
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