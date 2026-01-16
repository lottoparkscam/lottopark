<?php

namespace Models;

use Carbon\Carbon;
use DateTime;
use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $whitelabelAffParentId
 * @property int $languageId
 * @property int $currencyId
 * @property int $whitelabelAffGroupId
 * @property ?int $whitelabelAffCasinoGroupId
 * @property int $whitelabelAffWithdrawalId
 * @property boolean $isActive
 * @property boolean $isConfirmed
 * @property boolean $isAccepted
 * @property boolean $isAffUser
 * @property string $login
 * @property string $email
 * @property string $token
 * @property string $subAffiliateToken
 * @property string $hash
 * @property string $salt
 * @property string $passwordResetHash
 * @property string $company
 * @property string $name
 * @property string $surname
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $country
 * @property string $state
 * @property string $zip
 * @property string $phoneCountry
 * @property string $phone
 * @property DateTime|Carbon $birthdate
 * @property string $timezone
 * @property string $withdrawalData
 * @property string $analytics
 * @property string $fbPixel
 * @property int $fbPixelMatch
 * @property DateTime|Carbon $dateCreated
 * @property string $lastIp
 * @property string $lastCountry
 * @property DateTime|Carbon $lastActive
 * @property boolean $isDeleted
 * @property DateTime|Carbon $dateDelete
 * @property int $affLeadLifetime
 * @property boolean $isShowName
 * @property int $hideLeadId
 * @property int $hideTransactionId
 * @property string $activationHash
 * @property DateTime|Carbon $activationValid
 * @property string $resendHash
 * @property DateTime|Carbon $resendLast
 *
 * @property HasMany|WhitelabelUserAff[] $whitelabelUserAffs
 * @property BelongsTo|WhitelabelAffGroup $whitelabelAffGroup
 * @property BelongsTo|WhitelabelAffCasinoGroup $whitelabelAffCasinoGroup
 */
class WhitelabelAff extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_aff';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'whitelabel_aff_parent_id',
        'language_id',
        'currency_id',
        'whitelabel_aff_group_id',
        'whitelabel_aff_casino_group_id' => ['default' => null],
        'whitelabel_aff_withdrawal_id',
        'is_active',
        'is_confirmed',
        'is_accepted',
        'is_aff_user',
        'login',
        'email',
        'token',
        'sub_affiliate_token',
        'hash',
        'salt',
        'password_reset_hash',
        'company',
        'name',
        'surname',
        'address_1',
        'address_2',
        'city',
        'country',
        'state',
        'zip',
        'phone_country',
        'phone',
        'birthdate',
        'timezone',
        'withdrawal_data',
        'analytics',
        'fb_pixel',
        'fb_pixel_match',
        'date_created',
        'last_ip',
        'last_country',
        'last_active',
        'is_deleted',
        'date_delete',
        'aff_lead_lifetime',
        'is_show_name',
        'hide_lead_id',
        'hide_transaction_id',
        'activation_hash',
        'activation_valid',
        'resend_hash',
        'resend_last',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_aff_parent_id' => self::CAST_INT,
        'language_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'whitelabel_aff_group_id' => self::CAST_INT,
        'whitelabel_aff_casino_group_id' => self::CAST_INT,
        'whitelabel_aff_withdrawal_id' => self::CAST_INT,
        'is_active' => self::CAST_BOOL,
        'is_confirmed' => self::CAST_BOOL,
        'is_accepted' => self::CAST_BOOL,
        'is_aff_user' => self::CAST_BOOL,
        'login' => self::CAST_STRING,
        'email' => self::CAST_STRING,
        'token' => self::CAST_STRING,
        'sub_affiliate_token' => self::CAST_STRING,
        'hash' => self::CAST_STRING,
        'salt' => self::CAST_STRING,
        'password_reset_hash' => self::CAST_STRING,
        'company' => self::CAST_STRING,
        'name' => self::CAST_STRING,
        'surname' => self::CAST_STRING,
        'address_1' => self::CAST_STRING,
        'address_2' => self::CAST_STRING,
        'city' => self::CAST_STRING,
        'country' => self::CAST_STRING,
        'state' => self::CAST_STRING,
        'zip' => self::CAST_STRING,
        'phone_country' => self::CAST_STRING,
        'phone' => self::CAST_STRING,
        'birthdate' => self::CAST_CARBON,
        'timezone' => self::CAST_STRING,
        'withdrawal_data' => self::CAST_STRING,
        'analytics' => self::CAST_STRING,
        'fb_pixel' => self::CAST_STRING,
        'fb_pixel_match' => self::CAST_INT,
        'date_created' => self::CAST_CARBON,
        'last_ip' => self::CAST_STRING,
        'last_country' => self::CAST_STRING,
        'last_active' => self::CAST_CARBON,
        'is_deleted' => self::CAST_BOOL,
        'date_delete' => self::CAST_CARBON,
        'aff_lead_lifetime' => self::CAST_INT,
        'is_show_name' => self::CAST_BOOL,
        'hide_lead_id' => self::CAST_INT,
        'hide_transaction_id' => self::CAST_INT,
        'activation_hash' => self::CAST_STRING,
        'activation_valid' => self::CAST_CARBON,
        'resend_hash' => self::CAST_STRING,
        'resend_last' => self::CAST_CARBON,
    ];

    protected array $relations = [
    	WhitelabelUserAff::class => self::HAS_MANY,
        WhitelabelAffGroup::class => self::BELONGS_TO,
        WhitelabelAffCasinoGroup::class => self::BELONGS_TO,
	];

    protected array $timezones = [];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
