<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $admin_user_id
 * @property int $module_id
 * @property int $whitelabel_id
 */
class AdminWhitelabelModule extends AbstractOrmModel
{
    protected static $_table_name = 'admin_whitelabel_module';

    protected static $_properties = [
        'id',
        'admin_user_id',
        'module_id',
        'whitelabel_id'
    ];

    protected $casts = [
        'id'            => self::CAST_INT,
        'admin_user_id' => self::CAST_INT,
        'module_id'     => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT
    ];
}