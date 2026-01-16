<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabel_promo_code_id
 * @property int $whitelabel_transaction_id
 * @property int $whitelabel_user_id
 * @property int $type
 * @property string $usedAt gets information about the date and time when the promo code was used.
 * @property BelongsTo|WhitelabelPromoCode|null $whitelabel_promo_code
 */
class WhitelabelUserPromoCode extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_user_promo_code';

    protected static array $_properties = [
        'id',
        'whitelabel_promo_code_id',
        'whitelabel_transaction_id',
        'whitelabel_user_id',
        'type',
        'used_at'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_promo_code_id' => self::CAST_INT,
        'whitelabel_transaction_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'type' => self::CAST_INT,
        'used_at' => self::CAST_CARBON,
    ];

    protected array $relations = [
    	WhitelabelPromoCode::class => self::BELONGS_TO
	];

    protected array $timezones = [];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
