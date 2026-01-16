<?php


namespace Models;

use Carbon\Carbon;
use Helpers_Time;
use Classes\Orm\AbstractOrmModel;
use Orm\BelongsTo;
use Enums\PaymentLogType;

/**
 * @property int $id
 * @property int|null $whitelabel_id
 * @property int|null $payment_method_id
 * @property int|null $whitelabel_payment_method_id
 * @property int|null $cc_method
 * @property int|null $whitelabel_transaction_id
 * @property int $payment_method_type
 * @property Carbon $date
 * @property int $type
 * @property string $message
 * @property string $data
 * @property array $data_json - newer version of data, to store log as json instead of serialization
 *
 * @property BelongsTo|WhitelabelPaymentMethod $whitelabel_payment_method
 * @property BelongsTo|PaymentMethod $paymentMethod
 */
class PaymentLog extends AbstractOrmModel
{
    protected static $_table_name = 'payment_log';

    protected static $_properties = [
        'id',
        'whitelabel_id',
        'payment_method_id',
        'whitelabel_payment_method_id',
        'cc_method',
        'whitelabel_transaction_id',
        'payment_method_type',
        'date',
        'type',
        'message',
        'data',
        'data_json' => ['default' => '[]'],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'payment_method_id' => self::CAST_INT,
        'whitelabel_payment_method_id' => self::CAST_INT,
        'cc_method' => self::CAST_INT,
        'whitelabel_transaction_id' => self::CAST_INT,
        'payment_method_type' => self::CAST_INT,
        'date' => self::CAST_CARBON,
        'type' => self::CAST_INT,
        'data_json' => self::CAST_ARRAY,
    ];

    protected array $timezones = [
        'date' => 'UTC'
    ];

    protected array $relations = [
        WhitelabelPaymentMethod::class => self::BELONGS_TO,
        PaymentMethod::class => self::BELONGS_TO,
    ];

    public static function createFromTransaction(
        WhitelabelTransaction $transaction,
        PaymentLogType $type,
        string $message,
        array $data = []
    ): self {
        $log = new self();
        $log->whitelabel_id = $transaction->whitelabel_id;
        $log->payment_method_id = $transaction->whitelabel_payment_method->payment_method_id;
        $log->whitelabel_payment_method_id = $transaction->whitelabel_payment_method_id;
        $log->whitelabel_transaction_id = $transaction->id;
        $log->payment_method_type = $transaction->payment_method_type;
        $log->date = Carbon::now()->format(Helpers_Time::DATETIME_FORMAT);
        $log->data_json = $data;
        $log->type = (string)$type;
        $log->message = $message;

        return $log;
    }

    public static function createFromData(
        array $logData,
        PaymentLogType $type,
        string $message,
        array $data = []
    ): self {
        $log = new self($logData);
        $log->date = Carbon::now()->format(Helpers_Time::DATETIME_FORMAT);
        $log->data_json = $data;
        $log->type = (string)$type;
        $log->message = $message;

        return $log;
    }

    // It is very important! Do not remove this variables!
    protected static array $_belongs_to = [];
    protected static array $_has_one = [];
    protected static array $_has_many = [];
}
