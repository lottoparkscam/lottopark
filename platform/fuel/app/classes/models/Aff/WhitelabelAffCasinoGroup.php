<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property BelongsTo|Whitelabel $whitelabel
 * @property int $id
 * @property int $whitelabelId
 * @property string $name
 * @property float $commissionPercentageValueForTier1 (5,2)
 * @property float $commissionPercentageValueForTier2 (5,2)
 *
 * @method WhitelabelAffGroup findOneById(int $id)
 */
class WhitelabelAffCasinoGroup extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_aff_casino_group';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'name',
        'commission_percentage_value_for_tier_1',
        'commission_percentage_value_for_tier_2',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'name' => self::CAST_STRING,
        'commission_percentage_value_for_tier_1' => self::CAST_FLOAT,
        'commission_percentage_value_for_tier_2' => self::CAST_FLOAT
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
