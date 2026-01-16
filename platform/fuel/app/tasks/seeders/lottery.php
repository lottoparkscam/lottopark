<?php

namespace Fuel\Tasks\Seeders;

/**
 * Lottery seeder.
 */
final class Lottery extends Seeder
{
    use \Without_Foreign_Key_Checks;

    protected function columnsStaging(): array
    {
        return [
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'current_jackpot', 'current_jackpot_usd', 'draw_jackpot_set', 'currency_id', 'last_date_local', 'next_date_local', 'next_date_utc', 'last_numbers', 'last_bnumbers', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'additional_data', 'is_multidraw_enabled', 'scans_enabled']
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'lottery' => [
                [1, 1, 'Powerball', 'PB', 'USA', 'US', 'powerball', 1, 'America/New_York', \Helpers_Time::generate_draw_days_json('3,6', '22:59:00'), '122.00000000', '122.00000000', 1, 1, null, null, null, '3,6,11,14,66', '21', '6248446.00', '473443', '0.00', '2019-06-25 16:00:02', '2.00', 1, 'N;', 1, 1],
                [2, 3, 'Mega Millions', 'MM', 'USA', 'US', 'mega-millions', 1, 'America/New_York', \Helpers_Time::generate_draw_days_json('2,5', '23:00:00'), '60.00000000', '60.00000000', 1, 1, null, null, null, '13,30,36,48,62', '18', '3538076.00', '388429', '0.00', '2019-06-25 16:00:03', '2.00', 1, 'N;', 1, 1],
                [3, 5, 'Eurojackpot', 'EJ', 'Europe', NULL, 'eurojackpot', 1, 'Europe/Helsinki', \Helpers_Time::generate_draw_days_json('5', '21:00:00'), '18.00000000', '20.33000000', 1, 2, null, null, null, '20,27,37,41,45', '1,7', '9619643.40', '630228', '0.00', '2019-06-25 16:00:04', '2.00', 1, 'N;', 1, 1],
                [4, 7, 'SuperEnalotto', 'SE', 'Italy', 'IT', 'superenalotto', 1, 'Europe/Rome', \Helpers_Time::generate_draw_days_json('2,4,6', '20:00:00'), '175.40000000', '198.10000000', 1, 2, null, null, null, '35,39,41,69,70,74', '89', '4025387.20', '468485', '0.00', '2019-06-25 16:00:04', '1.00', 1, 'N;', 0, 1],
                [5, 9, 'UK Lottery', 'UK', 'UK', 'GB', 'lotto-uk', 1, 'Europe/London', \Helpers_Time::generate_draw_days_json('3,6', '19:30:00'), '8.40000000', '10.58000000', 1, 3, null, null, null, '7,40,42,47,52,54', '29', '3855430.00', '960786', '0.00', '2019-06-17 14:03:54', '2.00', 1, 'N;', 1, 1],
                [6, 12, 'EuroMillions', 'EM', 'Europe', NULL, 'euromillions', 1, 'Europe/Brussels', \Helpers_Time::generate_draw_days_json('2,5', '20:30:00'), '45.00000000', '50.82000000', 1, 2, null, null, null, '5,8,9,25,39', '3,7', '13699878.81', '2150438', '0.00', '2019-06-25 16:00:04', '2.50', 1, 'N;', 1, 1],
                [7, 13, 'Polish Lotto', 'PL', 'Poland', 'PL', 'lotto-pl', 0, 'Europe/Warsaw', \Helpers_Time::generate_draw_days_json('2,4,6', '21:40:00'), '15.00000000', '3.98000000', 1, 4, null, null, null, '3,22,35,36,41,43', NULL, '2685129.20', '69912', '0.00', '2019-06-25 16:00:05', '3.00', 1, 'N;', 1, 0],
                [8, 16, 'La Primitiva', 'LP', 'Spain', 'ES', 'la-primitiva', 1, 'Europe/Madrid', \Helpers_Time::generate_draw_days_json('4,6', '21:30:00'), '17.20000000', '19.43000000', 1, 2, null, null, null, '1,2,3,12,18,27', '21', '5961103.26', '1435321', '0.00', '2019-06-25 16:00:05', '1.00', 1, "a:1:{s:6:\"refund\";i:1;}", 1, 1],
                [9, 17, 'BonoLoto', 'BL', 'Spain', 'ES', 'bonoloto', 1, 'Europe/Madrid', \Helpers_Time::generate_draw_days_json('1,2,3,4,5,6', '21:30:00'), '1.40000000', '1.59000000', 1, 2, null, null, null, '9,19,22,31,39,46', '7', '854026.34', '494211', '0.00', '2019-06-25 16:00:09', '0.50', 1, "a:1:{s:6:\"refund\";i:4;}", 1, 1],
                [10, 18, 'Oz Lotto', 'OZ', 'Australia', 'AU', 'oz-lotto', 1, 'Australia/Melbourne', \Helpers_Time::generate_draw_days_json('2', '20:30:00'), '2.00000000', '1.39000000', 1, 12, null, null, null, '14,19,25,29,35,36,42', '1,13', '112921781.40', '1443116', '40000000.00', '2019-06-25 16:00:15', '1.30', 1, 'N;', 1, 1],
                [11, 19, 'Powerball AU', 'PAU', 'Australia', 'AU', 'powerball-au', 1, 'Australia/Melbourne', \Helpers_Time::generate_draw_days_json('4', '20:30:00'), '40.00000000', '27.61000000', 1, 12, null, null, null, '4,10,14,16,17,19,34', '13', '11241035.40', '649655', '0.00', '2019-06-25 16:00:17', '1.20', 1, 'N;', 1, 1],
                [12, 20, 'Saturday Lotto', 'SL', 'Australia', 'AU', 'saturday-lotto-au', 1, 'Australia/Melbourne', \Helpers_Time::generate_draw_days_json('6', '20:30:00'), '1.00000000', '0.69000000', 1, 12, null, null, null, '5,10,11,12,29,41', '13,21', '13311565.79', '534152', '931789.71', '2019-06-25 16:00:20', '0.72', 1, 'N;', 1, 1],
                [13, 21, 'Mon & Wed Lotto', 'MWL', 'Australia', 'AU', 'monday-wednesday-lotto-au', 1, 'Australia/Melbourne', \Helpers_Time::generate_draw_days_json('1,3', '20:30:00'), '1.00000000', '0.69000000', 1, 12, null, null, null, '9,22,23,32,36,40', '33,37', '2310175.30', '69256', '1000000.00', '2019-06-25 16:00:22', '0.60', 1, 'N;', 1, 1],
                [14, 22, 'El Gordo', 'EGLP', 'Spain', 'ES', 'el-gordo-primitiva', 1, 'Europe/Madrid', \Helpers_Time::generate_draw_days_json('7', '21:30:00'), '9.70000000', '11.05000000', 1, 2, null, null, null, '8,19,22,30,34', NULL, '1265322.48', '415192', '0.00', '2019-06-25 16:00:10', '1.50', 0, "a:1:{s:6:\"refund\";i:9;}", 1, 1],
                [15, 23, 'French Lotto', 'FL', 'France', 'FR', 'lotto-fr', 1, 'Europe/Paris', \Helpers_Time::generate_draw_days_json('1,3,6', '20:35:00'), '7.00000000', '7.97000000', 1, 2, null, null, null, '1,16,32,33,45', '6', '1915998.80', '452691', '0.00', '2019-06-25 16:00:10', '2.20', 0, 'N;', 1, 1],
            ]
        ];
    }
}
