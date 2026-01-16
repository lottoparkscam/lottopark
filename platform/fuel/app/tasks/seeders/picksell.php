<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Payment_Method;

final class Picksell extends Seeder
{
    private const ID = Helpers_Payment_Method::PICKSELL_ID;

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
                [self::ID, 'Picksell']
            ],
            'payment_method_supported_currency' => [
                [self::ID, 'EUR'],
            ],
        ];
    }
}
