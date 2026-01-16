<?php

namespace Models;

use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $whitelabelId
 * @property int $id
 * @property int $slotProviderId
 * @property bool $isEnabled Disable possibility to play slots
 * @property bool $isLimitEnabled
 * @property float $maxMonthlyMoneyAroundUsd
 *
 * @property HasMany|SlotLog[] $slotLogs
 * @property HasMany|SlotOpenGame[] $slotOpenGames
 * @property HasMany|SlotTransaction[] $slotTransactions
 * @property HasMany|WhitelabelSlotProviderSubprovider[] $whitelabelSlotProviderSubproviders
 * @property BelongsTo|SlotProvider $slotProvider
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WhitelabelSlotProvider extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_slot_provider';

    protected static array $_properties = [
        'id',
        'slot_provider_id',
        'whitelabel_id',
        'is_enabled' => ['default' => false],
        'is_limit_enabled' => ['default' => false],
        'max_monthly_money_around_usd' => ['default' => 50000.0],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'slot_provider_id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'is_enabled' => self::CAST_BOOL,
        'is_limit_enabled' => self::CAST_BOOL,
        'max_monthly_money_around_usd' => self::CAST_FLOAT,
    ];

    protected array $relations = [
        SlotProvider::class => self::BELONGS_TO,
        Whitelabel::class => self::BELONGS_TO,
        SlotLog::class => self::HAS_MANY,
        SlotOpenGame::class => self::HAS_MANY,
        SlotTransaction::class => self::HAS_MANY,
        WhitelabelSlotProviderSubprovider::class => self::HAS_MANY,
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function isLimitDisabled(): bool
    {
        return !$this->isLimitEnabled;
    }
}
