<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class GreekKenoIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const SLUG = Lottery::GREEK_KENO_SLUG;
    const LOTTERY_ID = Helpers_Lottery::GREEK_KENO_ID;
    const LOTTERY_SOURCE_ID = 47;
    const LOTTERY_TYPE_ID = 41;
    const LOTTERY_PROVIDER_ID = 51;
    const START_TIME = '0:00';
    const INTERVAL = 5;
    const DRAWS_DAILY = 288;
    const TICKET_PRICE = 1;

    /**
     * Tables disabled on production.
     *
     * @var string[]
     */
    private $disabled_tables_on_production = [
        'whitelabel_lottery'
    ];

    protected static function getDrawDatesArray($start_time, $interval, $draws_daily): array
    {
        $draw_dates = [];
        foreach (Helpers_Time::ISO_WEEK_DAYS as $day) {
            $date = Carbon::parse($day . ' ' . $start_time);
            for ($i = 0; $i < $draws_daily; $i++) {
                $date->addMinutes($i == 0 ? 0 : $interval);// we don't want to add interval to start time!
                $draw_dates[] = $date->format('D H:i');
            }
        }
        return $draw_dates;
    }

    protected function columnsStaging(): array
    {
        return [
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'type', 'force_currency_id'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot', 'slug'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines', 'is_bonus_balance_in_use'],
            'lottery_type_multiplier' => ['multiplier', 'lottery_id'], // we don't need to have any foreign keys other than lottery_id
            'lottery_type_numbers_per_line' => ['lottery_type_id', 'min', 'max'],
        ];
    }

    public static function lottery_multipliers_rows(): array
    {
        $multipliers = [];
        for ($i = 1; $i <= 10; $i++) {
            $multipliers[] = [$i, self::LOTTERY_ID];
        }

        return $multipliers;
    }


    protected function rowsStaging(): array
    {
        $timezone = 'Europe/Athens';
        $draw_dates = static::getDrawDatesArray(self::START_TIME, self::INTERVAL, self::DRAWS_DAILY);
        $draw_dates_json = json_encode($draw_dates);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [
                    'id' => self::LOTTERY_ID,
                    'source_id' => self::LOTTERY_SOURCE_ID,
                    'name' => 'Greek Keno',
                    'shortname' => 'GRK',
                    'country' => 'Greece',
                    'country_iso' => 'GR',
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
                    'type' => 'keno',
                    'force_currency_id' => null,
                ],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'hq.gginternational.work SITE OFFICIAL', 'https://hq.gginternational.work'],
            ],
            'lottery_type' => [
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 0.18, 20, 0, 80, 0, 0, 0],
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
                    'prize' => 2.5,
                    'odds' => 4,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-1-1',
                ],


                // SELECTED = 2
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 1,
                    'prize' => 1,
                    'odds' => 2.63,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-1',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 2,
                    'prize' => 5,
                    'odds' => 16.63,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-2',
                ],


                // SELECTED = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 2,
                    'prize' => 2.5,
                    'odds' => 7.21,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 3,
                    'prize' => 25,
                    'odds' => 72.07,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 25,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-3',
                ],


                // SELECTED = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 2,
                    'prize' => 1,
                    'odds' => 4.7,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-2',
                ],
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
                    'prize' => 100,
                    'odds' => 326.44,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-4',
                ],


                // SELECTED = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 3,
                    'prize' => 2,
                    'odds' => 11.91,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 4,
                    'prize' => 20,
                    'odds' => 82.7,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 5,
                    'prize' => 450,
                    'odds' => 1550.57,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 450,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-5',
                ],


                // SELECTED = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 7.7,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 4,
                    'prize' => 7,
                    'odds' => 35.04,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 7,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 5,
                    'prize' => 50,
                    'odds' => 323.04,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 50,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 6,
                    'prize' => 1600,
                    'odds' => 7752.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1600,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-6',
                ],


                // SELECTED = 7
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 5.71,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 4,
                    'prize' => 3,
                    'odds' => 19.16,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 3,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 5,
                    'prize' => 20,
                    'odds' => 115.76,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 6,
                    'prize' => 100,
                    'odds' => 1365.98,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 7,
                    'prize' => 5000,
                    'odds' => 40979.31,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5000,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-7',
                ],


                // SELECTED = 8
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 4,
                    'prize' => 2,
                    'odds' => 12.27,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 5,
                    'prize' => 10,
                    'odds' => 54.64,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 6,
                    'prize' => 50,
                    'odds' => 422.53,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 50,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 7,
                    'prize' => 1000,
                    'odds' => 6232.27,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 8,
                    'prize' => 15000,
                    'odds' => 230114.61,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 15000,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-8',
                ],


                // SELECTED = 9
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 4,
                    'prize' => 1,
                    'odds' => 8.76,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 5,
                    'prize' => 5,
                    'odds' => 30.67,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 6,
                    'prize' => 25,
                    'odds' => 174.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 25,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 7,
                    'prize' => 200,
                    'odds' => 1690.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 200,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 8,
                    'prize' => 4000,
                    'odds' => 30681.95,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4000,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 9,
                    'prize' => 40000,
                    'odds' => 1380687.65,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 40000,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-9',
                ],


                // SELECTED = 10
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 0,
                    'prize' => 2,
                    'odds' => 21.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 5,
                    'prize' => 2,
                    'odds' => 19.44,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 6,
                    'prize' => 20,
                    'odds' => 87.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 7,
                    'prize' => 80,
                    'odds' => 620.68,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 80,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 8,
                    'prize' => 500,
                    'odds' => 7384.47,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 500,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 9,
                    'prize' => 10000,
                    'odds' => 163381.37,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10000,
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
                    'is_jackpot' => false,
                    'slug' => 'keno-10-10',
                ],


                // SELECTED = 11
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 0,
                    'prize' => 2,
                    'odds' => 30.57,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 5,
                    'prize' => 1,
                    'odds' => 13.5,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 6,
                    'prize' => 10,
                    'odds' => 49.50,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 7,
                    'prize' => 50,
                    'odds' => 277.18,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 50,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 8,
                    'prize' => 250,
                    'odds' => 2430.62,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 250,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 9,
                    'prize' => 1500,
                    'odds' => 35244.06,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1500,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-9',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 10,
                    'prize' => 15000,
                    'odds' => 945181.49,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 15000,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-10',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 11,
                    'match_b' => 11,
                    'prize' => 500000,
                    'odds' => 62381978.24,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 500000,
                    'is_jackpot' => false,
                    'slug' => 'keno-11-11',
                ],


                // SELECTED = 12
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 0,
                    'prize' => 4,
                    'odds' => 43.05,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4,
                    'is_jackpot' => false,
                    'slug' => 'keno-12-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 6,
                    'prize' => 5,
                    'odds' => 31.05,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-12-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 7,
                    'prize' => 25,
                    'odds' => 142.30,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 25,
                    'is_jackpot' => false,
                    'slug' => 'keno-12-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 8,
                    'prize' => 150,
                    'odds' => 980.78,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 150,
                    'is_jackpot' => false,
                    'slug' => 'keno-12-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 9,
                    'prize' => 1000,
                    'odds' => 10482.07,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000,
                    'is_jackpot' => false,
                    'slug' => 'keno-12-9',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 10,
                    'prize' => 2500,
                    'odds' => 184230.29,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2500,
                    'is_jackpot' => false,
                    'slug' => 'keno-12-10',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 11,
                    'prize' => 25000,
                    'odds' => 5978272.91,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 25000,
                    'is_jackpot' => false,
                    'slug' => 'keno-12-11',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 12,
                    'match_b' => 12,
                    'prize' => 1000000,
                    'odds' => 478261833.14,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000000,
                    'is_jackpot' => true,
                    'slug' => 'keno-12-12',
                ],
            ],
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::NONE, 1, 7, 0, '19:30:00', $timezone, 30, 0, 0, 0, 49999],
            ],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '0', '0', '0', '1000', '1', true],
            ],
            'lottery_type_multiplier' => self::lottery_multipliers_rows(),
            'lottery_type_numbers_per_line' => [
                self::LOTTERY_TYPE_ID, 1, 12
            ],
        ];
    }
}
