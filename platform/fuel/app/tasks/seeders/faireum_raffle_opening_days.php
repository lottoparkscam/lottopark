<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Core\DB;

include 'faireum_raffle_closed.php';

final class Faireum_Raffle_Opening_Days
{
    public function execute(): void
    {
        $id = Faireum_Raffle_Closed::RAFFLE_ID;
        DB::query(/** @lang MySql */'UPDATE raffle SET is_sell_enabled = true, is_sell_limitation_enabled = true, sell_open_dates = \'["Mon 23:59", "Tue 23:59", "Wed 23:59", "Thu 23:59", "Fri 23:59", "Sat 23:59", "Sun 23:59"]\' WHERE id = :id;')->bind('id', $id)->execute();
    }
}
