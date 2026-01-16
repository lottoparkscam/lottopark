<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;



/**
 * Set For Life UK seeder.
 */
final class SetForLifeUK extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Without_Tables_On_Staging;

    const LOTTERY_ID = Helpers_Lottery::SETFORLIFE_UK_ID;
    const LOTTERY_SOURCE_ID = 34;
    const LOTTERY_TYPE_ID = 28;
    const LOTTERY_PROVIDER_ID = 38;

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
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'is_multidraw_enabled'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
        ];
    }

    protected function rowsStaging(): array
    {
        [$draw_days, $draw_hour, $timezone] = ['1,4', '20:00', 'Europe/London']; //TODO: double check draw hour
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                // 'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'is_multidraw_enabled'],
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Set For Life (UK)', 'SFLUK', 'UK', 'GB', 'set-for-life-uk', 1, $timezone, $draw_dates_json, 0, Currency::GBP, 0, 0, 0.00, '2020-06-09', 1.50, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1],
            ],
            'lottery_source' => [
//                            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'LTECH API', 'LTECH API'],
            ],

            // Source: https://www.national-lottery.co.uk/games/set-for-life/about-set-for-life#What-are-the-overall-odds-of-winning-any-prize
            'lottery_type' => [
                //             'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 12.4, 5, 1, 47, 10, 0, 2],
            ],

            // Source: https://www.national-lottery.com/set-for-life/prizes
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // 1 - PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                //             'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
                [self::LOTTERY_TYPE_ID, 5, 1, 3600000, 15339390, 0, 0, 1],
                [self::LOTTERY_TYPE_ID, 5, 0, 120000, 1704377, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 1, 250, 73045, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 50, 8116, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 1, 30, 1782, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 20, 198, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 2, 1, 10, 134, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 2, 0, 5, 15, 0, 0, 0],
            ],
            //             'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier',  'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout']
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 7, 0, "19:30", 'Europe/London', 0, 0, 0, 0.20, 0],
            ],
            //  ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
