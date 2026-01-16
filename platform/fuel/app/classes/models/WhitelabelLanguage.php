<?php
declare(strict_types=1);

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabelId
 * @property int $languageId
 * @property int $currencyId
 *
 * @property BelongsTo|Whitelabel $whitelabel
 * @property BelongsTo|Language $language
 * @property BelongsTo|Currency|null $currency
 */
class WhitelabelLanguage extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_language';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'language_id',
        'currency_id',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'language_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        Language::class => self::BELONGS_TO,
        Currency::class => self::BELONGS_TO,
    ];

    // It is very important! Do not remove this variables!
    protected static array $_has_one = [];
    protected static array $_has_many = [];
    protected static array $_belongs_to = [];
}
