<?php

namespace Fuel\Tasks\Seeders;

use Models\Whitelabel;

/**
 * Faireum custom withdrawal seeder.
 */
final class FaireumCryptoexchangesWithdrawal extends Seeder
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
        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'theme' => 'faireum'
            ]
        ]);

        if (empty($whitelabel)) {
            echo "Whitelabel Faireum does not exist!";
            return [];
        }
        /** @var Whitelabel $faireum */
        $faireum = $whitelabel;

        return [
            'withdrawal' => [
                [9, 'Crypto Exchanges'],
            ],
            'whitelabel_withdrawal' => [
                [$faireum->id, 9],
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
        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'theme' => 'lottopark'
            ]
        ]);

        if (empty($whitelabel)) {
            echo "Whitelabel Lottopark does not exist!";
            return [];
        }

        return [
            'withdrawal' => [
                [9, 'Crypto Exchanges'],
            ],
        ];
    }
}
