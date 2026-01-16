<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $adminUserId
 * @property int $whitelabelId
 * 
 * @property BelongsTo|Whitelabel $whitelabel
 * @property BelongsTo|AdminUser $adminUser
 */
class AdminWhitelabel extends AbstractOrmModel
{
    protected static $_table_name = 'admin_whitelabel';

    protected static $_properties = [
        'id',
        'admin_user_id',
        'whitelabel_id'
    ];

    protected $casts = [
        'id'                => self::CAST_INT,
        'admin_user_id'     => self::CAST_INT,
        'whitelabel_id'     => self::CAST_INT
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        AdminUser::class => self::BELONGS_TO,
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
