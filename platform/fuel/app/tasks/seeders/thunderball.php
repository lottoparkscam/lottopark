<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;

final class Thunderball extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Without_Tables_On_Staging;

    const LOTTERY_ID = Helpers_Lottery::THUNDERBALL_ID;
    const LOTTERY_SOURCE_ID = 35;
    const LOTTERY_TYPE_ID = 29;
    const LOTTERY_PROVIDER_ID = 39;

    /**
     * Tables disabled on production.
     *
     * @var string[]
     */
    private $disabled_tables_on_production = [
        'whitelabel_lottery'
    ];

    private $disabled_tables_on_staging = [
        'whitelabel_lottery'
    ];

    protected function columnsStaging(): array
    {
        return [
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'scans_enabled', 'is_multidraw_enabled'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
        ];
    }

    protected function rowsStaging(): array
    {
        // Source: https://www.lottery.co.uk/thunderball/how-to-play 
        [$draw_days, $draw_hour, $timezone] = ['2,3,5,6', '20:15:00', 'Europe/London']; //TODO: double check draw hour
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                // 'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'scans_enabled', 'is_multidraw_enabled'],
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Thunderball', 'THB', 'UK', 'GB', 'thunderball', 1, $timezone, $draw_dates_json, 0, Currency::GBP, 0, 0, 0.00, '2020-06-08', 1, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1, 1],
            ],
            'lottery_source' => [
//                            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'LTECH API', 'LTECH API'],
            ],
            'lottery_type' => [
                //             'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 13, 5, 1, 39, 14, 0, 2],
            ],

            // Source: https://www.lottery.co.uk/thunderball/odds , https://www.national-lottery.com/thunderball/prizes 
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // 1 - PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                //             'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
                [self::LOTTERY_TYPE_ID, 5, 1, 500000, 8060598, 0, 0, 1],
                [self::LOTTERY_TYPE_ID, 5, 0, 5000, 620046, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 1, 250, 47416, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 100, 3648, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 1, 20, 1437, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 10, 111, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 2, 1, 10, 135, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 1, 1, 5, 35, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 0, 1, 3, 29, 0, 0, 0],
            ],

            // Source: https://www.national-lottery.co.uk/games/thunderball/about-thunderball
            //             'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier',  'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout']
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 5, 0, "19:30:00", 'Europe/London', 0, 0, 0, 0.15, 0],
            ],
            //  ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
