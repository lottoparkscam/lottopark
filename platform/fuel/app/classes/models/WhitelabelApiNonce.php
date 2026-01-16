<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $nonce
 * @property Carbon $date
 * @property array $data
 */
class WhitelabelApiNonce extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_api_nonce';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'nonce',
        'date',
        'data',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'nonce' => self::CAST_INT,
        'date' => self::CAST_CARBON,
        'data' => self::CAST_ARRAY,
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
