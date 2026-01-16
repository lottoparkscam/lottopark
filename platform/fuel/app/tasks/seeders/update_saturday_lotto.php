<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;
use Lotto_Lotteries_Ids;

final class Update_Saturday_Lotto extends Seeder
{

    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        return [];
    }

    public function execute(): void
    {
        DB::update('lottery')
            ->set([
                'price' => DB::expr('price + 0.10')
            ])
            ->where('id', '=', 12)
            ->execute();

        DB::update('lottery_type_data')
        ->set([
            'match_n' => 3,
            'match_b' => 0,
        ])
        ->where('lottery_type_id', '=', DB::expr('(select id from lottery_type where lottery_id = ' . Lotto_Lotteries_Ids::SATURDAY_LOTTO_AU . ')'))
        ->where('match_n', '=', 1)
        ->where('match_b', '=', 2)
        ->execute();
    }
}
