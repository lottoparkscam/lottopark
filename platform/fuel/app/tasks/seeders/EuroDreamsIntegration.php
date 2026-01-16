<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class EuroDreamsIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const SLUG = Lottery::EURODREAMS_SLUG;
    const LOTTERY_ID = Helpers_Lottery::EURODREAMS_ID;
    const LOTTERY_SOURCE_ID = 53;
    const LOTTERY_TYPE_ID = 47;
    const LOTTERY_PROVIDER_ID = 57;
    const DRAW_DAYS = [1,4];
    const DRAW_TIMES = ['21:00'];
    const TICKET_PRICE = 2.5;

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
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'type', 'is_multidraw_enabled', 'force_currency_id', 'scans_enabled'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout', 'closing_times'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines', 'is_bonus_balance_in_use'],
        ];
    }


    protected function rowsStaging(): array
    {
        $timezone = 'Europe/Madrid';
        $draw_dates_json = Helpers_Time::generateMultipleDrawsPerDayJson(self::DRAW_DAYS, self::DRAW_TIMES);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [
                    'id' => self::LOTTERY_ID,
                    'source_id' => self::LOTTERY_SOURCE_ID,
                    'name' => 'EuroDreams',
                    'shortname' => 'ED',
                    'country' => 'Spain',
                    'country_iso' => 'ES',
                    'slug' => self::SLUG,
                    'is_enabled' => 0,
                    'timezone' => $timezone,
                    'draw_dates' => $draw_dates_json,
                    'draw_jackpot_set' => 0,
                    'currency_id' => Currency::EUR,
                    'last_total_prize' => 0,
                    'last_total_winners' => 0,
                    'last_jackpot_prize' => 0.00,
                    'last_update' => '2020-08-02',
                    'price' => self::TICKET_PRICE,
                    'estimated_updated' => 0,
                    'next_date_local' => $draw_dates['next_date_local'],
                    'next_date_utc' => $draw_dates['next_date_utc'],
                    'last_date_local' => $draw_dates['last_date_local'],
                    'type' => 'lottery',
                    'is_multidraw_enabled' => 1,
                    'force_currency_id' => Currency::EUR,
                    'scans_enabled' => 1,
                ],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'hq.gginternational.work SITE OFFICIAL', 'https://hq.gginternational.work'],
            ],
            'lottery_type' => [
                [
                    'id' => self::LOTTERY_TYPE_ID, 
                    'lottery_id' => self::LOTTERY_ID, 
                    'odds' => 4.66, // Odds 1 in ...
                    'ncount' => 6,
                    'bcount' => 1,
                    'nrange' => 40,
                    'brange' => 5,
                    'bextra' => 0,
                    'def_insured_tiers' => 0,
                ],
            ],
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // PARIMUTUEL: prize = prize_fund_percent, estimated = prize

                /**
                 * 'match_n' => {NORMAL_BALLS},
                 * 'match_b' => {BONUS_BALLS},
                 * 'odds' => X, // Odds 1 in ...
                 */

                // MATCH_N = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 1,
                    'prize' => 7200000,
                    'odds' => 19191900,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => true,
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 0,
                    'prize' => 120000,
                    'odds' => 4797975,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 0,
                    'prize' => 0.0213,
                    'odds' => 18815.59,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 100,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 0,
                    'prize' => 0.3424,
                    'odds' => 456.14,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 40,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 0,
                    'prize' => 0.6363,
                    'odds' => 32.07,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 5,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 2
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 0,
                    'prize' => 2.5,
                    'odds' => 5.52,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],
            ],
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 6, 0, '20:30:00', $timezone, 0, 0, 0, 0, 0, '{"1":"20:30:00", "4":"20:30:00"}'],
            ],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '2', '0', '0', '0', '1000', '1', true],
            ],
        ];
    }
}
