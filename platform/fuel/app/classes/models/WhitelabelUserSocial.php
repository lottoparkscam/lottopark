<?php

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelSocialApiId
 * @property int $whitelabelUserId
 * @property string $socialUserId Must be in string because int range to low
 * @property boolean $isConfirmed
 * @property string|Null $activationHash We send hash to the user as you need to add a login via social media
 * @property Carbon|Null $lastHashSentAt
 *
 * @property BelongsTo|WhitelabelUser $whitelabelUser
 * @property BelongsTo|WhitelabelSocialApi $whitelabelSocialApi
 */
class WhitelabelUserSocial extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_user_social';

    protected static $_properties = [
        'id',
        'whitelabel_social_api_id',
        'is_confirmed' => ['default' => false],
        'whitelabel_user_id',
        'social_user_id',
        'activation_hash',
        'last_hash_sent_at',
    ];

    protected $casts = [
        'id'                            => self::CAST_INT,
        'whitelabel_social_api_id'      => self::CAST_INT,
        'is_confirmed'                  => self::CAST_BOOL,
        'whitelabel_user_id'            => self::CAST_INT,
        'social_user_id'                => self::CAST_STRING,
        'activation_hash'               => self::CAST_STRING,
        'last_hash_sent_at'                => self::CAST_CARBON,
    ];

    protected array $relations = [
        WhitelabelSocialApi::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
        'last_hash_sent_at' => 'UTC'
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    /**
     * WhitelabelUserSocial isConfirmed after register is true
     * When User try login to existed account isConfirm equals false
     */
    public function isConfirmedByEmail(): bool
    {
        return $this->isConfirmed;
    }

    public function isUserNotConfirmedByEmail(): bool
    {
        return !$this->isConfirmedByEmail();
    }
}
