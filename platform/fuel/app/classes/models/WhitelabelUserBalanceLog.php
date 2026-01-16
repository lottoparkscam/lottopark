<?php

namespace Models;

use Carbon\Traits\Date;
use Classes\Orm\AbstractOrmModel;
use Models\WhitelabelUser;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabel_user_id
 * @property int $level
 *
 * @property Date|null $created_at
 * @property Date|null $session_datetime
 *
 * @property string $message
 * @property string $balance_change_currency_code
 * @property string $balance_change_import_currency_code
 * @property string $balance_change_before_conversion_currency_code
 *
 * @property bool $is_bonus
 *
 * @property float $balance_change
 * @property float $balance_change_import
 * @property float $balance_change_before_conversion
 *
 * @property BelongsTo|WhitelabelUser|null $whitelabel_user
 */
class WhitelabelUserBalanceLog extends AbstractOrmModel
{
    const USER_BALANCE_LOG_STATUS_SUCCESS = 0;
    const USER_BALANCE_LOG_STATUS_FAILURE = 1;

    protected static $_table_name = 'whitelabel_user_balance_log';

    protected static $_properties = [
        'id',
        'whitelabel_user_id',
        'level',

        'created_at',
        'session_datetime',

        'message',
        'balance_change_currency_code',
        'balance_change_import_currency_code',
        'balance_change_before_conversion_currency_code',

        'is_bonus' => ['default' => 0],

        'balance_change' => ['default' => 0.00],
        'balance_change_import' => ['default' => 0.00],
        'balance_change_before_conversion' => ['default' => 0.00]
    ];

    protected $casts = [
        'id'                        => self::CAST_INT,
        'whitelabel_user_id'        => self::CAST_INT,
        'level'                     => self::CAST_INT,

        'created_at'                => self::CAST_DATETIME,
        'session_datetime'          => self::CAST_DATETIME,

        'is_bonus'                  => self::CAST_BOOL,

        'balance_change'            => self::CAST_FLOAT,
        'balance_change_import'     => self::CAST_FLOAT,
        'balance_change_before_conversion'  => self::CAST_FLOAT,
    ];

    protected array $relations = [
        WhitelabelUser::class => self::BELONGS_TO
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}