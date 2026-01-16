<?php

namespace Fuel\Tasks\Seeders;

use Model_Whitelabel;

/**
 * Faireum custom withdrawal seeder.
 */
final class Faireum_Custom_Withdrawal extends Seeder
{
    protected function columnsProduction(): array
    {
        return [
            'withdrawal' => ['id', 'name'],
            'whitelabel_withdrawal' => ['whitelabel_id', 'withdrawal_id']
        ];
    }

    protected function rowsProduction(): array
    {
        $whitelabel_model = Model_Whitelabel::find_by(['theme' => 'faireum']);
        if (empty($whitelabel_model[0])) {
            echo "Whitelabel Faireum does not exist!";
            return [];
        }
        $faireum = $whitelabel_model[0];

        return [
            'withdrawal' => [
                [7, 'Membership'],
                [8, 'Tether (USDT)']
            ],
            'whitelabel_withdrawal' => [
                [$faireum->id, 7],
                [$faireum->id, 8]
            ]
        ];
    }

    protected function columnsStaging(): array
    {
        return [
            'withdrawal' => ['id', 'name'],
        ];
    }

    protected function rowsStaging(): array
    {
        $whitelabel_model = Model_Whitelabel::find_by(['theme' => 'lottopark']);

        if (empty($whitelabel_model[0])) {
            echo "Whitelabel Lottopark does not exist!";
            return [];
        }
        $lottopark = $whitelabel_model[0];

        return [
            'withdrawal' => [
                [7, 'Membership'],
                [8, 'Tether (USDT)']
            ],
        ];
    }
}
