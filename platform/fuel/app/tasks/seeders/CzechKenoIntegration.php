<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;
use Model_Lottery_Type_Data;
use Models\Lottery;

final class CzechKenoIntegration extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Adjusts_Next_Draw_Date;

    const SLUG = Lottery::CZECH_KENO_SLUG;
    const LOTTERY_ID = Helpers_Lottery::CZECH_KENO_ID;
    const LOTTERY_SOURCE_ID = 48;
    const LOTTERY_TYPE_ID = 42;
    const LOTTERY_PROVIDER_ID = 52;
    const START_TIME = '0:05';
    const INTERVAL = 5;
    const DRAWS_DAILY = 287;
    const TICKET_PRICE = 0.5;

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
        $timezone = 'Europe/Prague';
        $draw_dates = static::getDrawDatesArray(self::START_TIME, self::INTERVAL, self::DRAWS_DAILY);
        $draw_dates_json = json_encode($draw_dates);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                [
                    'id' => self::LOTTERY_ID,
                    'source_id' => self::LOTTERY_SOURCE_ID,
                    'name' => 'Czech Keno',
                    'shortname' => 'CZK',
                    'country' => 'Czechia',
                    'country_iso' => 'CZ',
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
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 0.34, 12, 0, 60, 0, 0, 0],
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

                // SELECTED = 2
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 1,
                    'prize' => 0.5,
                    'odds' => 3.07,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-1',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 2,
                    'match_b' => 2,
                    'prize' => 4.5,
                    'odds' => 26.82,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 4.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-2-2',
                ],

                // SELECTED = 3
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 1,
                    'prize' => 0.5,
                    'odds' => 2.53,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-1',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 2,
                    'prize' => 1,
                    'odds' => 10.8,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-3-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 3,
                    'match_b' => 3,
                    'prize' => 6,
                    'odds' => 155.55,
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
                    'prize' => 1,
                    'odds' => 6.55,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 3,
                    'prize' => 5.5,
                    'odds' => 46.18,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 4,
                    'match_b' => 4,
                    'prize' => 50,
                    'odds' => 985.12,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 50,
                    'is_jackpot' => false,
                    'slug' => 'keno-4-4',
                ],

                // SELECTED = 5
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 2,
                    'prize' => 1,
                    'odds' => 4.78,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 3,
                    'prize' => 1.5,
                    'odds' => 22.01,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 5,
                    'match_b' => 4,
                    'prize' => 8,
                    'odds' => 229.86,
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
                    'odds' => 6895.85,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 100,
                    'is_jackpot' => false,
                    'slug' => 'keno-5-5',
                ],

                // SELECTED = 6
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 2,
                    'prize' => 0.5,
                    'odds' => 3.9,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 13.16,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 4,
                    'prize' => 6,
                    'odds' => 89.66,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 6,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 5,
                    'prize' => 50,
                    'odds' => 1316.92,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 50,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 6,
                    'match_b' => 6,
                    'prize' => 1000,
                    'odds' => 54181.67,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1000,
                    'is_jackpot' => false,
                    'slug' => 'keno-6-6',
                ],

                // SELECTED = 7
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 2,
                    'prize' => 0.5,
                    'odds' => 3.42,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 0.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-2',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 3,
                    'prize' => 1,
                    'odds' => 9.02,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-3',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 4,
                    'prize' => 1.5,
                    'odds' => 45.11,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 1.5,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-4',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 5,
                    'prize' => 6,
                    'odds' => 432.3,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 6,
                    'is_jackpot' => false,
                    'slug' => 'keno-7-5',
                ],
                [
                    'lottery_type_id' => self::LOTTERY_TYPE_ID,
                    'match_n' => 7,
                    'match_b' => 6,
                    'prize' => 100,
                    'odds' => 8707.77,
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
                    'odds' => 487635,
                    'type' => Model_Lottery_Type_Data::FIXED,
                    'estimated' => 5000,
                    'is_jackpot' => true,
                    'slug' => 'keno-7-7',
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
                self::LOTTERY_TYPE_ID, 2, 7
            ],
        ];
    }
}
