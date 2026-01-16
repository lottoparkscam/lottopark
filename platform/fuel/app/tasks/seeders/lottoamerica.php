<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;



/**
 * Lotto America seeder.
 */
final class LottoAmerica extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Without_Tables_On_Staging;

    const LOTTERY_ID = Helpers_Lottery::LOTTO_AMERICA_ID;
    const LOTTERY_SOURCE_ID = 36;
    const LOTTERY_TYPE_ID = 30;
    const LOTTERY_PROVIDER_ID = 40;

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
        [$draw_days, $draw_hour, $timezone] = ['3,6', '23:00:00', 'America/New_York'];
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                // 'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'scans_enabled', 'is_multidraw_enabled'],
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Lotto America', 'LA', 'USA', 'US', 'lotto-america', 1, $timezone, $draw_dates_json, 0, Currency::USD, 0, 0, 0.00, '2020-06-05', 1.00, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1, 1],
            ],
            'lottery_source' => [
//                            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'LTECH API', 'LTECH API'],
            ],
            'lottery_type' => [
                //             'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 9.63, 5, 1, 52, 10, 0, 3], // TODO: check def_insured_tiers
            ],
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // 1 - PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                //             'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
                [self::LOTTERY_TYPE_ID, 5, 1, 0, 25989600.00, 0, 0, 1],
                [self::LOTTERY_TYPE_ID, 5, 0, 20000, 2887733.33, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 1, 1000, 110594.04, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 100, 12288.23, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 1, 20, 2404.22, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 5, 267.14, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 2, 1, 5, 160.28, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 1, 1, 2, 29.14, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 0, 1, 2, 16.94, 0, 0, 0],
            ],
            //             'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier',  'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout']
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 5, 0, "22:00", 'America/New_York', 0, 0, 0, 0.05, 0], // TODO: check tax (federal, state taxes)
            ],
            //  ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
