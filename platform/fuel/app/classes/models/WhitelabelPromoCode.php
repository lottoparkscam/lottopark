<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property string $token
 * @property int $whitelabel_campaign_id
 *
 * @property BelongsTo|WhitelabelCampaign $whitelabel_campaign
 */
class WhitelabelPromoCode extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_promo_code';

    protected static array $_properties = [
        'id',
        'token',
        'whitelabel_campaign_id',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_campaign_id' => self::CAST_INT,
    ];

    protected array $relations = [
    	WhitelabelCampaign::class => self::BELONGS_TO
	];

    protected array $timezones = [];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
