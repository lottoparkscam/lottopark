<?php


namespace Models;

use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;

/**
 * @property int $id
 * @property int $whitelabel_payment_method_id
 * @property int $currency_id
 * @property bool $is_zero_decimal
 * @property float $min_purchase
 * @property bool $is_default
 * @property bool $is_enabled
 *
 * @property BelongsTo|WhitelabelPaymentMethod $whitelabelPaymentMethod
 * @property BelongsTo|Currency $currency
 */
class WhitelabelPaymentMethodCurrency extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_payment_method_currency';

    protected static $_properties = [
        'id',
        'whitelabel_payment_method_id',
        'currency_id',
        'is_zero_decimal' => ['default' => true],
        'min_purchase' => ['default' => 0.0],
        'is_default' => ['default' => true],
        'is_enabled' => ['default' => true],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_payment_method_id' => self::CAST_INT,
        'currency_id' => self::CAST_INT,
        'is_zero_decimal' => self::CAST_BOOL,
        'min_purchase' => self::CAST_FLOAT,
        'is_default' => self::CAST_BOOL,
        'is_enabled' => self::CAST_BOOL,
    ];

    protected array $relations = [
        WhitelabelPaymentMethod::class => self::BELONGS_TO,
        Currency::class => self::BELONGS_TO
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
