<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

final class UpdateEurojackpotOddsTable extends Seeder
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
        DB::update('lottery_type_data')
            ->value('odds', 139838160.00)
            ->where('id', '=', 19)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 6991908.00)
            ->value('prize', 0.086)
            ->where('id', '=', 20)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 3107515.00)
            ->value('prize', 0.049)
            ->where('id', '=', 21)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 621503.00)
            ->value('prize', 0.008)
            ->where('id', '=', 22)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 31075.00)
            ->value('prize', 0.01)
            ->where('id', '=', 23)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 13811.00)
            ->value('prize', 0.008)
            ->where('id', '=', 24)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 14125.00)
            ->value('prize', 0.011)
            ->where('id', '=', 25)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 985.00)
            ->value('prize', 0.026)
            ->where('id', '=', 26)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 706.00)
            ->value('prize', 0.029)
            ->where('id', '=', 27)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 314.00)
            ->value('prize', 0.054)
            ->where('id', '=', 28)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 188.00)
            ->value('prize', 0.068)
            ->where('id', '=', 29)
            ->execute();

        DB::update('lottery_type_data')
            ->value('odds', 49.00)
            ->value('prize', 0.203)
            ->where('id', '=', 30)
            ->execute();
    }
}