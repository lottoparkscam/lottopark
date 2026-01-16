<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelAffId
 * @property string $campaign
 * @property bool $isCasino
 * 
 * @property BelongsTo|WhitelabelAff[] $whitelabelAff
 */
class WhitelabelAffCampaign extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_aff_campaign';

    protected static array $_properties = [
        'id',
        'whitelabel_aff_id',
        'campaign',
        'is_casino' => ['default' => false],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_aff_id' => self::CAST_INT,
        'campaign' => self::CAST_STRING,
        'is_casino' => self::CAST_BOOL,
    ];

    protected array $relations = [
        WhitelabelAff::class => self::BELONGS_TO
    ];

    protected array $timezones = [];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
