<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Container;
use Exception;
use Orm\BelongsTo;
use Orm\HasMany;
use Services\Logs\FileLoggerService;

/**
 * @property int $id
 * @property int $socialTypeId
 * @property int $whitelabelId
 * @property string $appId Must be in string because int range too low
 * @property string $secret
 * @property bool $isEnabled
 *
 * @property BelongsTo|Whitelabel $whitelabel
 * @property BelongsTo|SocialType $socialType
 * @property HasMany|WhitelabelUserSocial[] $whitelabelUserSocials
 */
class WhitelabelSocialApi extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_social_api';

    protected static $_properties = [
        'id',
        'social_type_id',
        'whitelabel_id',
        'app_id',
        'secret',
        'is_enabled' => ['default' => false],
    ];

    protected $casts = [
        'id'                => self::CAST_INT,
        'social_type_id'    => self::CAST_INT,
        'whitelabel_id'     => self::CAST_INT,
        'app_id'            => self::CAST_STRING,
        'secret'            => self::CAST_STRING,
        'is_enabled'        => self::CAST_BOOL,
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        SocialType::class => self::BELONGS_TO,
        WhitelabelUserSocial::class => self::HAS_MANY,
    ];

    protected array $timezones = [];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];

    public function turnOff(): void
    {
        $this->isEnabled = false;
        try {
            $this->save();
        } catch (Exception) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error(
                'whitelabel_social_api for id ' . $this->id .
                'has not been disabled. Disable it manually, set is_enabled on false.'
            );
        }
    }
}
