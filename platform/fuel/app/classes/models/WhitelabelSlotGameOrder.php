<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * This model has only one record per whitelabel.
 * $orderJson->category - decides where the order is changed. Currently we support only homepage, but others will appear in future releases.
 *
 * @property int $id
 * @property int $whitelabelId
 * @property array $orderJson eg. [$category => [gameId: $slotGameId, gameOrder: $orderNumber]]
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WhitelabelSlotGameOrder extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_slot_game_order';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'order_json',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'order_json' => self::CAST_ARRAY,
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO
    ];

    protected array $timezones = [];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
