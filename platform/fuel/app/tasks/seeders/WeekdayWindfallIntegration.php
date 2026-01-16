<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class WeekdayWindfallIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const LOTTERY_SLUG = Lottery::WEEKDAY_WINDFALL_SLUG;
    const LOTTERY_NAME = 'Weekday Windfall';
    const LOTTERY_SHORTNAME = 'WWF';
    const LOTTERY_ID = Helpers_Lottery::WEEKDAY_WINDFALL_ID;
    const LOTTERY_SOURCE_ID = 56;
    const LOTTERY_TYPE_ID = 50;
    const LOTTERY_PROVIDER_ID = 60;
    const COUNTRY_NAME = 'Australia';
    const COUNTRY_ISO = 'AU';
    const TIMEZONE = 'Australia/Melbourne';
    const TICKET_PRICE = 0.68;
    const CURRENCY = Currency::AUD;
    const NUMBERS_POOL = 45;
    const NUMBERS_DRAWN = 6;
    const BONUS_NUMBERS_POOL = 0;
    const BONUS_NUMBERS_DRAWN = 0;
    const EXTRA_NUMBERS_COUNT = 2;
    const ODDS = 85.44;
    const DEF_INSURED_TIERS = 2;
    const SCANS_ENABLED = 1;
    const IS_BONUS_BALANCE_IN_USE = true;
    const DRAW_DAYS = [Carbon::MONDAY, Carbon::WEDNESDAY, Carbon::FRIDAY];
    const DRAW_TIMES = ['20:30'];

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
                    'draw_jackpot_set' => 0,
                    'currency_id' => self::CURRENCY,
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
                    'force_currency_id' => self::CURRENCY,
                    'scans_enabled' => self::SCANS_ENABLED,
                ],
            ],
            'lottery_source' => [
                [
                    'id' => self::LOTTERY_SOURCE_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'name' => 'hq.gginternational.work SITE OFFICIAL',
                    'website' => 'https://hq.gginternational.work',
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

                // MATCH_N = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 0,
                    'prize' => 0,
                    'odds' => 8145060,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000000,
                    'is_jackpot' => true,
                ],

                // MATCH_N = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 1,
                    'prize' => 0.045,
                    'odds' => 678755,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 8281.22,
                    'is_jackpot' => false,
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 0,
                    'prize' => 0.07,
                    'odds' => 36690,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 536.09,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 0,
                    'prize' => 0.19,
                    'odds' => 733,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 28.83,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 1,
                    'prize' => 0.27,
                    'odds' => 298,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 16.34,
                    'is_jackpot' => false,
                ],

                // MATCH_N = 1
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 1,
                    'match_b' => 2,
                    'prize' => 0.42,
                    'odds' => 144,
                    'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                    'estimated' => 12.99,
                    'is_jackpot' => false,
                ],
            ],
            'lottery_provider' => [
                [
                    'id' => self::LOTTERY_PROVIDER_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'provider' => Model_Lottery_Provider::LOTTORISQ,
                    'min_bets' => 4,
                    'max_bets' => 4,
                    'multiplier' => 4,
                    'closing_time' => '18:00:00',
                    'timezone' => self::TIMEZONE,
                    'offset' => 0,
                    'tax' => 0,
                    'tax_min' => 0,
                    'fee' => 0.07,
                    'max_payout' => 0,
                    'closing_times' => '{"1":"18:00:00", "3":"18:00:00", "5":"18:00:00"}'
                ],
            ],
            'whitelabel_lottery' => [
                [
                    'whitelabel_id' => '1',
                    'lottery_id' => self::LOTTERY_ID,
                    'lottery_provider_id' => self::LOTTERY_PROVIDER_ID,
                    'is_enabled' => '1',
                    'model' => '2',
                    'income' => '1',
                    'income_type' => '0',
                    'tier' => '0',
                    'volume' => '1000',
                    'min_lines' => '1',
                    'is_bonus_balance_in_use' => self::IS_BONUS_BALANCE_IN_USE,
                ],
            ],
        ];
    }
}
