<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class SwedishKenoIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const LOTTERY_SLUG = Lottery::SWEDISH_KENO_SLUG;
    const LOTTERY_NAME = 'Swedish Keno';
    const LOTTERY_SHORTNAME = 'SEK';
    const LOTTERY_ID = Helpers_Lottery::SWEDISH_KENO_ID;
    const LOTTERY_SOURCE_ID = 66;
    const LOTTERY_TYPE_ID = 60;
    const LOTTERY_PROVIDER_ID = 70;
    const COUNTRY_NAME = 'Sweden';
    const COUNTRY_ISO = 'SE';
    const TIMEZONE = 'Europe/Stockholm';
    const TICKET_PRICE = 0.5;
    const CURRENCY = Currency::EUR;
    const MULTIPLIER_MAX = 20;
    const NUMBERS_POOL = 70;
    const NUMBERS_DRAWN = 20;
    const NUMBERS_PER_LINE_MIN = 1;
    const NUMBERS_PER_LINE_MAX = 11;
    const ODDS = 7.39;
    const DRAW_DATES = [
        'Mon 19:00',
        'Tue 19:00',
        'Wed 19:00',
        'Thu 19:00',
        'Fri 19:00',
        'Sat 18:00',
        'Sun 18:00',
    ];

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
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'type', 'force_currency_id', 'is_multidraw_enabled'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot', 'slug'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines', 'quick_pick_lines', 'is_bonus_balance_in_use', 'is_multidraw_enabled'],
            'lottery_type_multiplier' => ['multiplier', 'lottery_id'],
            'lottery_type_numbers_per_line' => ['lottery_type_id', 'min', 'max'],
        ];
    }

    public static function lottery_multipliers_rows(): array
    {
        $multipliers = [];
        for ($i = 1; $i <= self::MULTIPLIER_MAX; $i++) {
            $multipliers[] = [$i, self::LOTTERY_ID];
        }

        return $multipliers;
    }

    protected function rowsStaging(): array
    {
        $draw_dates_json = json_encode(self::DRAW_DATES);
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
                    'type' => 'keno',
                    'force_currency_id' => self::CURRENCY,
                    'is_multidraw_enabled' => 1,
                ],
            ],
            'lottery_source' => [
                [
                    'id' => self::LOTTERY_SOURCE_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'name' => 'OFFICIAL WEBSITE',
                    'website' => 'OFFICIAL WEBSITE',
                ]
            ],
            'lottery_type' => [
                [
                    'id' => self::LOTTERY_TYPE_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'odds' => self::ODDS,
                    'ncount' => self::NUMBERS_DRAWN,
                    'bcount' => 0,
                    'nrange' => self::NUMBERS_POOL,
                    'brange' => 0,
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
                 * 'match_n' => {SELECTED},
                 * 'match_b' => {MATCHED},
                 * 'odds' => X, // Odds 1 in ...
                 * 'slug' => 'keno-{SELECTED}-{MATCHED}',
                 */

                // SELECTED = 1
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 1,
                    'match_b' => 1,
                    'prize' => 1,
                    'odds' => 3.5,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-1-1',
                ],

                // SELECTED = 2
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 2,
                    'prize' => 3,
                    'odds' => 12.71,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 3,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-2',
                ],

                // SELECTED = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 2,
                    'prize' => 0.5,
                    'odds' => 5.76,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 3,
                    'prize' => 6,
                    'odds' => 48.02,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 6,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-3',
                ],

                // SELECTED = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 2,
                    'prize' => 0.5,
                    'odds' => 3.94,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 16.09,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 4,
                    'prize' => 12,
                    'odds' => 189.25,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 12,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-4',
                ],

                // SELECTED = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 3,
                    'prize' => 0.5,
                    'odds' => 8.67,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 4,
                    'prize' => 4,
                    'odds' => 49.96,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 5,
                    'prize' => 80,
                    'odds' => 780.64,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 80,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-5',
                ],

                // SELECTED = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 3,
                    'prize' => 0.5,
                    'odds' => 5.87,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 4,
                    'prize' => 1.5,
                    'odds' => 22.09,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 5,
                    'prize' => 9,
                    'odds' => 169.14,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 9,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 6,
                    'prize' => 170,
                    'odds' => 3382.77,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 170,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-6',
                ],

                // SELECTED = 7
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 4,
                    'prize' => 0.5,
                    'odds' => 12.62,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 5,
                    'prize' => 5,
                    'odds' => 63.12,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 6,
                    'prize' => 40,
                    'odds' => 618.56,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 40,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 7,
                    'prize' => 1000,
                    'odds' => 15464.07,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-7',
                ],

                // SELECTED = 8
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 4,
                    'prize' => 0.5,
                    'odds' => 8.46,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 5,
                    'prize' => 1.5,
                    'odds' => 31.07,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 6,
                    'prize' => 10,
                    'odds' => 198.82,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 7,
                    'prize' => 100,
                    'odds' => 2435.59,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 8,
                    'prize' => 4500,
                    'odds' => 74941.26,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4500,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-8',
                ],

                // SELECTED = 9
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 5,
                    'prize' => 0.5,
                    'odds' => 18.21,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 6,
                    'prize' => 5,
                    'odds' => 85.6,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 7,
                    'prize' => 25,
                    'odds' => 684.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 25,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 8,
                    'prize' => 500,
                    'odds' => 10325.24,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 500,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 9,
                    'prize' => 25000,
                    'odds' => 387196.53,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 25000,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-9',
                ],

                // SELECTED = 10
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 0,
                    'prize' => 0.5,
                    'odds' => 38.62,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 5,
                    'prize' => 0.5,
                    'odds' => 12.08,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 6,
                    'prize' => 1.5,
                    'odds' => 44.44,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 7,
                    'prize' => 10,
                    'odds' => 261.09,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 8,
                    'prize' => 80,
                    'odds' => 2570.77,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 80,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 9,
                    'prize' => 1500,
                    'odds' => 47237.98,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1500,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-9',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 10,
                    'prize' => 100000,
                    'odds' => 2147180.74,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100000,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-10',
                ],

                // SELECTED = 11
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 5,
                    'prize' => 0.5,
                    'odds' => 8.78,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 6,
                    'prize' => 1,
                    'odds' => 26.35,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 7,
                    'prize' => 3,
                    'odds' => 121.2,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 3,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 8,
                    'prize' => 20,
                    'odds' => 876.4,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 9,
                    'prize' => 200,
                    'odds' => 10516.8,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 200,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-9',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 10,
                    'prize' => 8000,
                    'odds' => 234237.9,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 8000,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-10',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 11,
                    'prize' => 500000,
                    'odds' => 12883084.42,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 500000,
                    'is_jackpot' => true,
                    'slug' => 'keno-11-11',
                ],
            ],
            'lottery_provider' => [
                [
                    'id' => self::LOTTERY_PROVIDER_ID,
                    'lottery_id' => self::LOTTERY_ID,
                    'provider' => Model_Lottery_Provider::NONE,
                    'min_bets' => 1,
                    'max_bets' => 7,
                    'multiplier' => 0,
                    'closing_time' => '18:00:00',
                    'timezone' => self::TIMEZONE,
                    'offset' => 30,
                    'tax' => 0,
                    'tax_min' => 0,
                    'fee' => 0,
                    'max_payout' => 49999,
                ],
            ],
            'whitelabel_lottery' => [
                [
                    'whitelabel_id' => '1',
                    'lottery_id' => self::LOTTERY_ID,
                    'lottery_provider_id' => self::LOTTERY_PROVIDER_ID,
                    'is_enabled' => '1',
                    'model' => '0',
                    'income' => '0',
                    'income_type' => '0',
                    'tier' => '0',
                    'volume' => '1000',
                    'min_lines' => '3',
                    'quick_pick_lines' => 3,
                    'is_bonus_balance_in_use' => true,
                    'is_multidraw_enabled' => true,
                ],
            ],
            'lottery_type_multiplier' => self::lottery_multipliers_rows(),
            'lottery_type_numbers_per_line' => [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'min' => self::NUMBERS_PER_LINE_MIN,
                'max' => self::NUMBERS_PER_LINE_MAX,
            ],
        ];
    }
}

