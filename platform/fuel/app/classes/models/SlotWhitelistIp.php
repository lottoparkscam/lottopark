<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $slotProviderId
 * @property string $ip It is IPv4 type e.g. 127.0.0.1
 * @property BelongsTo|SlotProvider $slotProvider
 */
class SlotWhitelistIp extends AbstractOrmModel
{
    protected static string $_table_name = 'slot_whitelist_ip';

    protected static array $_properties = [
        'id',
        'slot_provider_id',
        'ip'
    ];

    protected $casts = [
        'id' => 'integer',
        'slot_provider_id' => 'integer',
        'ip' => 'string'
    ];

    protected array $relations = [
        SlotProvider::class => self::BELONGS_TO
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
