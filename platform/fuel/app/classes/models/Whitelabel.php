<?php

namespace Models;

use Helpers_General;
use Orm\HasOne;
use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $languageId
 * @property string $name
 * @property bool $isActive
 * @property string $domain
 * @property string $type https://gginternational.slite.com/app/docs/tn7hjDGgVN?source=search
 * @property int $userActivationType
 * @property string $userRegistrationThroughRefOnly
 * @property int $affActivationType
 * @property string $affAutoAccept
 * @property string $affPayoutType
 * @property string $affLeadAutoAccept
 * @property string $affRefLifetime
 * @property string $affHideTicketAndPaymentCost
 * @property bool $affHideAmount
 * @property string $affHideIncome
 * @property string $affEnableSignUps
 * @property bool $affAutoCreateOnRegister
 * @property float $maxPayout
 * @property float $welcomePopupTimeout
 * @property int $registerNameSurname Set in manager for three properties, require(2)/optional(1)/none(0).
 * @property int $registerPhone Set in manager for three properties, require(2)/optional(1)/none(0).
 * @property int $useRegisterCompany Set in manager for three properties, require(2)/optional(1)/none(0).
 * @property string $username
 * @property string $hash
 * @property string $salt
 * @property string $email
 * @property string $realname
 * @property string $companyDetails
 * @property string $licence
 * @property string $timezone
 * @property string $prefix
 * @property string $defCommissionValue
 * @property string $defCommissionValueManager
 * @property string $def_commission_value_2
 * @property string $def_commission_value_2_manager
 * @property string $defFtpCommissionValue
 * @property string $def_ftp_commission_value_manager
 * @property string $def_ftp_commission_value_2
 * @property string $def_ftp_commission_value_2_manager
 * @property float|null $defaultCasinoCommissionPercentageValueForTier1 (5,2) - default % value for 1st casino commission line
 * @property float|null $defaultCasinoCommissionPercentageValueForTier2 (5,2) - default % value for 2nd casino commission line
 * @property string $analytics
 * @property string $analytics_casino
 * @property string $defaultSiteCurrency
 * @property string $fbPixel
 * @property string $fbPixelMatch
 * @property float $margin
 * @property float $prepaid
 * @property string $prepaidAlertLimit
 * @property int $managerSiteCurrencyId
 * @property string $maxOrderCount
 * @property float $userBalanceChangeLimit
 * @property string $theme
 * @property string $usStateActive
 * @property string $enabledUsStates
 * @property string $showOkInWelcomePopup
 * @property string $lastLogin
 * @property string $lastActive
 * @property string $isReport
 * @property bool $assertUniqueEmailsForUsers
 * @property bool $useLoginsForUsers
 * @property float $defaultWhitelabelUserGroupId
 * @property string $userCanChangeGroup
 * @property string $canUserSelectGroupWhileRegister
 * @property bool $canUserRegisterViaSite
 * @property bool $canUserLoginViaSite
 * @property bool $displayDepositButton
 * @property bool $isScansDisplayedForUsers
 * @property string $supportEmail
 * @property string $paymentEmail
 *
 * @property float $maxDailyBalanceChangePerUser
 *
 * @property bool $showCategories
 * @property bool $isReducingBalanceIncreasesLimits
 * @property bool $isBalanceChangeGlobalLimitEnabledInApi
 *
 * @property BelongsTo|Currency $currency
 *
 * @property HasMany|WhitelabelWithdrawal[]|null $whitelabelWithdrawals
 * @property HasMany|WhitelabelUserTicketSlip[]|null $whitelabelUserTicketSlips
 * @property HasMany|WhitelabelUserTicket[]|null $whitelabelUserTickets
 *
 * @property HasOne|WhitelabelLtech|null $whitelabelLtech
 * @property HasMany|WhitelabelUser[]|null $whitelabelUsers
 *
 * @property HasMany|WhitelabelLottery[] $whitelabelLotteries
 * @property HasMany|WhitelabelPlugin[] $whitelabelPlugins
 * @property HasMany|CrmLog[] $crmLogs
 * @property HasMany|WithdrawalRequest[] $withdrawalRequests
 * @property HasMany|WhitelabelPaymentMethod[] $whitelabelPaymentMethods
 * @property HasOne|WhitelabelApi|null $whitelabelApi
 * @property HasMany|WhitelabelSlotProvider[] $whitelabelSlotProviders
 * @property HasMany|CleanerLog[] $cleanerLogs
 * @property HasMany|SynchronizerLog[] $synchronizerLogs
 * @property HasMany|WhitelabelReferStatistics[] $whitelabelRefererStatistics
 * @property HasOne|CloudflareZone $cloudflareZone
 * @property HasOne|WhitelabelSlotGameOrder $whitelabelSlotGameOrder
 * @property HasMany|WhitelabelSocialApi[] $whitelabelSocialApis
 * @property HasMany|WhitelabelLanguage[] $whitelabelLanguages
 */
class Whitelabel extends AbstractOrmModel
{
    public const TYPE_V1 = 1;
    public const TYPE_V2 = 2;

    public const LOTTOPARK_PREFIX = 'LP';

    public const PREMIERLOTO_THEME = 'premierloto';
    public const FAIREUM_THEME = 'faireum';
    public const LOVCASINO_THEME = 'lovcasino';
    public const LOTTOHOY_THEME = 'lottohoy';
    public const REDFOXLOTTO_THEME = 'redfoxlotto';
    public const LOTTOMAT_THEME = 'lottomat';
    public const LOTTOPARK_THEME = 'lottopark';
    public const DOUBLEJACK_THEME = 'doublejack';
    public const LOTTERYKING_THEME = 'lotteryking';
    public const LOTOKING_THEME = 'lotoking';
    public const LUMINARIAGAMES_THEME = 'luminariagames';
    public const LOTTOBAZAR_THEME = 'lottobazar';

    public const WHITELABELS_WITH_CASINO_BANNER = [
        self::LOTTOPARK_THEME,
        self::LOTTOMAT_THEME,
        self::LOTTOHOY_THEME,
        self::LOVCASINO_THEME,
        self::DOUBLEJACK_THEME,
        self::LOTTERYKING_THEME,
        self::LUMINARIAGAMES_THEME,
        self::LOTTOBAZAR_THEME,
    ];

    public const ACTIVATION_TYPE_NONE = 0;
    public const ACTIVATION_TYPE_OPTIONAL = 1;
    public const ACTIVATION_TYPE_REQUIRED = 2;

    protected static $_table_name = 'whitelabel';

    protected static $_properties = [
        'id',
        'language_id',
        'name',
        'is_active' => ['default' => true],
        'domain',
        'type',
        'user_activation_type',
        'user_registration_through_ref_only',
        'aff_activation_type',
        'aff_auto_accept',
        'aff_payout_type',
        'aff_lead_auto_accept',
        'aff_ref_lifetime',
        'aff_hide_ticket_and_payment_cost',
        'aff_hide_amount' => ['default' => false],
        'aff_hide_income',
        'aff_enable_sign_ups',
        'max_payout',
        'welcome_popup_timeout',
        'username',
        'hash',
        'salt',
        'email',
        'realname',
        'company_details',
        'licence' => ['default' => '8048/JAZ'],
        'timezone',
        'prefix',
        'def_commission_value',
        'def_commission_value_manager',
        'def_commission_value_2',
        'def_commission_value_2_manager',
        'def_ftp_commission_value',
        'def_ftp_commission_value_manager',
        'def_ftp_commission_value_2',
        'def_ftp_commission_value_2_manager',
        'default_casino_commission_percentage_value_for_tier_1' => ['default' => null],
        'default_casino_commission_percentage_value_for_tier_2' => ['default' => null],
        'analytics',
        'analytics_casino',
        'default_site_currency',
        'fb_pixel',
        'fb_pixel_match',
        'margin',
        'prepaid',
        'prepaid_alert_limit',
        'manager_site_currency_id',
        'max_order_count',
        'user_balance_change_limit',
        'theme',
        'us_state_active',
        'enabled_us_states',
        'show_ok_in_welcome_popup',
        'show_categories',
        'last_login',
        'last_active',
        'is_report',
        'default_whitelabel_user_group_id',
        'user_can_change_group',
        'can_user_select_group_while_register',
        'can_user_register_via_site'        => ['default' => 1],
        'can_user_login_via_site'           => ['default' => 1],
        'display_deposit_button'            => ['default' => 1],
        'is_scans_displayed_for_users'      => ['default' => true],
        'max_daily_balance_change_per_user'   => ['default' => 0],
        'is_reducing_balance_increases_limits' => ['default' => false],
        'is_balance_change_global_limit_enabled_in_api' => ['default' => true],
        'use_logins_for_users'               => ['default' => false],
        'register_name_surname'              => ['default' => 0],
        'register_phone'                     => ['default' => 0],
        'use_register_company'                     => ['default' => 0],
        'assert_unique_emails_for_users'     => ['default' => true],
        'aff_auto_create_on_register'        => ['default' => false],
        'support_email',
        'payment_email',
    ];

    protected $casts = [
        'id'                       => self::CAST_INT,
        'manager_site_currency_id' => self::CAST_INT,
        'language_id'              => self::CAST_INT,
        'user_activation_type'     => self::CAST_INT,
        'aff_activation_type'      => self::CAST_INT,

        'name' => self::CAST_STRING,
        'is_active' => self::CAST_BOOL,
        'company_details' => self::CAST_STRING,
        'licence' => self::CAST_STRING,
        'can_user_register_via_site'            => self::CAST_BOOL,
        'can_user_login_via_site'               => self::CAST_BOOL,
        'display_deposit_button'                => self::CAST_BOOL,
        'is_scans_displayed_for_users'          => self::CAST_BOOL,
        'is_reducing_balance_increases_limits'  => self::CAST_BOOL,
        'is_balance_change_global_limit_enabled_in_api' => self::CAST_BOOL,
        'use_logins_for_users'                  => self::CAST_BOOL,
        'register_name_surname'                 => self::CAST_INT,
        'register_phone'                        => self::CAST_INT,
        'use_register_company'                  => self::CAST_INT,
        'assert_unique_emails_for_users'        => self::CAST_BOOL,
        'aff_auto_create_on_register'           => self::CAST_BOOL,
        'show_categories'                       => self::CAST_BOOL,
        'aff_hide_amount' => self::CAST_BOOL,
        'prepaid'                           => self::CAST_FLOAT,
        'margin'                            => self::CAST_FLOAT,
        'max_daily_balance_change_per_user'   => self::CAST_FLOAT,
        'default_whitelabel_user_group_id'  => self::CAST_FLOAT,
        'user_balance_change_limit'         => self::CAST_FLOAT,
        'support_email' => self::CAST_STRING,
        'payment_email' => self::CAST_STRING,
        'default_casino_commission_percentage_value_for_tier_1' => self::CAST_FLOAT,
        'default_casino_commission_percentage_value_for_tier_2' => self::CAST_FLOAT,
    ];

    protected static array $_belongs_to = [
        'currency' => [
            'key_from' => 'manager_site_currency_id',
            'model_to' => Currency::class,
            'key_to'   => 'id',
        ],
    ];

    protected array $relations = [
        Currency::class => self::BELONGS_TO,
        CloudflareZone::class => self::HAS_ONE,
        WhitelabelLtech::class => self::HAS_ONE,
        WhitelabelLottery::class => self::HAS_MANY,
        CrmLog::class => self::HAS_MANY,
        WithdrawalRequest::class => self::HAS_MANY,
        WhitelabelPaymentMethod::class => self::HAS_MANY,
        WhitelabelApi::class => self::HAS_ONE,
        WhitelabelSlotProvider::class => self::HAS_MANY,
        CleanerLog::class => self::HAS_MANY,
        SynchronizerLog::class => self::HAS_MANY,
        WhitelabelPlugin::class => self::HAS_MANY,
        WhitelabelUser::class => self::HAS_MANY,
        WhitelabelUserTicket::class => self::HAS_MANY,
        WhitelabelUserTicketSlip::class => self::HAS_MANY,
        WhitelabelWithdrawal::class => self::HAS_MANY,
        WhitelabelReferStatistics::class => self::HAS_MANY,
        WhitelabelSlotGameOrder::class => self::HAS_ONE,
        WhitelabelSocialApi::class => self::HAS_MANY,
        WhitelabelOAuthClient::class => self::HAS_MANY,
        WhitelabelLanguage::class => self::HAS_MANY,
    ];

    // NOTE: it could be done without hardcoding by querying over whitelabel_slot_provider
    public function hasCasinoBanner(): bool
    {
        return in_array($this->theme, self::WHITELABELS_WITH_CASINO_BANNER);
    }

    public function isV2(): bool
    {
        return (int)$this->type === self::TYPE_V2;
    }

    public function isV1(): bool
    {
        return (int)$this->type === self::TYPE_V1;
    }

    public function isTheme(string $theme): bool
    {
        return $this->theme === $theme;
    }

    public function isNotTheme(string $theme): bool
    {
        return !$this->isTheme($theme);
    }

    public function loginForUserIsUsedDuringRegistration(): bool
    {
        return $this->useLoginsForUsers;
    }

    public function isNameSurnameRequiredDuringRegistration(): bool
    {
        return $this->registerNameSurname === Helpers_General::REGISTER_FIELD_REQUIRED;
    }

    public function isPhoneRequiredDuringRegistration(): bool
    {
        return $this->registerPhone === Helpers_General::REGISTER_FIELD_REQUIRED;
    }

    public function isCompanyRequiredDuringRegistration(): bool
    {
        return $this->useRegisterCompany === Helpers_General::REGISTER_FIELD_REQUIRED;
    }

    public function isNameAndSurnameUsedDuringRegistration(): bool
    {
        return in_array($this->registerNameSurname, Helpers_General::DISPLAY_REGISTER_FIELD_VALUES);
    }

    public function isPhoneUsedDuringRegistration(): bool
    {
        return in_array($this->registerPhone, Helpers_General::DISPLAY_REGISTER_FIELD_VALUES);
    }

    public function isCompanyUsedDuringRegistration(): bool
    {
        return in_array($this->useRegisterCompany, Helpers_General::DISPLAY_REGISTER_FIELD_VALUES);
    }

    public function hasCasino(): bool
    {
        if (empty($this->whitelabelSlotProviders)) {
            return false;
        }

        foreach ($this->whitelabelSlotProviders as $provider) {
            if ($provider->isEnabled) {
                return true;
            }
        }
        return false;
    }

    public function isActivationForUsersRequired(): bool
    {
        return $this->userActivationType === self::ACTIVATION_TYPE_REQUIRED;
    }

    protected static array $_has_many = [];
    protected static array $_has_one = [];
}
