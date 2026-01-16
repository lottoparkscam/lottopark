<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;


/**
 * Zambia Integration seeder.
 */
final class MegaSena extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Without_Tables_On_Staging;

    const LOTTERY_ID = Helpers_Lottery::MEGASENA_ID;
    const LOTTERY_SOURCE_ID = 30;
    const LOTTERY_TYPE_ID = 24;
    const LOTTERY_PROVIDER_ID = 34;

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
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'scans_enabled'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout', 'closing_times'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
        ];
    }

    protected function rowsStaging(): array
    {
        [$draw_days, $draw_hour, $timezone] = ['3,6', '20:00:00', 'America/Sao_Paulo'];
        $closing_times = [3 => "19:00:00", 6 => "12:00:00"];
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Mega-Sena', 'MS', 'Brazil', 'BR', 'mega-sena', 1, $timezone, $draw_dates_json, 0, Currency::BRL, 0, 0, 0.00, '2019-06-01', 4.5, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'LTECH', 'LTECH API'],
            ],
            'lottery_type' => [
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 2297.56, 6, 0, 60, 0, 0, 3],
            ],
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                [self::LOTTERY_TYPE_ID, 6, 0, 0.35, 50063860, 1, 0, 1],
                [self::LOTTERY_TYPE_ID, 5, 0, 0.19, 154518.09, 1, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 0.19, 2332.35, 1, 0, 0],
            ],
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 3, 0, '20:00:00', 'America/Sao_Paulo', 0, 0, 0, 0.30, 0, json_encode($closing_times)],
            ],
            //  ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
