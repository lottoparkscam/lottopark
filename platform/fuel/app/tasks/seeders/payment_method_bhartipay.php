<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Payment_Method;

/**
 * Description of payment_method_astro_pay_card
 */
class Payment_Method_Bhartipay extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'payment_method' => [
                'id',
                'name'
            ],
            'payment_method_supported_currency' => [
                'payment_method_id',
                'code',
                'iso_code'
            ]
        ];
    }

    protected function rowsStaging(): array
    {
        $id_of_payment = Helpers_Payment_Method::BHARTIPAY;

        return [
            'payment_method' => [
                [$id_of_payment, 'Bhartipay'],
            ],
            'payment_method_supported_currency' => [
                [$id_of_payment, 'INR', '356']
            ]
        ];
    }

    protected function columnsProduction(): array
    {
        return [
            'payment_method' => [
                'id',
                'name'
            ],
            'payment_method_supported_currency' => [
                'payment_method_id',
                'code',
                'iso_code'
            ]
        ];
    }

    protected function rowsProduction(): array
    {
        $id_of_payment = Helpers_Payment_Method::BHARTIPAY;

        return [
            'payment_method' => [
                [$id_of_payment, 'Bhartipay'],
            ],
            'payment_method_supported_currency' => [
                [$id_of_payment, 'EUR', '978'],
                [$id_of_payment, 'GBP', '826'],
                [$id_of_payment, 'INR', '356'],
                [$id_of_payment, 'USD', '840']
            ]
        ];
    }
}
