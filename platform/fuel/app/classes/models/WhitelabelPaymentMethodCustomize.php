<?php

declare(strict_types=1);

namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabel_payment_method_id
 * @property int $whitelabel_language_id
 * @property string $title
 * @property string $title_for_mobile
 * @property string $title_in_description
 * @property string $description
 * @property string $additional_failure_text
 * @property string $additional_success_text
 *
 * @property BelongsTo|WhitelabelPaymentMethod $whitelabel_payment_method
 */
class WhitelabelPaymentMethodCustomize extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_payment_method_customize';

    protected static $_properties = [
        'id',
        'whitelabel_payment_method_id',
        'whitelabel_language_id',
        'title',
        'title_for_mobile',
        'title_in_description',
        'description',
        'additional_failure_text',
        'additional_success_text',
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_payment_method_id' => self::CAST_INT,
        'whitelabel_language_id' => self::CAST_INT,
        'title' => self::CAST_STRING,
        'title_for_mobile' => self::CAST_STRING,
        'description' => self::CAST_STRING,
        'additional_failure_text' => self::CAST_STRING,
        'additional_success_text' => self::CAST_STRING,
    ];

    protected array $relations = [
        WhitelabelPaymentMethod::class => self::BELONGS_TO,
        WhitelabelLanguage::class => self::BELONGS_TO
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
