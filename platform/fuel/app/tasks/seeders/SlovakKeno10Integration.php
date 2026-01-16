<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class SlovakKeno10Integration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const LOTTERY_SLUG = Lottery::SLOVAK_KENO_10_SLUG;
    const LOTTERY_NAME = 'Slovak Keno 10';
    const LOTTERY_SHORTNAME = 'SK2';
    const LOTTERY_ID = Helpers_Lottery::SLOVAK_KENO_10_ID;
    const LOTTERY_SOURCE_ID = 57;
    const LOTTERY_TYPE_ID = 51;
    const LOTTERY_PROVIDER_ID = 61;
    const COUNTRY_NAME = 'Slovakia';
    const COUNTRY_ISO = 'SK';
    const TIMEZONE = 'Europe/Bratislava';
    const TICKET_PRICE = 0.5;
    const CURRENCY = Currency::EUR;
    const MULTIPLIER_MAX = 20;
    const NUMBERS_POOL = 80;
    const NUMBERS_DRAWN = 20;
    const NUMBERS_PER_LINE_MIN = 1;
    const NUMBERS_PER_LINE_MAX = 10;
    const ODDS = 9.17;
    const DRAW_DATES = [
        'Mon 18:00',
        'Tue 18:00',
        'Wed 18:00',
        'Thu 18:00',
        'Fri 18:00',
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
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'type', 'force_currency_id'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot', 'slug'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines', 'is_bonus_balance_in_use'],
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
                    'odds' => 4,
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
                    'prize' => 4,
                    'odds' => 16.63,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-2',
                ],

                // SELECTED = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 2,
                    'prize' => 1,
                    'odds' => 7.21,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 3,
                    'prize' => 8,
                    'odds' => 72.07,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 8,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-3',
                ],

                // SELECTED = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 3,
                    'prize' => 4,
                    'odds' => 23.12,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 4,
                    'prize' => 25,
                    'odds' => 326.44,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 25,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-4',
                ],

                // SELECTED = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 11.91,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 4,
                    'prize' => 8,
                    'odds' => 82.7,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 8,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 5,
                    'prize' => 100,
                    'odds' => 1550.57,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-5',
                ],

                // SELECTED = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 0,
                    'prize' => 0.5,
                    'odds' => 6,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 3,
                    'prize' => 0.5,
                    'odds' => 7.7,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 4,
                    'prize' => 1,
                    'odds' => 35.04,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 5,
                    'prize' => 10,
                    'odds' => 323.04,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 6,
                    'prize' => 300,
                    'odds' => 7752.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 300,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-6',
                ],

                // SELECTED = 7
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 0,
                    'prize' => 0.5,
                    'odds' => 8.23,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 4,
                    'prize' => 1,
                    'odds' => 19.16,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 5,
                    'prize' => 5,
                    'odds' => 115.76,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 6,
                    'prize' => 50,
                    'odds' => 1365.98,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 50,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 7,
                    'prize' => 2000,
                    'odds' => 40979.31,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2000,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-7',
                ],

                // SELECTED = 8
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 0,
                    'prize' => 0.5,
                    'odds' => 11.33,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 4,
                    'prize' => 0.5,
                    'odds' => 12.27,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 5,
                    'prize' => 2,
                    'odds' => 54.64,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 6,
                    'prize' => 20,
                    'odds' => 422.53,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 7,
                    'prize' => 200,
                    'odds' => 6232.27,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 200,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 8,
                    'prize' => 10000,
                    'odds' => 230114.61,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10000,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-8',
                ],

                // SELECTED = 9
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 0,
                    'prize' => 0.5,
                    'odds' => 15.69,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 5,
                    'prize' => 1.5,
                    'odds' => 30.67,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 6,
                    'prize' => 10,
                    'odds' => 174.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 7,
                    'prize' => 100,
                    'odds' => 1690.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 8,
                    'prize' => 1000,
                    'odds' => 30681.95,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 9,
                    'prize' => 25000,
                    'odds' => 1380687.65,
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
                    'odds' => 21.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 5,
                    'prize' => 1.5,
                    'odds' => 19.44,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 6,
                    'prize' => 5,
                    'odds' => 87.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 7,
                    'prize' => 10,
                    'odds' => 620.68,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 8,
                    'prize' => 250,
                    'odds' => 7384.47,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 250,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 9,
                    'prize' => 5000,
                    'odds' => 163381.37,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5000,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-9',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 10,
                    'prize' => 100000,
                    'odds' => 8911711.18,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100000,
                    'is_jackpot' => true,
                    'slug' => 'keno-10-10',
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
                    'closing_time' => '17:30:00',
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
                    'min_lines' => '1',
                    'is_bonus_balance_in_use' => true,
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

