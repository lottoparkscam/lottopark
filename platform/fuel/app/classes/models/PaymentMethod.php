<?php


namespace Models;

use Fuel\Core\Inflector;
use Classes\Orm\AbstractOrmModel;
use Orm\HasMany;

/**
 * @property int $id
 * @property string $name
 *
 * @property bool $isEnabledForCasino
 * @property HasMany|PaymentLog[] $paymentLogs
 */
class PaymentMethod extends AbstractOrmModel
{
    protected static $_table_name = 'payment_method';

    protected static $_properties = [
        'id',
        'name',

        'is_enabled_for_casino'
    ];

    protected $casts = [
        'id' => self::CAST_INT,

        'is_enabled_for_casino' => self::CAST_BOOL,
    ];

    public function get_slug_attribute(): string
    {
        return Inflector::friendly_title($this->name, '-', true);
    }

    protected array $relations = [
        PaymentLog::class => self::HAS_MANY,
    ];

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
