<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;
use Lotto_Lotteries_Ids;
use Lotto_Lotteries_OzLotto;

final class UpdateOzLottoRules extends Seeder
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
        // Numbers to select
        DB::update('lottery_type')
            ->value('nrange', 47)
            ->value('bextra', Lotto_Lotteries_OzLotto::EXTRA_NUMBERS_COUNT)
            ->value('odds', 51) // overall lottery odds
            ->where('lottery_id', '=', Lotto_Lotteries_Ids::OZ_LOTTO)
            ->execute();

        // Price per ticket
        DB::update('lottery')
            ->value('price', 1.40)
            ->where('id', '=', Lotto_Lotteries_Ids::OZ_LOTTO)
            ->execute();

        // Odds table
        DB::update('lottery_type_data')
            ->value('odds', 62891499.00)
            ->where('id', '=', 95)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 2994833.00)
            ->where('id', '=', 96)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 242824.00)
            ->where('id', '=', 97)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 26270.00)
            ->where('id', '=', 98)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 4497.00)
            ->where('id', '=', 99)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 182.00)
            ->where('id', '=', 100)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 71.00)
            ->where('id', '=', 101)
            ->execute();
    }
}
