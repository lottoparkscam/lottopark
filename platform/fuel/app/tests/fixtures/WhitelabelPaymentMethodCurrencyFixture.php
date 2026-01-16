<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Models\WhitelabelPaymentMethod;
use Models\WhitelabelPaymentMethodCurrency;
use Models\Currency;
use Helpers\CurrencyHelper;

final class WhitelabelPaymentMethodCurrencyFixture extends AbstractFixture
{
    public function getDefaults(): array
    {
        return [
            'currency_id' => null,
            'is_zero_decimal' => true,
            'min_purchase' => 0.00,
            'is_default' => true,
            'is_enabled' => true,
        ];
    }

    public static function getClass(): string
    {
        return WhitelabelPaymentMethodCurrency::class;
    }

    public function withWhitelabelPaymentMethod(WhitelabelPaymentMethod $whitelabelPaymentMethod): self
    {
        $this->with(function (WhitelabelPaymentMethodCurrency $whitelabelPaymentMethodCurrency) use ($whitelabelPaymentMethod) {
            $whitelabelPaymentMethodCurrency->whitelabelPaymentMethod = $whitelabelPaymentMethod;
        });

        return $this;
    }

    public function withCurrency(Currency $currency): self
    {
        $this->with(function (WhitelabelPaymentMethodCurrency $whitelabelPaymentMethodCurrency) use ($currency) {
            $whitelabelPaymentMethodCurrency->currency = $currency;
        });

        return $this;
    }

    public function createOneForWhitelabelPaymentMethod(
        WhitelabelPaymentMethod $whitelabelPaymentMethod,
        string $currencyCode,
        bool $enabled = true,
        bool $default = false
    ): WhitelabelPaymentMethodCurrency {
        $currency = CurrencyHelper::getCurrencyByCode($currencyCode);

        /** @var WhitelabelPaymentMethodCurrency $whitelabelPaymentMethodCurrency */
        $whitelabelPaymentMethodCurrency = $this->withWhitelabelPaymentMethod($whitelabelPaymentMethod)
            ->withCurrency($currency)
            ->createOne([
                'is_enabled' => $enabled,
                'is_default' => $default
            ]);

        return $whitelabelPaymentMethodCurrency;
    }
}
