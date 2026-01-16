<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Payment_Method;

final class WonderlandPay extends Seeder
{
    private const ID = Helpers_Payment_Method::WONDERLANDPAY;

    protected function columnsStaging(): array
    {
        return [
            'payment_method' => ['id', 'name'],
            'payment_method_supported_currency' => ['payment_method_id', 'code'],
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'payment_method' => [
                [self::ID, 'WonderlandPay']
            ],
            'payment_method_supported_currency' => [
                [self::ID, 'USD'],
                [self::ID, 'EUR'],
                [self::ID, 'CNY'],
                [self::ID, 'HKD'],
                [self::ID, 'GBP'],
                [self::ID, 'JPY'],
                [self::ID, 'AUD'],
                [self::ID, 'CAD'],
                [self::ID, 'SGD'],
                [self::ID, 'DKK'],
                [self::ID, 'KRW'],
                [self::ID, 'TRL'],
                [self::ID, 'MYR'],
                [self::ID, 'THB'],
                [self::ID, 'INR'],
                [self::ID, 'PHP'],
                [self::ID, 'CHF'],
                [self::ID, 'SEK'],
                [self::ID, 'ILS'],
                [self::ID, 'ZAR'],
                [self::ID, 'RUB'],
                [self::ID, 'NOK'],
                [self::ID, 'AED'],
            ],
        ];
    }
}
