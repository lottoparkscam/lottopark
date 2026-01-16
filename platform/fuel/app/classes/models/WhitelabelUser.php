<?php

namespace Models;

use Orm\BelongsTo;
use Orm\HasMany;
use Orm\HasOne;
use Orm\RecordNotFound;
use Classes\Orm\AbstractOrmModel;
use Classes\Orm\Criteria\Model_Orm_Criteria_Where;
use Models\Whitelabel;
use Models\WhitelabelUserBalanceLog;
use Carbon\Carbon;


/**
 * todo st: fix completely this Model
 * @property int $id
 * @property string $activation_hash
 * @property string $activation_valid
 * @property string $address1
 * @property string $address2
 * @property float $balance
 * @property float $bonusBalance
 * @property float $casinoBalance
 * @property float $casinoBonusBalance
 * @property string $birthdate
 * @property string $browser_type
 * @property string $city
 * @property string $connected_aff_id Used when option "Automatically create an affiliate account for new users" is on.
 * @property string $country
 * @property string $currencyId
 * @property string $date_delete
 * @property string $date_register
 * @property string $email
 * @property string $first_deposit
 * @property float $first_deposit_amount_manager
 * @property string $first_purchase
 * @property string $gender
 * @property bool $isActive
 * @property string $hash
 * @property bool $isConfirmed
 * @property bool $isMigrated for users migration to LotteryProject
 * @property bool $isDeleted
 * @property int $languageId
 * @property string $last_active
 * @property string $last_country
 * @property float $last_deposit_amount_manager
 * @property string $last_deposit_date
 * @property string $last_ip
 * @property float $last_purchase_amount_manager
 * @property string $last_purchase_date
 * @property string $last_update
 * @property string $login
 * @property string $lines_sold_quantity
 * @property string $lost_hash
 * @property string $lost_last
 * @property string $name
 * @property string $national_id
 * @property string $net_winnings_manager
 * @property string $phone
 * @property string $phone_country
 * @property string $company
 * @property float $pnl_manager
 * @property string $refer_bonus_used
 * @property string $referrer_id
 * @property string $register_country
 * @property string $register_ip
 * @property Carbon $resendLast
 * @property string $resend_hash
 * @property string $sale_status
 * @property string $salt
 * @property string $second_deposit
 * @property string $second_purchase
 * @property string $sent_welcome_mail
 * @property string $state
 * @property string $surname
 * @property string $system_type
 * @property string $tickets_sold_quantity
 * @property string $timezone
 * @property string $token
 * @property float $total_deposit_manager
 * @property float $total_net_income_manager
 * @property float $total_purchases_manager
 * @property float $total_withdrawal_manager
 * @property int $whitelabel_id
 * @property string $zip
 * @property int $prize_payout_whitelabel_user_group_id
 * @property int $currency_id
 * @property Carbon $loginHashCreatedAt
 * @property string $loginByHashLast
 * 
 * @property BelongsTo|Currency $currency
 * @property BelongsTo|WhitelabelUserGroup $group
 * @property BelongsTo|Whitelabel|null $whitelabel
 * @property BelongsTo|WhitelabelUserAff|null $whitelabel_user_aff
 * @property BelongsTo|Language|null $language
 * @property HasOne|WhitelabelUserPromoCode|null $whitelabel_user_promo_code
 * @property HasMany|WhitelabelUserBalanceLog[]|null $whitelabel_user_balance_logs
 * @property HasMany|WhitelabelTransaction[] $whitelabel_transactions
 * @property HasMany|SlotLog[] $slot_logs
 * @property HasMany|SlotTransaction[] $slot_transactions
 * @property HasMany|WhitelabelReferStatistics $whitelabelRefererStatistics
 * @property HasMany|WhitelabelUserSocial[] $whitelabelUserSocials
 * @property HasOne|WhitelabelPluginUser|null $whitelabelPluginUser
 */
class WhitelabelUser extends AbstractOrmModel
{
    protected static bool $_cache_disabled = true;

    protected static $_table_name = 'whitelabel_user';

    protected static $_properties = [
        'id',
        'activation_hash',
        'activation_valid',
        'address_1',
        'address_2',
        'balance',
        'bonus_balance',
        'casino_balance' => ['default' => 0.00],
        'casino_bonus_balance' => ['default' => 0.00],
        'birthdate',
        'browser_type',
        'city',
        'connected_aff_id',
        'country',
        'currency_id',
        'date_delete',
        'date_register',
        'email',
        'first_deposit',
        'first_deposit_amount_manager',
        'first_purchase',
        'gender',
        'is_active',
        'hash',
        'is_confirmed',
        'is_deleted',
        'language_id',
        'last_active',
        'last_country',
        'last_deposit_amount_manager',
        'last_deposit_date',
        'last_ip',
        'last_purchase_amount_manager',
        'last_purchase_date',
        'last_update',
        'login',
        'lines_sold_quantity',
        'lost_hash',
        'lost_last',
        'name',
        'national_id',
        'net_winnings_manager',
        'phone',
        'phone_country',
        'company',
        'pnl_manager',
        'refer_bonus_used',
        'referrer_id',
        'register_country',
        'register_ip',
        'resend_last',
        'resend_hash',
        'sale_status',
        'salt',
        'second_deposit',
        'second_purchase',
        'sent_welcome_mail',
        'state',
        'surname',
        'system_type',
        'tickets_sold_quantity',
        'timezone',
        'token',
        'total_deposit_manager',
        'total_net_income_manager',
        'total_purchases_manager',
        'total_withdrawal_manager',
        'whitelabel_id',
        'zip',
        'prize_payout_whitelabel_user_group_id',
        'login_hash',
        'login_hash_created_at',
        'login_by_hash_last'
    ];

    protected $casts = [
        'id'                                    => self::CAST_INT,
        'prize_payout_whitelabel_user_group_id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'language_id' => self::CAST_INT,
        'is_active'     => self::CAST_BOOL,
        'is_deleted'    => self::CAST_BOOL,
        'is_confirmed' => self::CAST_BOOL,
        'currency_id'   => self::CAST_INT,
        'balance'       => self::CAST_FLOAT,
        'bonus_balance' => self::CAST_FLOAT,
        'casino_balance' => self::CAST_FLOAT,
        'login_hash' => self::CAST_STRING,
        'login_hash_created_at' => self::CAST_CARBON,
        'login_by_hash_last' => self::CAST_CARBON,
        'resend_last' => self::CAST_CARBON,
        'casino_bonus_balance' => self::CAST_FLOAT,
        'pnl_manager' => self::CAST_FLOAT,
        'total_net_income_manager' => self::CAST_FLOAT,
    ];

    protected static array $_has_one = [
        'whitelabel_user_promo_code' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserPromoCode::class,
            'key_to'   => 'whitelabel_user_id'
        ],
        'whitelabel_user_aff' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserAff::class,
            'key_to'   => 'whitelabel_user_id'
        ],
    ];

    protected static array $_belongs_to = [
        'group'         => [
            'key_from' => 'prize_payout_whitelabel_user_group_id',
            'model_to' => WhitelabelUserGroup::class,
            'key_to'   => 'id',
        ],
        'whitelabel' => [
            'key_from' => 'whitelabel_id',
            'model_to' => Whitelabel::class,
            'key_to'   => 'id'
        ],
    ];

    protected static array $_has_many = [
        'whitelabel_user_balance_logs' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserBalanceLog::class,
            'key_to'   => 'whitelabel_user_id'
        ]
    ];

    protected array $relations = [
        WhitelabelTransaction::class => self::HAS_MANY,
        Language::class => self::BELONGS_TO,
        SlotLog::class => self::HAS_MANY,
        SlotTransaction::class => self::HAS_MANY,
        Currency::class => self::BELONGS_TO,
        WhitelabelReferStatistics::class => self::HAS_MANY,
        WhitelabelUserSocial::class => self::HAS_MANY,
        WhitelabelPluginUser::class => self::HAS_ONE,
    ];

    public function get_active_user_by_email(string $email): self
    {
        return $this->push_criterias([
            new Model_Orm_Criteria_Where('email', $email),
            new Model_Orm_Criteria_Where('is_active', true),
        ])->get_one();
    }

    /**
     * @param int $id
     * @return $this
     *
     * @throws RecordNotFound
     */
    public function get_user_by_id(int $id): self
    {
        return $this->get_by_id($id);
    }

    public function clear_cache(): void
    {
        $this::flush_cache();
    }

    /**
     * IMPORTANT: you should only use this function on model with eager loaded whitelabel relation.
     * Especially if you expect to call it multiple times.
     */
    public function getPrefixedToken(): string
    {
        return $this->whitelabel->prefix . 'U' . $this->token;
    }

    public function isUserNotActivated(): bool
    {
        return !$this->isConfirmed && !$this->isActive;
    }
}
