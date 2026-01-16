<?php

namespace Models;

use Carbon\Carbon;
use DateTime;
use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * @property int $id
 * @property string $token
 * @property int $whitelabel_id
 * @property int $bonus_type
 * @property int $type
 * @property int $whitelabel_aff_id
 * @property int $lottery_id
 * @property int $max_codes_user
 * @property int $max_users_per_code
 * @property string $prefix
 * @property bool $is_active
 * @property DateTime|Carbon $date_start
 * @property DateTime|Carbon $date_end
 * @property int $max_users
 * @property float $discount_amount
 * @property int $discount_type
 * @property float $bonus_balance_amount
 * @property int $bonus_balance_type
 *
 * @property HasMany|WhitelabelPromoCode[] $whitelabel_promo_codes
 */
class WhitelabelCampaign extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_campaign';

    protected static array $_properties = [
        'id',
        'token',
        'whitelabel_id',
        'bonus_type',
        'type',
        'whitelabel_aff_id',
        'lottery_id',
        'max_codes_user',
        'max_users_per_code',
        'prefix',
        'is_active',
        'date_start',
        'date_end',
        'max_users',
        'discount_amount',
        'discount_type',
        'bonus_balance_amount',
        'bonus_balance_type'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'token' => self::CAST_STRING,
        'whitelabel_id' => self::CAST_INT,
        'bonus_type' => self::CAST_INT,
        'type' => self::CAST_INT,
        'whitelabel_aff_id' => self::CAST_INT,
        'lottery_id' => self::CAST_INT,
        'max_codes_user' => self::CAST_INT,
        'max_users_per_code' => self::CAST_INT,
        'prefix' => self::CAST_STRING,
        'is_active' => self::CAST_BOOL,
        'date_start' => self::CAST_DATETIME,
        'date_end' => self::CAST_CARBON,
        'max_users' => self::CAST_INT,
        'discount_amount' => self::CAST_FLOAT,
        'discount_type' => self::CAST_INT,
        'bonus_balance_amount' => self::CAST_FLOAT,
        'bonus_balance_type' => self::CAST_INT,
    ];

    protected array $relations = [
    	WhitelabelPromoCode::class => self::HAS_MANY
	];

    protected array $timezones = [];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function isRegister(): bool
    {
        return $this->type === 2; 
    }
}
