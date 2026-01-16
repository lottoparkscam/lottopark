<?php

namespace Models;

use Orm\HasOne;
use Orm\HasMany;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $default_currency_id
 * @property string $code
 * @property string $js_currency_format
 *
 * @property HasOne|WordpressTag|null $tag
 * @property HasMany|WhitelabelUser|null $user
 */
class Language extends AbstractOrmModel
{
    protected static $_table_name = 'language';

    protected static $_properties = [
        'id',
        'default_currency_id',
        'code',
        'js_currency_format'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'default_currency_id' => self::CAST_INT
    ];

    protected static array $_has_one = [
        'tag' => [
            'key_from' => 'id',
            'model_to' => WordpressTag::class,
            'key_to' => 'language_id'
        ]
    ];

    protected array $relations = [
        WhitelabelUser::class => self::HAS_MANY
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_many = [];
}
