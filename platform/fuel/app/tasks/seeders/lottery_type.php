<?php

namespace Fuel\Tasks\Seeders;

/**
* Lottery Type seeder.
*/
final class Lottery_Type extends Seeder
{
    protected function columnsStaging(): array
    {
        return [
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'date_start', 'def_insured_tiers', 'additional_data']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'lottery_type' => [
                [1, 1, '24.87', 5, 1, 69, 26, 0, NULL, 3, NULL],
                [2, 2, '14.49', 5, 1, 75, 15, 0, NULL, 3, NULL],
                [3, 3, '26.39', 5, 2, 50, 10, 0, NULL, 4, NULL],
                [4, 4, '20.58', 6, 0, 90, 0, 1, NULL, 3, NULL],
                [5, 5, '9.27', 6, 0, 59, 0, 1, NULL, 2, NULL],
                [6, 6, '12.88', 5, 2, 50, 11, 0, NULL, 4, NULL],
                [7, 7, '53.96', 6, 0, 49, 0, 0, NULL, 1, NULL],
                [8, 2, '24.12', 5, 1, 70, 25, 0, '2017-10-31', 3, NULL],
                [9, 6, '12.98', 5, 2, 50, 12, 0, '2016-09-24', 4, NULL],
                [10, 8, '8.43', 6, 0, 49, 0, 1, NULL, 3, "a:3:{s:6:\"refund\";i:1;s:10:\"refund_min\";i:0;s:10:\"refund_max\";i:9;}"],
                [11, 9, '8.43', 6, 0, 49, 0, 1, NULL, 4, "a:3:{s:6:\"refund\";i:1;s:10:\"refund_min\";i:0;s:10:\"refund_max\";i:9;}"],
                [12, 10, '54.59', 7, 0, 45, 0, 2, NULL, 3, NULL],
                [13, 11, '44.38', 7, 1, 35, 20, 0, NULL, 3, NULL],
                [14, 12, '85.44', 6, 0, 45, 0, 2, NULL, 3, NULL],
                [15, 13, '85.44', 6, 0, 45, 0, 2, NULL, 2, NULL],
                [16, 14, '6.16', 5, 0, 54, 0, 0, NULL, 4, "a:4:{s:6:\"refund\";i:1;s:17:\"refund_selectable\";i:1;s:10:\"refund_min\";i:0;s:10:\"refund_max\";i:9;}"],
                [17, 15, '5.99', 5, 1, 49, 10, 0, '2008-10-06', 2, NULL],
            ]
        ];
    }

}