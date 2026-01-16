<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * This model is used to store the bonus usage information by a specific user
 * Created for registration scenario purpose, but can handle other scenarios as well.
 * Bonus can be used only once in the registration scenario.
 *
 * @property int $id
 * @property int $bonusId
 * @property string $type
 * @property string $lotteryType
 * @property int $whitelabelUserId
 * @property string $usedAt gets information about the date and time when the bonus was used.
 *
 * @property BelongsTo|WhitelabelUser $user
 */
class WhitelabelUserBonus extends AbstractOrmModel
{
    public const TYPE_PURCHASE = WhitelabelBonus::WELCOME_PURCHASE;
    public const TYPE_REGISTER = WhitelabelBonus::WELCOME_REGISTER;

    public const TYPE_LOTTERY = WhitelabelBonus::WELCOME_TYPE_LOTTERY;
    public const TYPE_RAFFLE = WhitelabelBonus::WELCOME_TYPE_RAFFLE;

    protected static $_table_name = 'whitelabel_user_bonus';

    protected static $_properties = [
        'id',
        'bonus_id',
        'type',
        'lottery_type',
        'whitelabel_user_id',
        'used_at',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'bonus_id' => self::CAST_INT,
        'type' => self::CAST_STRING,
        'lottery_type' => self::CAST_STRING,
        'whitelabel_user_id' => self::CAST_INT,
        'used_at' => self::CAST_DATETIME,
    ];

    protected array $relations = [
        WhitelabelUser::class => self::BELONGS_TO
    ];

    protected static array $_belongs_to = [
        'user' => [
            'key_from' => 'whitelabel_user_id',
            'model_to' => WhitelabelUser::class,
            'key_to' => 'id',
        ],
    ];

    // It is very important! Do not remove this variables!
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function isFreeTicketRaffleAvailableForUser(int $userId): bool
    {
        return $this->isRaffle() && !$this->isUsedByUser($userId);
    }

    public function isRaffle(): bool
    {
        return $this->lotteryType === WhitelabelBonus::WELCOME_TYPE_RAFFLE;
    }

    public function isUsedByUser(int $userId): bool
    {
        return $this->whitelabelUserId === $userId && $this->usedAt !== null;
    }
}