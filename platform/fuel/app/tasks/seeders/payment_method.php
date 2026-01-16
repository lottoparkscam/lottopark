<?php

namespace Fuel\Tasks\Seeders;

/**
* Payment Method seeder.
*/
final class Payment_Method extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'payment_method' => ['id', 'name']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'payment_method' => [
                [1, 'Test payment'],
                [2, 'Skrill'],
                [3, 'Neteller'],
                [4, 'Cubits'],
                [5, 'tpay.com'],
                [6, 'Klarna'],
                [7, 'Entercash'],
                [8, 'Piastrix'],
                [9, 'ecoPayz'],
                [10, 'paysafecard'],
                [11, 'Entropay'],
                [12, 'CoinPayments'],
                [13, 'AsiaPaymentGateway'],
                [14, 'PayPal'],
                [15, 'BitBayPay'],
                [16, 'DusuPay'],
                [17, 'EasyPaymentGateway'],
                [18, 'ApcoPay CC'],
                [19, 'Directa24'],
                [20, 'Stripe'],
            ]
        ];
    }

}