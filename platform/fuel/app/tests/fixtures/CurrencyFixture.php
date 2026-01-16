<?php

namespace Tests\Fixtures;

use Models\Currency;

final class CurrencyFixture extends AbstractFixture
{
    public const USD = 'usd';
    public const EUR = 'eur';
    public const PLN = 'pln';
    public const ABC = 'abc';
    public const ZXC = 'zxc';

    public const SUPPORTED_CODES = [
        'USD',
        'EUR',
        'GBP',
        'PLN',
        'AED',
        'AFN',
        'XAF'
    ];

    public function getDefaults(): array
    {
        return [
            'code' => $this->faker->randomElement(self::SUPPORTED_CODES),
            'rate' => $this->faker->randomFloat(1, 1, 10),
        ];
    }

    public static function getClass(): string
    {
        return Currency::class;
    }

    public function getStates(): array
    {
        return [
            self::USD => function (Currency $model, array $attributes = []) {
                $model->code = 'USD';
                $model->rate = 1.0;
            },
            self::EUR => function (Currency $model, array $attributes = []) {
                $model->code = 'EUR';
                $model->rate = 0.8418;
            },
            self::PLN => function (Currency $model, array $attributes = []) {
                $model->code = 'PLN';
                $model->rate = 3.5896;
            },
            self::ABC => function (Currency $model, array $attributes = []) {
                $model->code = 'ABC';
            },
            self::ZXC => function (Currency $model, array $attributes = []) {
                $model->code = 'ZXC';
            },
        ];
    }
}
