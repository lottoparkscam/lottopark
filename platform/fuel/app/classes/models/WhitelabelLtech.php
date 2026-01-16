<?php

namespace Models;

use Orm\HasMany;
use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $whitelabel_id
 *
 * @property bool $is_enabled
 * @property bool $locked
 * @property bool $can_be_locked
 *
 * @property string $key
 * @property string $name
 * @property string $secret
 *
 * @property BelongsTo|Whitelabel|null $whitelabel
 *
 * @property HasMany|WhitelabelUserTicketSlip[]|null $whitelabel_user_ticket_slips
 */
class WhitelabelLtech extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_ltech';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'is_enabled',
        'locked',
        'can_be_locked',
        'key',
        'name',
        'secret'
    ];

    protected $casts = [
        'id'            => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,

        'is_enabled'    => self::CAST_BOOL,
        'locked'        => self::CAST_BOOL,
        'can_be_locked' => self::CAST_BOOL
    ];

    protected static array $_belongs_to = [
        'whitelabel' => [
            'key_from' => 'whitelabel_id',
            'model_to' => Whitelabel::class,
            'key_to' => 'id'
        ]
    ];

    protected static array $_has_many = [
        'whitelabel_user_ticket_slips' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserTicketSlip::class,
            'key_to' => 'whitelabel_ltech'
        ]
    ];
}
