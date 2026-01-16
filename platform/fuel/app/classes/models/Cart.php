<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * @property int $id
 * @property int $whitelabelUserId
 * @property string $status
 * @property Carbon $created_at
 * @property Carbon $updated_at
 *
 */
class Cart extends AbstractOrmModel
{
    protected static string $_table_name = 'carts';

    protected static array $_properties = [
        'id',
        'whitelabel_user_id',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
