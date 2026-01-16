<?php

namespace Tests\Fixtures;

use Helpers_General;
use Models\Currency;
use Models\Whitelabel;
use Models\WhitelabelTransaction as Transaction;
use Models\WhitelabelUser as User;

final class WhitelabelTransactionFixture extends AbstractFixture
{
    public const CURRENCY = 'currency';
    public const WHITELABEL = 'whitelabel';
    public const USER = 'user';
    public const PAYMENT_CURRENCY = 'payment_currency';

    /** @inheritdoc */
    public function getDefaults(): array
    {
        return [
            'additional_data' => serialize([]),
            'additional_data_json' => json_encode([]),
            'amount' => 0.0,
            'amount_manager' => 0.0,
            'amount_payment' => 0.0,
            'amount_usd' => 0.0,
            'cost' => 0.0,
            'cost_manager' => 0.0,
            'cost_usd' => 0.0,
            'date' => $this->faker->dateTimeBetween('-10 days')->format('Y-m-d H:i:s'),
            'date_confirmed' => $this->faker->boolean(40) ?
                $this->faker->dateTimeBetween('-5 days')->format('Y-m-d H:i:s') : null,
            'published_at_timestamp',
            'income' => 0.0,
            'income_manager' => 0.0,
            'income_usd' => 0.0,
            'margin' => 0.0,
            'margin_manager' => 0.0,
            'margin_usd' => 0.0,
            'bonus_amount_payment' => 0.0,
            'bonus_amount_usd' => 0.0,
            'bonus_amount' => 0.0,
            'bonus_amount_manager' => 0.0,
            'payment_cost' => 0.0,
            'payment_cost_manager' => 0.0,
            'payment_cost_usd' => 0.0,
            'payment_method_type' => $this->faker->randomElement(
                [
                    Helpers_General::PAYMENT_TYPE_BALANCE,
                    Helpers_General::PAYMENT_TYPE_BONUS_BALANCE,
                    Helpers_General::PAYMENT_TYPE_CC,
                    Helpers_General::PAYMENT_TYPE_OTHER,
                ]
            ),
            'status' => $status = $this->faker->randomElement(
                [
                    Helpers_General::STATUS_TRANSACTION_APPROVED,
                    Helpers_General::STATUS_TRANSACTION_ERROR,
                    Helpers_General::STATUS_TRANSACTION_PENDING,
                ]
            ),
            'token' => $this->faker->numberBetween(1000, 999999),
            'transaction_out_id' => $status !== Helpers_General::STATUS_TRANSACTION_PENDING ?? $this->faker->uuid(),
            'type' => $this->faker->randomElement(
                [
                    Helpers_General::TYPE_TRANSACTION_DEPOSIT,
                    Helpers_General::TYPE_TRANSACTION_PURCHASE,
                ]
            ),
            'payment_attempt_date' => $this->faker->date(),
            'payment_attempts_count' => 0,
        ];
    }

    public static function getClass(): string
    {
        return Transaction::class;
    }

    /** @inheritdoc */
    public function getStates(): array
    {
        return [
            self::CURRENCY => $this->reference('currency', CurrencyFixture::class),
            self::PAYMENT_CURRENCY => $this->reference('payment_currency', CurrencyFixture::class),
            self::WHITELABEL => $this->reference('whitelabel', WhitelabelFixture::class),
            self::USER => $this->reference('user', WhitelabelUserFixture::class),
            self::BASIC => $this->basic(),
        ];
    }

    public function withWhitelabel(Whitelabel $wl): self
    {
        $this->with(function (Transaction $transaction, array $attributes = []) use ($wl) {
            $transaction->whitelabel = $wl;
        });
        return $this;
    }

    private function basic(): callable
    {
        return function (Transaction $transaction, array $attributes = []): void {
            if (empty($transaction->whitelabel)) {
                $transaction->whitelabel = $this->fixture(self::WHITELABEL)->with('basic')->makeOne();
            }

            if (empty($transaction->user)) {
                $transaction->user = $this->fixture(self::USER)->with('basic')->makeOne();
            }

            if (empty($transaction->currency)) {
                $transaction->currency = $this->fixture(self::CURRENCY)->makeOne();
            }

            if (empty($transaction->payment_currency)) {
                $samePaymentCurrency = $this->faker->boolean(80);
                $paymentCurrency = $samePaymentCurrency ? $transaction->currency : $this->fixture(
                    self::CURRENCY
                )->makeOne();
                $transaction->payment_currency = $paymentCurrency;
            }
        };
    }

    public function withUser(User $user): self
    {
        $this->with(function (Transaction $transaction, array $attributes = []) use ($user) {
            $transaction->user = $user;
        });
        return $this;
    }

    public function withCurrency(Currency $transactionCurrency, ?Currency $payment = null): self
    {
        $this->with(function (Transaction $transaction, array $attributes = [])
 use ($transactionCurrency, $payment) {
            $transaction->currency = $transactionCurrency;
            $transaction->payment_currency = $payment ?? $transaction->currency;
        });
        return $this;
    }
}
