<?php

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * @property int $id
 * @property string $type
 *
 * @property HasMany|WhitelabelSocialApi[] $whitelabelSocialApis
 * @method findOneByType(string $type)
 */
class SocialType extends AbstractOrmModel
{
    public const FACEBOOK_TYPE = 'facebook';
    public const GOOGLE_TYPE = 'google';

    protected static $_table_name = 'social_type';

    protected static $_properties = [
        'id',
        'type',
    ];

    protected $casts = [
        'id'   => self::CAST_INT,
        'type' => self::CAST_STRING,
    ];

    protected array $relations = [
        WhitelabelSocialApi::class => self::HAS_MANY,
    ];

    protected array $timezones = [];

    // It is very important! Do not remove these variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
