<?php

declare(strict_types=1);

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;

/**
 * @property string $accessToken
 * @property string $clientId
 * @property int $userId
 * @property Carbon $expires
 * @property string $scope
 */
class WhitelabelOAuthAccessToken extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_oauth_access_token';

    protected static $_properties = [
        'access_token',
        'client_id',
        'user_id',
        'expires',
        'scope'
    ];

    protected $casts = [
        'access_token' => self::CAST_STRING,
        'client_id' => self::CAST_STRING,
        'user_id' => self::CAST_INT,
        'expires' => self::CAST_CARBON,
        'scope' => self::CAST_STRING,
    ];

    protected array $relations = [
        /**
         * WhitelabelUser relation exists but does not work due to unexpected column name 'user_id'.
         * 'user_id' field name is required by the OAuth2 package.
         * @see class Fuel\Migrations\Create_Table_Whitelabel_Oauth_Access_Token
         *
         * WhitelabelUser::class => self::BELONGS_TO,
         */
    ];

    protected array $timezones = [
        'expires' => 'UTC'
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    // Model does not use default property "id"
    protected static $_primary_key = ['access_token'];
    protected static $block_set_pks = false;
}
