<?php

declare(strict_types=1);

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;

/**
 * @property string $authorizationCode
 * @property string $clientId
 * @property int $userId
 * @property string $redirectUri
 * @property Carbon $expires
 * @property string $scope
 * @property string $idToken
 * @property string $codeChallenge
 * @property string $codeChallengeMethod
 */
class WhitelabelOAuthAuthorizationCode extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_oauth_authorization_code';

    protected static $_properties = [
        'authorization_code',
        'client_id',
        'user_id',
        'redirect_uri',
        'expires',
        'scope',
        'id_token',
        'code_challenge',
        'code_challenge_method',
    ];

    protected $casts = [
        'authorization_code' => self::CAST_STRING,
        'client_id' => self::CAST_STRING,
        'user_id' => self::CAST_INT,
        'redirect_uri' => self::CAST_STRING,
        'expires' => self::CAST_CARBON,
        'scope' => self::CAST_STRING,
        'id_token' => self::CAST_STRING,
        'code_challenge' => self::CAST_STRING,
        'code_challenge_method' => self::CAST_STRING,
    ];

    protected array $relations = [
        /**
         * WhitelabelUser relation exists but does not work due to unexpected column name 'user_id'.
         * 'user_id' field name is required by the OAuth2 package.
         * @see class Fuel\Migrations\Create_Table_Whitelabel_Oauth_Authorization_Code
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
    protected static $_primary_key = ['authorization_code'];
    protected static $block_set_pks = false;
}
