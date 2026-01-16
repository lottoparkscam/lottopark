<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class ItalianKenoIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const LOTTERY_SLUG = Lottery::ITALIAN_KENO_SLUG;
    const LOTTERY_NAME = 'Italian Keno';
    const LOTTERY_SHORTNAME = 'ITK';
    const LOTTERY_ID = Helpers_Lottery::ITALIAN_KENO_ID;
    const LOTTERY_SOURCE_ID = 55;
    const LOTTERY_TYPE_ID = 49;
    const LOTTERY_PROVIDER_ID = 59;
    const COUNTRY_NAME = 'Italy';
    const COUNTRY_ISO = 'IT';
    const TIMEZONE = 'Europe/Rome';
    const TICKET_PRICE = 1;
    const CURRENCY = Currency::EUR;
    const MULTIPLIER_MAX = 5;
    const NUMBERS_POOL = 90;
    const NUMBERS_DRAWN = 20;
    const NUMBERS_PER_LINE_MIN = 1;
    const NUMBERS_PER_LINE_MAX = 10;
    const ODDS = 0.15;
    const DRAW_START_TIME = '0:00';
    const DRAW_INTERVAL_IN_MINUTES = 5;
    const DRAWS_DAILY = 288;

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

    protected function rowsStaging(): array
    {
        $draw_dates = Helpers_Lottery::getDrawDatesArray(self::DRAW_START_TIME, self::DRAW_INTERVAL_IN_MINUTES, self::DRAWS_DAILY);
        $draw_dates_json = json_encode($draw_dates);
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
                    'name' => 'hq.gginternational.work SITE OFFICIAL',
                    'website' => 'https://hq.gginternational.work',
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
                    'prize' => 3,
                    'odds' => 4.5,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 3,
                    'is_jackpot' => false,
                    'slug' => 'keno-1-1',
                ],

                // SELECTED = 2
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 2,
                    'prize' => 14,
                    'odds' => 21.08,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 14,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-2',
                ],

                // SELECTED = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 2,
                    'prize' => 2,
                    'odds' => 8.83,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 3,
                    'prize' => 45,
                    'odds' => 103.05,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 45,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-3',
                ],

                // SELECTED = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 2,
                    'prize' => 1,
                    'odds' => 5.57,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 3,
                    'prize' => 10,
                    'odds' => 32.02,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 4,
                    'prize' => 90,
                    'odds' => 527.39,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 90,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-4',
                ],

                // SELECTED = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 2,
                    'prize' => 1,
                    'odds' => 4.23,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 3,
                    'prize' => 4,
                    'odds' => 15.96,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 4,
                    'prize' => 15,
                    'odds' => 129.59,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 15,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 5,
                    'prize' => 140,
                    'odds' => 2834.71,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 140,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-5',
                ],

                // SELECTED = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 3,
                    'prize' => 2,
                    'odds' => 9.98,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 4,
                    'prize' => 10,
                    'odds' => 53.21,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 5,
                    'prize' => 100,
                    'odds' => 573.69,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 6,
                    'prize' => 1000,
                    'odds' => 16063.33,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-6',
                ],

                // SELECTED = 7
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 6.23,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 4,
                    'prize' => 4,
                    'odds' => 28.17,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 5,
                    'prize' => 40,
                    'odds' => 199.54,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 40,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 6,
                    'prize' => 400,
                    'odds' => 2753.71,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 400,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 7,
                    'prize' => 1600,
                    'odds' => 96379.97,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1600,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-7',
                ],

                // SELECTED = 8
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 8.21,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 5,
                    'prize' => 20,
                    'odds' => 91.34,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 6,
                    'prize' => 200,
                    'odds' => 828.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 200,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 7,
                    'prize' => 800,
                    'odds' => 14284.89,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 800,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 8,
                    'prize' => 10000,
                    'odds' => 615349.06,
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
                    'prize' => 2,
                    'odds' => 10.86,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 5,
                    'prize' => 10,
                    'odds' => 49.68,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 6,
                    'prize' => 40,
                    'odds' => 332.87,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 40,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 7,
                    'prize' => 400,
                    'odds' => 3772.5,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 400,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 8,
                    'prize' => 2000,
                    'odds' => 80093.05,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2000,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 9,
                    'prize' => 100000,
                    'odds' => 4204885.26,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100000,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-9',
                ],

                // SELECTED = 10
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 0,
                    'prize' => 2,
                    'odds' => 14.42,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 5,
                    'prize' => 5,
                    'odds' => 30.49,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 6,
                    'prize' => 15,
                    'odds' => 160.97,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 15,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 7,
                    'prize' => 150,
                    'odds' => 1348.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 150,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 8,
                    'prize' => 1000,
                    'odds' => 18804.46,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 9,
                    'prize' => 20000,
                    'odds' => 486565.29,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20000,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-9',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 10,
                    'prize' => 1000000,
                    'odds' => 30963246.02,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000000,
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
                    'closing_time' => '19:30:00',
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
            'lottery_type_multiplier' => Helpers_Lottery::lotteryMultipliersRows(self::LOTTERY_ID, self::MULTIPLIER_MAX),
            'lottery_type_numbers_per_line' => [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'min' => self::NUMBERS_PER_LINE_MIN,
                'max' => self::NUMBERS_PER_LINE_MAX,
            ],
        ];
    }
}
