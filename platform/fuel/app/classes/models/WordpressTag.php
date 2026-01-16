<?php

namespace Models;

use Orm\BelongsTo;
use Classes\Orm\AbstractOrmModel;

/**
 * @property int $id
 * @property int $language_id
 * @property string $footer
 * @property string $privacy_policy
 * @property string $terms_and_conditions
 * @property string $country
 *
 * @property BelongsTo|Language|null $language
 */
class WordpressTag extends AbstractOrmModel
{
    protected static $_table_name = "wordpress_tag";

    protected static $_properties = [
        'id',
        'language_id',
        'footer',
        'privacy_policy',
        'terms_and_conditions',
        'country'
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'language_id' => self::CAST_INT,
    ];

    protected static array $_belongs_to = [
        'language' => [
            'key_from' => 'language_id',
            'model_to' => Language::class,
            'key_to' => 'id'
        ]
    ];
}
