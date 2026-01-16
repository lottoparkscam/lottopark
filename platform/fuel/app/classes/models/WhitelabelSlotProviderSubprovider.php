<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Models\SlotSubprovider;
use Models\WhitelabelSlotProvider;

/**
 * @property int $id
 * @property int $whitelabelSlotProviderId
 * @property int $slotSubproviderId
 * @property bool $isEnabled this field is updated automatically by sync games task.
 * @property bool $forceDisable when it is set, disables auto change of $isEnabled by sync task
 * 
 * @property BelongsTo|WhitelabelSlotProvider $whitelabelSlotProvider
 * @property BelongsTo|SlotSubprovider $slotSubprovider
 */
class WhitelabelSlotProviderSubprovider extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_slot_provider_subprovider';

    protected static array $_properties = [
        'id',
        'whitelabel_slot_provider_id',
        'slot_subprovider_id',
        'is_enabled' => ['default' => true],
        'force_disable' => ['default' => false],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_slot_provider_id' => self::CAST_INT,
        'slot_subprovider_id' => self::CAST_INT,
        'is_enabled' => self::CAST_BOOL,
        'force_disable' => self::CAST_BOOL,
    ];

    protected array $relations = [
        WhitelabelSlotProvider::class => self::BELONGS_TO,
        SlotSubprovider::class => self::BELONGS_TO,
    ];

    protected array $timezones = [];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
