<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;

/**
 * Zambia Integration seeder.
 *
 */
final class Florida_Lotto extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production;

    const LOTTERY_ID = Helpers_Lottery::FLORIDA_LOTTO_ID;
    const LOTTERY_SOURCE_ID = 29;
    const LOTTERY_TYPE_ID = 23;
    const LOTTERY_PROVIDER_ID = 33;

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
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout']
        ];
    }

    protected function rowsStaging(): array
    {
        [$draw_days, $draw_hour, $timezone] = ['3,6', '23:15:00', 'America/New_York'];
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Florida Lotto', 'FLL', 'USA', 'US', 'florida-lotto', 1, $timezone, $draw_dates_json, 0, Currency::USD, 0, 0, 0.00, '2019-06-01', 1, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local']],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'hq.gginternational.work SITE OFFICIAL', 'https://hq.gginternational.work'],
            ],
            'lottery_type' => [
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 67.36, 6, 0, 53, 0, 0, 1],
            ],
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                [self::LOTTERY_TYPE_ID, 6, 0, 0, 22957480, 0, 0, 1],
                [self::LOTTERY_TYPE_ID, 5, 0, 500, 81409.5, 0, 5000, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 70, 1415.82, 0, 70, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 5, 70.79, 0, 5, 0],
            ],
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 10, 0, '22:40:00', 'America/New_York', 0, 0, 0, 0.05, 30000],
            ]
        ];
    }
}
