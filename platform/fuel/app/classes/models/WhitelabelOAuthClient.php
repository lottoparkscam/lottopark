<?php

declare(strict_types=1);

namespace Models;

use Carbon\Carbon;
use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $clientId
 * @property int $whitelabelId
 * @property string $name
 * @property string $domain
 * @property string $clientSecret
 * @property string $redirectUri
 * @property string $autologinUri
 * @property string $grantTypes
 * @property string $scope
 * @property Carbon $createdAt
 *
 * @property BelongsTo|Whitelabel $whitelabel
 */
class WhitelabelOAuthClient extends AbstractOrmModel
{
    public const AUTOLOGIN_URI_KEY = 'whitelabel_oauth_client_autologin_uri';

    protected static $_table_name = 'whitelabel_oauth_client';

    protected static $_properties = [
        'client_id',
        'whitelabel_id',
        'name',
        'domain',
        'client_secret',
        'redirect_uri',
        'autologin_uri',
        'grant_types',
        'scope',
        'created_at'
    ];

    protected $casts = [
        'client_id'   => self::CAST_STRING,
        'name' => self::CAST_STRING,
        'domain' => self::CAST_STRING,
        'client_secret' => self::CAST_STRING,
        'redirect_uri' => self::CAST_STRING,
        'autologin_uri' => self::CAST_STRING,
        'grant_types' => self::CAST_STRING,
        'scope' => self::CAST_STRING,
        'created_at' => self::CAST_CARBON
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
    ];

    protected array $timezones = [
        'created_at' => 'UTC'
    ];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    // Model does not use default property "id"
    protected static $_primary_key = ['client_id'];
    protected static $block_set_pks = false;
}
