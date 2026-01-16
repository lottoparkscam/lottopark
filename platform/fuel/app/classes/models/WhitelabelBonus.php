<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $bonusId
 * @property int $purchaseLotteryId
 * @property int $registerLotteryId
 * @property int $purchaseRaffleId
 * @property int $registerRaffleId
 * @property float $minTotalPurchase
 * @property int $registerWebsite
 * @property int $registerApi
 */
class WhitelabelBonus extends AbstractOrmModel
{
    const WELCOME = 1;
    const REFER_A_FRIEND = 2;

    public const WELCOME_TYPE_LOTTERY = 'lottery';
    public const WELCOME_TYPE_RAFFLE = 'raffle';

    public const WELCOME_PURCHASE = 'purchase';
    public const WELCOME_REGISTER = 'register';

    protected static string $_table_name = 'whitelabel_bonus';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'bonus_id',
        'purchase_lottery_id',
        'register_lottery_id',
        'purchase_raffle_id',
        'register_raffle_id',
        'register_website',
        'register_api',
        'min_total_purchase'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'bonus_id' => self::CAST_INT,
        'purchase_lottery_id' => self::CAST_INT,
        'register_lottery_id' => self::CAST_INT,
        'purchase_raffle_id' => self::CAST_INT,
        'register_raffle_id' => self::CAST_INT,
        'register_website' => self::CAST_INT,
        'register_api' => self::CAST_INT,
        'min_total_purchase' => self::CAST_FLOAT,
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        /* These relations don't work well since fuel cannot recognize
         * the related table name by field name.
         * Noticed when trying to delete a record.
         *
         * Lottery::class => self::BELONGS_TO,
         * Raffle::class => self::BELONGS_TO
         */
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function getPurchaseId(): ?int
    {
        $lotteryFieldName = self::WELCOME_PURCHASE . '_' . self::WELCOME_TYPE_LOTTERY . '_id';
        $raffleFieldName = self::WELCOME_PURCHASE . '_' . self::WELCOME_TYPE_RAFFLE . '_id';

        return $this->$lotteryFieldName ?? $this->$raffleFieldName ?? null;
    }

    public function getRegisterId(): ?int
    {
        $lotteryFieldName = self::WELCOME_REGISTER . '_' . self::WELCOME_TYPE_LOTTERY . '_id';
        $raffleFieldName = self::WELCOME_REGISTER . '_' . self::WELCOME_TYPE_RAFFLE . '_id';

        return $this->$lotteryFieldName ?? $this->$raffleFieldName ?? null;
    }

    public function getPurchaseLotteryType(): string
    {
        if(empty($this->purchaseLotteryId) && !empty($this->purchaseRaffleId)){
            return self::WELCOME_TYPE_RAFFLE;
        }

        return self::WELCOME_TYPE_LOTTERY;
    }

    public function getRegisterLotteryType(): string
    {
        if(empty($this->registerLotteryId) && !empty($this->registerRaffleId)){
            return self::WELCOME_TYPE_RAFFLE;
        }

        return self::WELCOME_TYPE_LOTTERY;
    }

    public function isWebsiteRegistrationAllowed(): bool
    {
        return (bool) $this->registerWebsite;
    }

    public function isApiRegistrationAllowed(): bool
    {
        return (bool) $this->registerApi;
    }
}