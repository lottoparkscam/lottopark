<?php

namespace Models;

use Exception;
use Orm\HasOne;
use Orm\HasMany;
use Orm\BelongsTo;
use Fuel\Core\Date;
use Classes\Orm\AbstractOrmModel;
use Modules\Payments\CustomOptionsAwareContract;
use Helpers_General;

/**
 * @property int $id
 * @property string|null $additional_data
 * @property array $additional_data_json
 * @property float|null $amount
 * @property float|null $amount_manager
 * @property float|null $amount_payment
 * @property float|null $amount_usd
 * @property float|null $cost
 * @property float|null $cost_manager
 * @property float|null $cost_usd
 * @property int $currency_id
 * @property Date $date
 * @property Date|null $date_confirmed
 * @property string $published_at_timestamp
 * @property float|null $income
 * @property float|null $income_manager
 * @property float|null $income_usd
 * @property float|null $margin
 * @property float|null $margin_manager
 * @property float|null $margin_usd
 * @property float $bonus_amount_payment
 * @property float $bonus_amount_usd
 * @property float $bonus_amount
 * @property float $bonus_amount_manager
 * @property float|null $payment_cost
 * @property float|null $payment_cost_manager
 * @property float|null $payment_cost_usd
 * @property int $payment_currency_id
 * @property int|null $payment_method_type
 * @property int $status
 * @property int $token
 * @property string|null $transaction_out_id
 * @property int $type
 * @property int|null $whitelabel_cc_method_id
 * @property int $whitelabel_id
 * @property int|null $whitelabel_payment_method_id
 * @property int $whitelabel_user_id
 *
 * @property Date|null $payment_attempt_date
 *
 * @property string $payment_method_slug
 * @property string $prefixed_token
 * @property bool $is_deposit_type
 * @property bool $is_purchase_type
 * @property bool $is_approved
 * @property bool $isCasino
 * @property int $payment_attempts_count
 *
 * @property BelongsTo|Currency $currency
 * @property BelongsTo|Currency $payment_currency
 * @property BelongsTo|WhitelabelUser $user
 * @property BelongsTo|Whitelabel $whitelabel
 * @property BelongsTo|WhitelabelPaymentMethod $whitelabel_payment_method
 * @property BelongsTo|WhitelabelUser $whitelabel_user
 * @property HasMany|WhitelabelUserTicket[]|null $whitelabel_tickets
 * @property HasMany|SynchronizerLog[] $synchronizerLogs
 * @property HasOne|WhitelabelRaffleTicket|null $whitelabel_raffle_ticket
 * @property HasOne|CleanerLog $cleanerLog
 * @property HasOne|WhitelabelAffCommission $whitelabelAffCommission
 */
class WhitelabelTransaction extends AbstractOrmModel implements CustomOptionsAwareContract
{
    protected static $_table_name = 'whitelabel_transaction';

    public const TYPE_PURCHASE = 0;
    public const TYPE_DEPOSIT = 1;

    protected static $_properties = [
        'id',
        'additional_data',
        'additional_data_json' => ['default' => '[]'],
        'amount' => ['default' => 0.00],
        'amount_manager' => ['default' => 0.00],
        'amount_payment' => ['default' => 0.00],
        'amount_usd' => ['default' => 0.00],
        'cost' => ['default' => 0.00],
        'cost_manager' => ['default' => 0.00],
        'cost_usd' => ['default' => 0.00],
        'currency_id',
        'date',
        'date_confirmed',
        'published_at_timestamp',
        'income',
        'income_manager',
        'income_usd',
        'margin',
        'margin_manager',
        'margin_usd',
        'bonus_amount_payment' => ['default' => 0.00],
        'bonus_amount_usd' => ['default' => 0.00],
        'bonus_amount' => ['default' => 0.00],
        'bonus_amount_manager' => ['default' => 0.00],
        'payment_cost' => ['default' => 0.00],
        'payment_cost_manager' => ['default' => 0.00],
        'payment_cost_usd' => ['default' => 0.00],
        'is_casino' => ['default' => false],
        'payment_currency_id',
        'payment_method_type',
        'status',
        'token',
        'transaction_out_id',
        'type',
        'whitelabel_cc_method_id',
        'whitelabel_id',
        'whitelabel_payment_method_id',
        'whitelabel_user_id',
        'payment_attempt_date',
        'payment_attempts_count' => ['default' => 0],
    ];

    protected $casts = [
        'id' => self::CAST_INT,
        'amount' => self::CAST_FLOAT,
        'additional_data_json' =>  self::CAST_ARRAY,
        'amount_manager' => self::CAST_FLOAT,
        'amount_payment' => self::CAST_FLOAT,
        'amount_usd' => self::CAST_FLOAT,
        'cost' => self::CAST_FLOAT,
        'cost_manager' => self::CAST_FLOAT,
        'cost_usd' => self::CAST_FLOAT,
        'currency_id' => self::CAST_INT,
        'date' => self::CAST_DATETIME,
        'date_confirmed' => self::CAST_DATETIME,
        'published_at_timestamp' => self::CAST_STRING,
        'income' => self::CAST_FLOAT,
        'income_manager' => self::CAST_FLOAT,
        'income_usd' => self::CAST_FLOAT,
        'margin' => self::CAST_FLOAT,
        'margin_manager' => self::CAST_FLOAT,
        'margin_usd' => self::CAST_FLOAT,
        'bonus_amount' => self::CAST_FLOAT,
        'bonus_amount_manager' => self::CAST_FLOAT,
        'bonus_amount_payment' => self::CAST_FLOAT,
        'bonus_amount_usd' => self::CAST_FLOAT,
        'payment_cost' => self::CAST_FLOAT,
        'payment_cost_manager' => self::CAST_FLOAT,
        'payment_cost_usd' => self::CAST_FLOAT,
        'payment_currency_id' => self::CAST_INT,
        'payment_method_type' => self::CAST_INT,
        'status' => self::CAST_INT,
        'type' => self::CAST_INT,
        'whitelabel_cc_method_id' => self::CAST_INT,
        'whitelabel_id' => self::CAST_INT,
        'whitelabel_payment_method_id' => self::CAST_INT,
        'whitelabel_user_id' => self::CAST_INT,
        'payment_attempt_date' => self::CAST_DATETIME,
        'payment_attempts_count' => self::CAST_INT,
        'is_casino' => self::CAST_BOOL,
    ];

    protected static array $_belongs_to = [
        'currency' => [
            'key_from' => 'currency_id',
            'model_to' => Currency::class,
            'key_to' => 'id',
        ],
        'payment_currency' => [
            'key_from' => 'payment_currency_id',
            'model_to' => Currency::class,
            'key_to' => 'id',
        ],
        'user' => [
            'key_from' => 'whitelabel_user_id',
            'model_to' => WhitelabelUser::class,
            'key_to' => 'id',
        ],
        'whitelabel_payment_method' => [
            'key_from' => 'whitelabel_payment_method_id',
            'model_to' => WhitelabelPaymentMethod::class,
            'key_to' => 'id',
        ],
    ];

    protected static array $_has_one = [
        'whitelabel_raffle_ticket' => [
            'key_from' => 'id',
            'model_to' => WhitelabelRaffleTicket::class,
            'key_to' => 'whitelabel_transaction_id'
        ],
    ];

    protected static array $_has_many = [
        'whitelabel_tickets' => [
            'key_from' => 'id',
            'model_to' => WhitelabelUserTicket::class,
            'key_to' => 'whitelabel_transaction_id'
        ],
    ];

    protected array $relations = [
        Whitelabel::class => self::BELONGS_TO,
        WhitelabelUser::class => self::BELONGS_TO,
        CleanerLog::class => self::HAS_ONE,
        SynchronizerLog::class => self::HAS_MANY,
    ];

    public function pay(): void
    {
        if ($this->status == Helpers_General::STATUS_TRANSACTION_APPROVED) {
            throw new Exception('Attempted to pay already approved transaction');
        }

        $this->status = Helpers_General::STATUS_TRANSACTION_APPROVED;
        $this->date_confirmed = Date::forge()->format('mysql');
        $this->attemptPayment();

        if (is_array($this->whitelabel_tickets)) {
            foreach ($this->whitelabel_tickets as $ticket) {
                $ticket->paid = true;
            }
        }
    }

    public function setStatusAsErrorWithTicket(): void
    {
        if ($this->status == Helpers_General::STATUS_TRANSACTION_APPROVED) {
            throw new Exception('Attempted to fail already approved transaction');
        }

        $this->status = Helpers_General::STATUS_TRANSACTION_ERROR;
        $this->attemptPayment();

        if (is_array($this->whitelabel_tickets)) {
            foreach ($this->whitelabel_tickets as $ticket) {
                $ticket->paid = false;
            }
        }
    }

    public function get_payment_method_slug_attribute(): ?string
    {
        if (empty($this->whitelabel_payment_method->payment_method)) {
            return null;
        }
        return $this->whitelabel_payment_method->payment_method->slug;
    }

    public function get_prefixed_token_attribute(): string
    {
        $wlPrefix = '';
        if ($this->whitelabel) {
            $wlPrefix = $this->whitelabel->prefix;
        }
        $tokenPrefix = $this->type == Helpers_General::TYPE_TRANSACTION_PURCHASE ? 'P' : 'D';
        $token = $this->token;

        return sprintf('%s%s%s', $wlPrefix, $tokenPrefix, $token);
    }

    public function get_is_deposit_type_attribute(): bool
    {
        return $this->type == Helpers_General::TYPE_TRANSACTION_DEPOSIT;
    }

    public function get_is_purchase_type_attribute(): bool
    {
        return $this->type == Helpers_General::TYPE_TRANSACTION_PURCHASE;
    }

    public function get_is_approved_attribute(): bool
    {
        return $this->status == Helpers_General::STATUS_TRANSACTION_APPROVED;
    }

    public function attemptPayment(): void
    {
        $this->payment_attempt_date = Date::forge()->format('mysql');
        $this->payment_attempts_count++;
    }

    public function getOptions(): array
    {
        return $this->whitelabel_payment_method->data_json;
    }

    public function getOrderId(): string
    {
        return $this->prefixed_token;
    }

    public function setAdditionalData(string $field, $value)
    {
        $this->additional_data_json = array_merge(
            $this->additional_data_json ?: [],
            [$field => $value]
        );
    }

    public function getAdditionalData(): array
    {
        return $this->additional_data_json ?: [];
    }
}
