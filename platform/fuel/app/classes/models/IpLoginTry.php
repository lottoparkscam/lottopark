<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;

/**
 * @property int $id
 * @property string $ip
 * @property Carbon $lastLoginTryAt
 * @property int $loginTryCount
 */
class IpLoginTry extends AbstractOrmModel
{
    protected static string $_table_name = 'ip_login_try';

    protected static array $_properties = [
        'id',
        'ip',
        'last_login_try_at',
        'login_try_count'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'ip' => self::CAST_STRING,
        'last_login_try_at' => self::CAST_CARBON,
        'login_try_count' => self::CAST_INT
    ];

    protected array $relations = [];

    protected array $timezones = [
        'date' => 'UTC'
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
