<?php

namespace Models;

use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $whitelabel_id
 * @property int $lottery_id
 * @property int $lottery_provider_id
 * @property int $tier
 * @property int $minLines
 * @property int $quickPickLines
 *
 * @property bool $isEnabled
 * @property int $model 0 = Purchase, 1 = Mixed, 2 = Purchase + Scan, 3 = None
 * @property bool $is_multidraw_enabled
 * @property bool $is_bonus_balance_in_use
 * @property int $bonusBalancePurchaseLimitPerUser Daily limit for how many ticket lines user can buy. 0 = unlimited (default). Limits reset at 00:00 UTC.
 * @property bool $should_decrease_prepaid
 * @property bool $isScanInCrmEnabled Enable scans in manager too.
 * @property bool $ltech_lock
 *
 * @property float $income
 * @property int $incomeType
 * @property float $volume
 * @property float $minimum_expected_income
 *
 * @property HasMany|WhitelabelUserTicketSlip[]|null $whitelabel_user_ticket_slips
 * @property BelongsTo|LotteryProvider $lotteryProvider
 * @property BelongsTo|Whitelabel|null $whitelabel
 * @property BelongsTo|Lottery|null $lottery
 */
class WhitelabelLottery extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_lottery';
    public const BONUS_BALANCE_PURCHASE_LIMIT_PER_USER_UNLIMITED = 0;
    public const LOTTERY_RELATION = 'lottery';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'lottery_id',
        'lottery_provider_id',
        'tier',
        'min_lines',
        'quick_pick_lines',

        'is_enabled',
        'model',
        'is_multidraw_enabled',
        'is_bonus_balance_in_use',
        'bonus_balance_purchase_limit_per_user',
        'should_decrease_prepaid',
        'is_scan_in_crm_enabled' => ['default' => true],
        'ltech_lock',

        'income',
        'income_type',
        'volume',
        'minimum_expected_income'
    ];

    protected $casts = [
        'id'                                    => self::CAST_INT,
        'whitelabel_id'                         => self::CAST_INT,
        'lottery_id'                            => self::CAST_INT,
        'lottery_provider_id'                   => self::CAST_INT,
        'tier'                                  => self::CAST_INT,
        'min_lines'                             => self::CAST_INT,
        'quick_pick_lines'                      => self::CAST_INT,

        'is_enabled'                            => self::CAST_BOOL,
        'model'                                 => self::CAST_INT,
        'is_multidraw_enabled'                  => self::CAST_BOOL,
        'is_bonus_balance_in_use'               => self::CAST_BOOL,
        'bonus_balance_purchase_limit_per_user' => self::CAST_INT,
        'should_decrease_prepaid'               => self::CAST_BOOL,
        'is_scan_in_crm_enabled'            => self::CAST_BOOL,
        'ltech_lock'                            => self::CAST_BOOL,

        'income'                                => self::CAST_FLOAT,
        'income_type'                           => self::CAST_INT,
        'volume'                                => self::CAST_FLOAT,
        'minimum_expected_income'               => self::CAST_FLOAT
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        Lottery::class => self::BELONGS_TO,
        LotteryProvider::class => self::BELONGS_TO,
        WhitelabelUserTicketSlip::class => self::HAS_MANY,
    ];

    // It is very important! Do not remove this variables!
    protected static array $_has_one = [];
    protected static array $_has_many = [];
    protected static array $_belongs_to = [];
}
