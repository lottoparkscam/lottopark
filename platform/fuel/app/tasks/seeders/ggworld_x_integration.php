<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;


/**
 * Ggworld Integration seeder.
 */
final class Ggworld_X_Integration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Enables_Lottery_On_Development,
        \Adjusts_Next_Draw_Date;

    const LOTTERY_ID = Helpers_Lottery::GGWORLD_X_ID;
    const LOTTERY_SOURCE_ID = 27;
    const LOTTERY_TYPE_ID = 21;
    const LOTTERY_PROVIDER_ID = 31;

    /**
     * Tables disabled on production.
     *
     * @var string[]
     */
    private $disabled_tables_on_production = [
        'whitelabel_lottery'
    ];

    protected function columnsStaging(): array
    {
        return [
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'is_multidraw_enabled', 'force_currency_id'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
        ];
    }

    protected function rowsStaging(): array
    {
        [$draw_days, $draw_hour, $timezone] = ['2,5', '20:45:00', 'Europe/Paris'];
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'GG World X', 'GGWX', 'World', 'FR', 'gg-world-x', 'is_enabled' => 0, $timezone, $draw_dates_json, 0, Currency::USD, 0, 0, 0.00, '2019-06-01', 0.8, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1, 1],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'hq.gginternational.work SITE OFFICIAL', 'https://hq.gginternational.work'],
            ],
            'lottery_type' => [
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 12.97, 5, 2, 50, 12, 0, 4],
            ],
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                [self::LOTTERY_TYPE_ID, 5, 2, 0, 139838160, Model_Lottery_Type_Data::JACKPOT, 10000000, 1],
                [self::LOTTERY_TYPE_ID, 5, 1, 50000, 6991908, Model_Lottery_Type_Data::FIXED, 0, 0],
                [self::LOTTERY_TYPE_ID, 5, 0, 25000, 3107514.67, Model_Lottery_Type_Data::FIXED, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 2, 10000, 621502.93, Model_Lottery_Type_Data::FIXED, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 1, 0.0210, 31075.15, Model_Lottery_Type_Data::PARIMUTUEL, 195.75, 0],
                [self::LOTTERY_TYPE_ID, 3, 2, 0.0067, 14125.07, Model_Lottery_Type_Data::PARIMUTUEL, 28.35, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 0.0038, 13811.18, Model_Lottery_Type_Data::PARIMUTUEL, 15.7, 0],
                [self::LOTTERY_TYPE_ID, 2, 2, 0.0175, 985.47, Model_Lottery_Type_Data::PARIMUTUEL, 5.15, 0],
                [self::LOTTERY_TYPE_ID, 3, 1, 0.0185, 706.25, Model_Lottery_Type_Data::PARIMUTUEL, 3.9, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 0.0350, 313.89, Model_Lottery_Type_Data::PARIMUTUEL, 3.3, 0],
                [self::LOTTERY_TYPE_ID, 1, 2, 0.0495, 187.71, Model_Lottery_Type_Data::PARIMUTUEL, 2.75, 0],
                [self::LOTTERY_TYPE_ID, 2, 1, 0.1485, 49.27, Model_Lottery_Type_Data::PARIMUTUEL, 2.2, 0],
                [self::LOTTERY_TYPE_ID, 2, 0, 0.8, 21.90, Model_Lottery_Type_Data::FIXED, 0, 0],
            ],
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER, 1, 7, 0, '19:30:00', 'Europe/Dublin', 0, 0, 0, 0, 9999],
            ],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
