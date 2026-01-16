<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * @property int $id
 *
 * @property string $username
 * @property string $name
 * @property string $surname
 * @property string $email
 * @property string $timezone
 * @property string $salt
 * @property string $hash
 * @property string $config_data
 *
 * @property int $language_id
 * @property int $role_id
 *
 * @property HasMany|CrmLog[] $crm_logs
 * @property int $roleId
 */
class AdminUser extends AbstractOrmModel
{
    public const SUPER_ADMINISTRATOR_ROLE_ID = 1;
    public const ADMINISTRATOR_ROLE_ID = 2;
    public const WHITE_LABEL_SUPER_ADMINISTRATOR_ROLE_ID = 3;
    public const WHITE_LABEL_ADMINISTRATOR_ROLE_ID = 4;
 
    protected static string $_table_name = 'admin_user';
    
    protected static array $_properties = [
        'id',
                
        'username',
        'name',
        'surname',
        'email',
        'timezone' => ['default' => 'UTC'],
        'salt',
        'hash',
        'config_data',
                
        'language_id',
        'role_id'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
                
        'username' => self::CAST_STRING,
        'name' => self::CAST_STRING,
        'surname' => self::CAST_STRING,
        'email' => self::CAST_STRING,
        'timezone' => self::CAST_STRING,
        'salt' => self::CAST_STRING,
        'hash' => self::CAST_STRING,
        'config_data' => self::CAST_STRING,
                
        'language_id' => self::CAST_INT,
        'role_id' => self::CAST_INT
    ];
    
    protected array $relations = [
        CrmLog::class => self::HAS_MANY
    ];
    
    protected array $timezones = [
    ];
    
    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function isSuperadmin(): bool
    {
        return $this->roleId === 1;
    }

    public function isNotSuperadmin(): bool
    {
        return !$this->isSuperadmin();
    }
}
