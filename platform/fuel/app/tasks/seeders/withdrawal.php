<?php

namespace Fuel\Tasks\Seeders;

/**
 * Withdrawal seeder.
 */
final class Withdrawal extends Seeder
{

    protected function columnsStaging(): array
    {
        return [
            'withdrawal' => ['id', 'name']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'withdrawal' => [
                [1, 'Bank account'],
                [2, 'Skrill'],
                [3, 'Neteller'],
                [4, 'BTC'],
                [5, 'Debit card'],
                [6, 'Paypal'],
            ]
        ];
    }
}
