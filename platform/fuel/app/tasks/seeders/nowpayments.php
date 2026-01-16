<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Payment_Method;

final class NowPayments extends Seeder
{
    private const ID = Helpers_Payment_Method::NOWPAYMENTS_ID;

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
                [self::ID, 'NOWPayments']
            ],
            'payment_method_supported_currency' => [
                [self::ID, 'EUR'],
            ],
        ];
    }
}
