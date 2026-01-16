<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;
use Helpers_Lottery;

final class UpdateGgWorldKenoTicketPrice extends Seeder
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
        DB::update('whitelabel_lottery')
            ->value('income', 0)
            ->where('lottery_id', '=', Helpers_Lottery::KENO_ID)
            ->execute();
    }
}
