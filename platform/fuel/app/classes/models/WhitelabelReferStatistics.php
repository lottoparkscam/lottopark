<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $whitelabelUserId
 * @property int $token
 * @property int $clicks
 * @property int $uniqueClicks
 * @property int $registrations
 * @property int $freeTickets
 *
 * @property BelongsTo|Whitelabel $whitelabel
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 */
class WhitelabelReferStatistics extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_refer_statistics';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'whitelabel_user_id',
        'token',
        'clicks',
        'unique_clicks',
        'registrations',
        'free_tickets'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_user_Id' => self::CAST_INT,
        'token' => self::CAST_INT,
        'clicks' => self::CAST_INT,
        'unique_clicks' => self::CAST_INT,
        'registrations' => self::CAST_INT,
        'free_tickets' => self::CAST_INT
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
