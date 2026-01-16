<?php

namespace Fuel\Tasks\Seeders;

/**
 * Description of visanet
 */
final class VisaNet extends Seeder
{

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
                [26, 'VisaNet']
            ],
            'payment_method_supported_currency' => [
                [26, 'USD'],
                [26, 'PEN'],
            ],
        ];
    }
}
