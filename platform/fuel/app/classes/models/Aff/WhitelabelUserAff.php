<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $whitelabelUserId
 * @property int $whitelabelAffId
 * @property int $whitelabelAffMediumId
 * @property int $whitelabelAffCampaignId
 * @property int $whitelabelAffContentId
 * @property string $externalId
 * @property string $btag
 * @property boolean $isDeleted
 * @property boolean $isAccepted
 * @property boolean $isExpired
 * @property boolean $isCasino
 * 
 * @property BelongsTo|WhitelabelAff|null $whitelabel_aff
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 */
class WhitelabelUserAff extends AbstractOrmModel
{
    protected static string $_table_name = 'whitelabel_user_aff';

    protected static array $_properties = [
        'id',
        'whitelabel_id',
        'whitelabel_user_id',
        'whitelabel_aff_id',
        'whitelabel_aff_medium_id',
        'whitelabel_aff_campaign_id',
        'whitelabel_aff_content_id',
        'external_id',
        'btag',
        'is_deleted',
        'is_accepted',
        'is_expired',
        'is_casino',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'whitelabel_aff_id' => self::CAST_INT,
        'whitelabel_aff_medium_id' => self::CAST_INT,
        'whitelabel_aff_campaign_id' => self::CAST_INT,
        'whitelabel_aff_content_id' => self::CAST_INT,
        'external_id' => self::CAST_STRING,
        'btag' => self::CAST_STRING,
        'is_deleted' => self::CAST_BOOL,
        'is_accepted' => self::CAST_BOOL,
        'is_expired' => self::CAST_BOOL,
        'is_casino' => self::CAST_BOOL,
    ];

    protected array $relations = [
    	WhitelabelAff::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO,
	];

    protected array $timezones = [];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
