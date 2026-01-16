<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;



/**
 * Lotto 6aus49 seeder.
 */
final class Lotto6Aus49 extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Without_Tables_On_Staging;

    const LOTTERY_ID = Helpers_Lottery::LOTTO_6AUS49_ID;
    const LOTTERY_SOURCE_ID = 38;
    const LOTTERY_TYPE_ID = 32;
    const LOTTERY_PROVIDER_ID = 42;

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
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers', 'additional_data'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot', 'additional_data'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout', 'closing_times'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
        ];
    }

    protected function rowsStaging(): array
    {
        // Source: https://www.lotto.de/lotto-6aus49/info/annahmeschluss
        [$draw_days, $draw_hour, $timezone] = ['3,6', '19:25:00', 'Europe/Berlin'];
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                // 'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'scans_enabled', 'is_multidraw_enabled'],
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Lotto 6aus49', '6AUS49', 'Germany', 'DE', 'lotto-6aus49', 1, $timezone, '["Wed 18:25", "Sat 19:25"]', 0, Currency::EUR, 0, 0, 0.00, '2020-06-23', 1.20, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1, 1],
            ],
            'lottery_source' => [
//                            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'LTECH API', 'LTECH API'],
            ],
            'lottery_type' => [
                //             'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 31, 6, 0, 49, 0, 0, 4, "a:3:{s:5:\"super\";i:1;s:9:\"super_min\";i:0;s:9:\"super_max\";i:9;}"], // TODO: check odds, def_insured_tiers
            ],
            // Source: https://www.lotto.de/lotto-6aus49/info/gewinnwahrscheinlichkeit
            // https://www.lotterycritic.com/lottery-results/germany-lotto/lotto-6aus49/
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // 1 - PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                //             'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
                [self::LOTTERY_TYPE_ID, 6, 0, 0.128, 139838160, 1, 0, 1, 'a:1:{s:5:"super";i:1;}'],   // TODO: check types, prizes
                [self::LOTTERY_TYPE_ID, 6, 0, 0.1, 15537573, 1, 0, 0, null],
                [self::LOTTERY_TYPE_ID, 5, 0, 0.05, 542008, 1, 0, 0, 'a:1:{s:5:"super";i:1;}'],
                [self::LOTTERY_TYPE_ID, 5, 0, 0.15, 60223, 1, 0, 0, null],
                [self::LOTTERY_TYPE_ID, 4, 0, 0.05, 10324, 1, 0, 0, 'a:1:{s:5:"super";i:1;}'],
                [self::LOTTERY_TYPE_ID, 4, 0, 0.1, 1147, 1, 0, 0, null],
                [self::LOTTERY_TYPE_ID, 3, 0, 0.1, 567, 1, 0, 0, 'a:1:{s:5:"super";i:1;}'],
                [self::LOTTERY_TYPE_ID, 3, 0, 0.45, 63, 1, 0, 0, null],
                [self::LOTTERY_TYPE_ID, 2, 0, 5, 76, 0, 0, 0, 'a:1:{s:5:"super";i:1;}'],
            ],
            // Source: https://www.lotto.de/lotto-6aus49/info/annahmeschluss
            //             'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier',  'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout', 'closing_times']
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 10, 0, "19:00", 'Europe/Berlin', 0, 0, 0, 0, 0, '{"3":"18:00:00", "6":"19:00:00"}'],  // TODO: check closing times (17:59), fee
            ],
            //  ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
