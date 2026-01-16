<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class LatvianKenoIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const SLUG = Lottery::LATVIAN_KENO_SLUG;
    const LOTTERY_ID = Helpers_Lottery::LATVIAN_KENO_ID;
    const LOTTERY_SOURCE_ID = 50;
    const LOTTERY_TYPE_ID = 44;
    const LOTTERY_PROVIDER_ID = 54;
    const START_TIME = '11:30';
    const INTERVAL = 240;
    const DRAWS_DAILY = 3;
    const TICKET_PRICE = 1;
    const MULTIPLIER_MAX = 10;

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
        $timezone = 'Europe/Riga';
        $draw_dates = static::getDrawDatesArray(self::START_TIME, self::INTERVAL, self::DRAWS_DAILY);
        $draw_dates_json = json_encode($draw_dates);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [
                    'id' => self::LOTTERY_ID,
                    'source_id' => self::LOTTERY_SOURCE_ID,
                    'name' => 'Latvian Keno',
                    'shortname' => 'LVK',
                    'country' => 'Latvia',
                    'country_iso' => 'LV',
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
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 0.21, 20, 0, 62, 0, 0, 0],
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
                    'prize' => 1.5,
                    'odds' => 3.1,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-1-1',
                ],

                // SELECTED = 2
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 2,
                    'prize' => 4.5,
                    'odds' => 9.95,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-2',
                ],

                // SELECTED = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 2,
                    'prize' => 1,
                    'odds' => 4.74,
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
                    'odds' => 33.18,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 8,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-3',
                ],

                // SELECTED = 4
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 4.98,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 11.65,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 4,
                    'prize' => 20,
                    'odds' => 115.14,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 20,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-4',
                ],

                // SELECTED = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 7.61,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 6.59,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 4,
                    'prize' => 2,
                    'odds' => 31.8,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 5,
                    'prize' => 45,
                    'odds' => 417.38,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 45,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-5',
                ],

                // SELECTED = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 11.72,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 4,
                    'prize' => 2,
                    'odds' => 14.74,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 5,
                    'prize' => 12,
                    'odds' => 94.41,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 12,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 6,
                    'prize' => 175,
                    'odds' => 1586.03,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 175,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-6',
                ],

                // SELECTED = 7
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 18.23,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 4,
                    'prize' => 1,
                    'odds' => 8.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 5,
                    'prize' => 3,
                    'odds' => 36.84,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 3,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 6,
                    'prize' => 30,
                    'odds' => 302.1,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 30,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 7,
                    'prize' => 700,
                    'odds' => 6344.12,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 700,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-7',
                ],

                // SELECTED = 8
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 28.65,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 5,
                    'prize' => 3,
                    'odds' => 19,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 3,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 6,
                    'prize' => 5,
                    'odds' => 101.31,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 7,
                    'prize' => 100,
                    'odds' => 1038.48,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 8,
                    'match_b' => 8,
                    'prize' => 3000,
                    'odds' => 26840.51,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 3000,
                    'is_jackpot' => false,
                    'slug' => 'keno-8-8',
                ],

                // SELECTED = 9
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 45.5,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 5,
                    'prize' => 1,
                    'odds' => 11.69,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 6,
                    'prize' => 2,
                    'odds' => 45.59,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 7,
                    'prize' => 40,
                    'odds' => 303.94,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 40,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 8,
                    'prize' => 350,
                    'odds' => 3834.36,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 350,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 9,
                    'match_b' => 9,
                    'prize' => 10000,
                    'odds' => 120782.28,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 10000,
                    'is_jackpot' => false,
                    'slug' => 'keno-9-9',
                ],

                // SELECTED = 10
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 0,
                    'prize' => 1,
                    'odds' => 73.07,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-0',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 5,
                    'prize' => 1,
                    'odds' => 8.15,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 6,
                    'prize' => 2,
                    'odds' => 24.78,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 2,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-6',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 7,
                    'prize' => 5,
                    'odds' => 120.82,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-7',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 8,
                    'prize' => 55,
                    'odds' => 991.32,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 55,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-8',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 9,
                    'prize' => 550,
                    'odds' => 15241.57,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 550,
                    'is_jackpot' => false,
                    'slug' => 'keno-10-9',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 10,
                    'match_b' => 10,
                    'prize' => 60000,
                    'odds' => 581950.97,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 60000,
                    'is_jackpot' => true,
                    'slug' => 'keno-10-10',
                ],
            ],
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::NONE, 1, 7, 0, '19:30:00', $timezone, 30, 0, 0, 0, 49999],
            ],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '0', '0', '0', '1000', '3', 3, true, true],
            ],
            'lottery_type_multiplier' => self::lottery_multipliers_rows(),
            'lottery_type_numbers_per_line' => [
                self::LOTTERY_TYPE_ID, 1, 10
            ],
        ];
    }
}
