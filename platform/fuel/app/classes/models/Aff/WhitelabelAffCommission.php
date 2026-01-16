<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Models\WhitelabelAff;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelAffId
 * @property int $whitelabelUserAffId
 * @property int $whitelabelTransactionId
 * @property int $currencyId
 * @property int $paymentCurrencyId
 * @property int $type
 * @property int $tier
 * @property float $commission
 * @property float $commissionUsd
 * @property float $commissionPayment
 * @property float $commissionManager
 * @property boolean $isAccepted
 *
 * @property BelongsTo|WhitelabelAff|null $whitelabelAff
 * @property BelongsTo|WhitelabelTransaction $whitelabelTransaction
 */
class WhitelabelAffCommission extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_aff_commission';

    protected static array $_properties = [
        'id',
        'whitelabel_aff_id',
        'whitelabel_user_aff_id',
        'whitelabel_transaction_id',
        'currency_id',
        'payment_currency_id',
        'type',
        'tier',
        'commission',
        'commission_usd',
        'commission_payment',
        'commission_manager',
        'is_accepted',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_aff_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'whitelabel_transaction_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'payment_currency_id' => self::CAST_INT,
        'type' => self::CAST_INT,
        'tier' => self::CAST_INT,
        'commission' => self::CAST_FLOAT,
        'commission_usd' => self::CAST_FLOAT,
        'commission_payment' => self::CAST_FLOAT,
        'commission_manager' => self::CAST_FLOAT,
        'is_accepted' => self::CAST_BOOL
    ];

    protected array $relations = [
        WhitelabelAff::class => self::BELONGS_TO
    ];

    protected array $timezones = [];

    protected static array $_belongs_to = [];

    protected static array $_has_one = [];

    protected static array $_has_many = [];
}
