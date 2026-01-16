<?php


namespace Models;

use Classes\Orm\AbstractOrmModel;
use Models\Whitelabel;
use Orm\BelongsTo;
use Orm\HasMany;

/**
 * @property int $id
 * @property int $whitelabel_id
 * @property int $payment_method_id
 * @property int $language_id
 * @property string $name
 * @property bool $show
 * @property string $data
 * @property array|null $data_json - new approach (as json in db) for old data (serialized one)
 * @property int $order
 * @property float $cost_percent
 * @property float $cost_fixed
 * @property int $cost_currency_id
 * @property int $payment_currency_id
 * @property boolean $show_payment_logotype
 * @property string $custom_logotype
 * @property boolean $only_deposit
 * @property boolean $allowUserToSelectCurrency
 *
 * @property BelongsTo|PaymentMethod $payment_method
 * @property BelongsTo|Whitelabel $whitelabel
 * @property HasMany|WhitelabelPaymentMethodCurrency[] $whitelabel_payment_method_currency
 *
 * @property HasMany|PaymentLog $payment_logs
 */
class WhitelabelPaymentMethod extends AbstractOrmModel
{
    protected static $_table_name = 'whitelabel_payment_method';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'payment_method_id',
        'language_id',
        'name',
        'show',
        'data' => ['default' => 'a:0:{}'],
        'data_json',
        'order' => ['default' => 0],
        'cost_percent' => ['default' => 0.0],
        'cost_fixed',
        'cost_currency_id',
        'payment_currency_id',
        'show_payment_logotype' => ['default' => true],
        'custom_logotype',
        'only_deposit' => ['default' => false],
        'allow_user_to_select_currency' => ['default' => false],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'payment_method_id' => self::CAST_INT,
        'language_id' => self::CAST_INT,
        'show' => self::CAST_BOOL,
        'cost_percent' => self::CAST_FLOAT,
        'cost_fixed' => self::CAST_FLOAT,
        'cost_currency_id' => self::CAST_INT,
        'payment_currency_id' => self::CAST_INT,
        'show_payment_logotype' => self::CAST_BOOL,
        'only_deposit' => self::CAST_BOOL,
        'allow_user_to_select_currency' => self::CAST_BOOL,
        'data_json' => self::CAST_ARRAY,
        'order' => self::CAST_INT
    ];

    protected array $relations = [
        PaymentLog::class => self::HAS_MANY,
        PaymentMethod::class => self::BELONGS_TO,
        Whitelabel::class => self::BELONGS_TO,
        WhitelabelPaymentMethodCurrency::class => self::HAS_MANY
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [
        'whitelabel_payment_method_currency' => [
            'key_from' => 'id',
            'model_to' => WhitelabelPaymentMethodCurrency::class,
            'key_to' => 'whitelabel_payment_method_id',
        ],
    ];
}
