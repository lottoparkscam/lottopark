<?php

namespace Fuel\Tasks\Seeders;

/**
* Lottery Provider seeder.
*/
final class Lottery_Provider extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'data', 'fee']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'lottery_provider' => [
                [1, 1, 0, 1, 5, 5, '17:00:00', 'UTC', 1, '0.00', '0.00', NULL, '0.00'],
                [2, 1, 1, 1, 5, 0, '22:00:00', 'America/New_York', 0, '0.00', '0.00', NULL, '0.25'],
                [3, 1, 2, 1, 0, 0, '22:59:00', 'America/New_York', 0, '0.00', '0.00', NULL, '0.00'],
                [4, 2, 0, 1, 5, 5, '17:00:00', 'UTC', 1, '0.00', '0.00', NULL, '0.00'],
                [5, 2, 1, 1, 5, 0, '22:00:00', 'America/New_York', 0, '0.00', '0.00', NULL, '0.15'],
                [6, 2, 2, 1, 0, 0, '23:00:00', 'America/New_York', 0, '0.00', '0.00', NULL, '0.00'],
                [7, 3, 0, 1, 5, 0, '17:00:00', 'UTC', 2, '0.00', '0.00', NULL, '0.00'],
                [8, 3, 1, 1, 6, 0, '18:45:00', 'Europe/Berlin', 0, '0.00', '0.00', NULL, '0.20'],
                [9, 3, 2, 1, 0, 0, '21:00:00', 'Europe/Helsinki', 0, '0.00', '0.00', NULL, '0.00'],
                [10, 4, 1, 1, 2, 0, '19:30:00', 'Europe/Rome', 0, '12.00', '500.00', NULL, '0.15'],
                [11, 4, 2, 1, 0, 0, '20:00:00', 'Europe/Rome', 0, '0.00', '0.00', NULL, '0.00'],
                [12, 5, 0, 1, 7, 0, '09:00:00', 'UTC', 0, '0.00', '0.00', NULL, '0.00'],
                [13, 5, 1, 1, 7, 0, '19:30:00', 'Europe/London', 0, '0.00', '0.00', NULL, '0.20'],
                [14, 5, 2, 1, 0, 0, '19:30:00', 'Europe/London', 0, '0.00', '0.00', NULL, '0.00'],
                [15, 6, 0, 1, 5, 0, '19:00:00', 'UTC', 2, '0.00', '0.00', NULL, '0.00'],
                [16, 6, 1, 1, 6, 0, '18:30:00', 'Europe/Vienna', 0, '0.00', '0.00', NULL, '0.00'],
                [17, 6, 2, 1, 0, 0, '20:30:00', 'Europe/Paris', 0, '0.00', '0.00', NULL, '0.00'],
                [18, 7, 2, 1, 0, 0, '21:40:00', 'Europe/Warsaw', 0, '0.00', '0.00', NULL, '0.00'],
                [19, 7, 1, 1, 8, 0, '20:40:00', 'Europe/Warsaw', 0, '10.00', '2280.00', NULL, '0.45'],
                [20, 8, 1, 1, 8, 0, '21:00:00', 'Europe/Madrid', 0, '20.00', '2500.00', NULL, '0.00'],
                [21, 9, 1, 2, 8, 0, '21:00:00', 'Europe/Madrid', 0, '20.00', '2500.00', NULL, '0.00'],
                [22, 10, 1, 1, 10, 0, '19:30:00', 'Australia/Sydney', 0, '0.00', '0.00', NULL, '0.00'],
                [23, 11, 1, 4, 4, 4, '19:30:00', 'Australia/Sydney', 0, '0.00', '0.00', NULL, '0.00'],
                [24, 12, 1, 4, 4, 4, '19:30:00', 'Australia/Sydney', 0, '0.00', '0.00', NULL, '0.00'],
                [25, 13, 1, 4, 4, 4, '19:30:00', 'Australia/Sydney', 0, '0.00', '0.00', NULL, '0.00'],
                [26, 14, 1, 1, 6, 0, '21:00:00', 'Europe/Madrid', 0, '20.00', '2500.00', NULL, '0.00'],
                [27, 15, 1, 1, 5, 0, '20:00:00', 'Europe/Paris', 0, '0.00', '0.00', NULL, '0.00'],
            ]
        ];
    }

}