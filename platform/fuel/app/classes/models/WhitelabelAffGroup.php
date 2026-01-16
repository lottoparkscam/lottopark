<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property BelongsTo|Whitelabel $whitelabel
 * @property int $id
 * @property int $whitelabelId
 * @property string $name
 * @property float $commissionValue
 * @property float $commissionValueManager
 * @property float $commissionValue2
 * @property float $commissionValue_2Manager
 * @property float $ftpCommissionValue
 * @property float $ftpCommissionValueManager
 * @property float $ftpCommissionValue2
 * @property float $ftpCommissionValue_2Manager
 */
class WhitelabelAffGroup extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_aff_group';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'name',
        'commission_value',
        'commission_value_manager',
        'commission_value_2',
        'commission_value_2_manager',
        'ftp_commission_value',
        'ftp_commission_value_manager',
        'ftp_commission_value_2',
        'ftp_commission_value_2_manager',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'name' => self::CAST_STRING,
        'commission_value' => self::CAST_FLOAT,
        'commission_value_manager' => self::CAST_FLOAT,
        'commission_value_2' => self::CAST_FLOAT,
        'commission_value_2_manager' => self::CAST_FLOAT,
        'ftp_commission_value' => self::CAST_FLOAT,
        'ftp_commission_value_manager' => self::CAST_FLOAT,
        'ftp_commission_value_2' => self::CAST_FLOAT,
        'ftp_commission_value_2_manager' => self::CAST_FLOAT,
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}