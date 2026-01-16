<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;

/**
 * Zambia Integration seeder.
 */
final class Zambia_Integration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Enables_Lottery_On_Development,
        \Adjusts_Next_Draw_Date;

    const LOTTERY_ID = Helpers_Lottery::ZAMBIA_ID;
    const LOTTERY_SOURCE_ID = 24;
    const LOTTERY_TYPE_ID = 18;
    const LOTTERY_PROVIDER_ID = 28;

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
        [$draw_days, $draw_hour, $timezone] = ['3,6', '21:00:00', 'Africa/Lusaka'];
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Lotto Zambia', 'LZ', 'Zambia', 'ZM', 'lotto-zambia', 'is_enabled' => 0, $timezone, $draw_dates_json, 0, Currency::ZMW, 0, 0, 0.00, '2019-06-01', 5, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'hq.gginternational.work SITE OFFICIAL', 'https://hq.gginternational.work'],
            ],
            'lottery_type' => [
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 22.17, 6, 0, 36, 0, 0, 3],
            ],
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                [self::LOTTERY_TYPE_ID, 6, 0, 0, 1947792, 0, 399768.08, 1],
                [self::LOTTERY_TYPE_ID, 5, 0, 0.0555, 10821, 1, 1501.41, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 0.0402, 298.5, 1, 30, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 5, 24, 0, 0, 0],
            ],
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER, 1, 15, 0, '20:00:00', 'Africa/Lusaka', 0, 0, 0, 0, 30000],
            ],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '0', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
