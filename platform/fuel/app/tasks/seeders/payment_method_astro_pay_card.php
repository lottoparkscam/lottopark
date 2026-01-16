<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Payment_Method;

/**
 * Description of payment_method_astro_pay_card
 */
class Payment_Method_Astro_Pay_Card extends Seeder
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
                'code'
            ]
        ];
    }

    protected function rowsStaging(): array
    {
        $id_of_payment = Helpers_Payment_Method::ASTRO_PAY_CARD;

        return [
            'payment_method' => [
                [$id_of_payment, 'AstroPayCard'],
            ],
            'payment_method_supported_currency' => [
                [$id_of_payment, 'AED'],
                [$id_of_payment, 'ARS'],
                [$id_of_payment, 'AUD'],
                [$id_of_payment, 'BOB'],
                [$id_of_payment, 'BRL'],
                [$id_of_payment, 'CAD'],
                [$id_of_payment, 'CLP'],
                [$id_of_payment, 'CNY'],
                [$id_of_payment, 'COP'],
                [$id_of_payment, 'EUR'],
                [$id_of_payment, 'GBP'],
                [$id_of_payment, 'GHS'],
                [$id_of_payment, 'IDR'],
                [$id_of_payment, 'INR'],
                [$id_of_payment, 'JPY'],
                [$id_of_payment, 'KES'],
                [$id_of_payment, 'MYR'],
                [$id_of_payment, 'MXN'],
                [$id_of_payment, 'NGN'],
                [$id_of_payment, 'PEN'],
                [$id_of_payment, 'PYG'],
                [$id_of_payment, 'RMB'],
                [$id_of_payment, 'RUB'],
                [$id_of_payment, 'SAR'],
                [$id_of_payment, 'SEK'],
                [$id_of_payment, 'THB'],
                [$id_of_payment, 'TRY'],
                [$id_of_payment, 'TWD'],
                [$id_of_payment, 'USD'],
                [$id_of_payment, 'UYU'],
                [$id_of_payment, 'VND'],
                [$id_of_payment, 'ZAR'],
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
                'code'
            ]
        ];
    }


    protected function rowsProduction(): array
    {
        $id_of_payment = Helpers_Payment_Method::ASTRO_PAY_CARD;

        return [
            'payment_method' => [
                [$id_of_payment, 'AstroPayCard'],
            ],
            'payment_method_supported_currency' => [
                [$id_of_payment, 'AED'],
                [$id_of_payment, 'ARS'],
                [$id_of_payment, 'AUD'],
                [$id_of_payment, 'BOB'],
                [$id_of_payment, 'BRL'],
                [$id_of_payment, 'CAD'],
                [$id_of_payment, 'CLP'],
                [$id_of_payment, 'CNY'],
                [$id_of_payment, 'COP'],
                [$id_of_payment, 'EUR'],
                [$id_of_payment, 'GBP'],
                [$id_of_payment, 'GHS'],
                [$id_of_payment, 'IDR'],
                [$id_of_payment, 'INR'],
                [$id_of_payment, 'JPY'],
                [$id_of_payment, 'KES'],
                [$id_of_payment, 'MYR'],
                [$id_of_payment, 'MXN'],
                [$id_of_payment, 'NGN'],
                [$id_of_payment, 'PEN'],
                [$id_of_payment, 'PYG'],
                [$id_of_payment, 'RMB'],
                [$id_of_payment, 'RUB'],
                [$id_of_payment, 'SAR'],
                [$id_of_payment, 'SEK'],
                [$id_of_payment, 'THB'],
                [$id_of_payment, 'TRY'],
                [$id_of_payment, 'TWD'],
                [$id_of_payment, 'USD'],
                [$id_of_payment, 'UYU'],
                [$id_of_payment, 'VND'],
                [$id_of_payment, 'ZAR'],
            ]
        ];
    }
}
