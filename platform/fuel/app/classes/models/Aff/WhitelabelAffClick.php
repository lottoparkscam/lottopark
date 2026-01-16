<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Carbon\Carbon;

/**
 * @property int $id
 * @property int $whitelabelAffId
 * @property Carbon $date
 * @property int $whitelabelAffMediumId
 * @property int $whitelabelAffCampaignId
 * @property int $whitelabelAffContentId
 * @property int $all
 * @property int $unique
 */
class WhitelabelAffClick extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_aff_click';

    protected static array $_properties = [
        'id',
        'whitelabel_aff_id',
        'date',
        'whitelabel_aff_medium_id',
        'whitelabel_aff_campaign_id',
        'whitelabel_aff_content_id',
        'all',
        'unique'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_aff_id' => self::CAST_INT,
        'date' => self::CAST_CARBON,
        'whitelabel_aff_medium_id' => self::CAST_INT,
        'whitelabel_aff_campaign_id' => self::CAST_INT,
        'whitelabel_aff_content_id' => self::CAST_INT,
        'all' => self::CAST_INT,
        'unique' => self::CAST_INT
    ];

    protected array $relations = [
    ];

    protected array $timezones = [
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}