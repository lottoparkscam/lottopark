<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class MiniPowerballIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const LOTTERY_SLUG = Lottery::MINI_POWERBALL_SLUG;
    const LOTTERY_NAME = 'Mini Powerball';
    const LOTTERY_SHORTNAME = 'MPB';
    const LOTTERY_ID = Helpers_Lottery::MINI_POWERBALL_ID;
    const LOTTERY_SOURCE_ID = 64;
    const LOTTERY_TYPE_ID = 58;
    const LOTTERY_PROVIDER_ID = 68;
    const COUNTRY_NAME = 'USA';
    const COUNTRY_ISO = 'US';
    const TIMEZONE = 'America/New_York';
    const TICKET_PRICE = 0.2;
    const CURRENCY = Currency::USD;
    const NUMBERS_POOL = 69;
    const NUMBERS_DRAWN = 5;
    const BONUS_NUMBERS_POOL = 26;
    const BONUS_NUMBERS_DRAWN = 1;
    const EXTRA_NUMBERS_COUNT = 0;
    const ODDS = 24.87;
    const DEF_INSURED_TIERS = 3;
    const SCANS_ENABLED = 1;
    const IS_BONUS_BALANCE_IN_USE = 1;
    const DRAW_DAYS = [Carbon::MONDAY, Carbon::WEDNESDAY, Carbon::SATURDAY];
    const DRAW_TIMES = ['22:59'];

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
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines','quick_pick_lines', 'is_bonus_balance_in_use'],
        ];
    }


    protected function rowsStaging(): array
    {
        $draw_dates_json = Helpers_Time::generateMultipleDrawsPerDayJson(self::DRAW_DAYS, self::DRAW_TIMES);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, self::TIMEZONE);

        return [
            'lottery' => [
                [
                    'id' => self::LOTTERY_ID,
                    'source_id' => self::LOTTERY_SOURCE_ID,
                    'name' => self::LOTTERY_NAME,
                    'shortname' => self::LOTTERY_SHORTNAME,
                    'country' => self::COUNTRY_NAME,
                    'country_iso' => self::COUNTRY_ISO,
                    'slug' => self::LOTTERY_SLUG,
                    'is_enabled' => 0,
                    'timezone' => self::TIMEZONE,
                    'draw_dates' => $draw_dates_json,
                    'draw_jackpot_set' => 1,
                    'currency_id' => self::CURRENCY,
                    'last_total_prize' => 0,
                    'last_total_winners' => 0,
                    'last_jackpot_prize' => 0.00,
                    'last_update' => '2020-08-02',
                    'price' => self::TICKET_PRICE,
                    'estimated_updated' => 1,
                    'next_date_local' => $draw_dates['next_date_local'],
                    'next_date_utc' => $draw_dates['next_date_utc'],
                    'last_date_local' => $draw_dates['last_date_local'],
                    'type' => 'lottery',
                    'is_multidraw_enabled' => 1,
                    'force_currency_id' => self::CURRENCY,
                    'scans_enabled' => self::SCANS_ENABLED,
                ],
            ],
            'lottery_source' => [
                [
                    'id' => self::LOTTERY_SOURCE_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'name' => 'powerball.com SITE',
                    'website' => 'http://www.powerball.com',
                ],
            ],
            'lottery_type' => [
                [
                    'id' => self::LOTTERY_TYPE_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'odds' => self::ODDS,
                    'ncount' => self::NUMBERS_DRAWN,
                    'bcount' => self::BONUS_NUMBERS_DRAWN,
                    'nrange' => self::NUMBERS_POOL,
                    'brange' => self::BONUS_NUMBERS_POOL,
                    'bextra' => self::EXTRA_NUMBERS_COUNT,
                    'def_insured_tiers' => self::DEF_INSURED_TIERS,
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

                // MATCH_N = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 1,
                    'prize' => 0.762,
                    'odds' => 292201338,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 29947422.93,
                    'is_jackpot' => true,
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 0,
                    'prize' => 100000,
                    'odds' => 11688053.52,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 1,
                    'prize' => 5000,
                    'odds' => 913129.18,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 0,
                    'prize' => 10,
                    'odds' => 36525.17,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 1,
                    'prize' => 10,
                    'odds' => 14494.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 0,
                    'prize' => 0.7,
                    'odds' => 579.76,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],

                //MATCH_N = 2
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 1,
                    'prize' => 0.7,
                    'odds' => 701.33,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 1
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 1,
                    'match_b' => 1,
                    'prize' => 0.4,
                    'odds' => 91.98,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 0
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 0,
                    'match_b' => 1,
                    'prize' => 0.4,
                    'odds' => 38.32,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0,
                    'is_jackpot' => false,
                ],
            ],
            'lottery_provider' => [
                [
                    'id' => self::LOTTERY_PROVIDER_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'provider' => Model_Lottery_Provider::LOTTORISQ,
                    'min_bets' => 1,
                    'max_bets' => 5,
                    'multiplier' => 0,
                    'closing_time' => '22:00:00',
                    'timezone' => self::TIMEZONE,
                    'offset' => 0,
                    'tax' => 0,
                    'tax_min' => 0,
                    'fee' => 0.03,
                    'max_payout' => 0,
                    'closing_times' => null
                ],
            ],
            'whitelabel_lottery' => [
                [
                    'whitelabel_id' => '1',
                    'lottery_id' => self::LOTTERY_ID,
                    'lottery_provider_id' => self::LOTTERY_PROVIDER_ID,
                    'is_enabled' => '1',
                    'model' => '2',
                    'income' => '0.27',
                    'income_type' => '0',
                    'tier' => '0',
                    'volume' => '1000',
                    'min_lines' => '10',
                    'quick_pick_lines' => '10',
                    'is_bonus_balance_in_use' => self::IS_BONUS_BALANCE_IN_USE,
                ],
            ],
        ];
    }
}
