<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Fuel\Core\DB;
use Helpers_Lottery;
use Model_Lottery_Type_Data;

final class LatvianKenoChangeRules extends Seeder
{
    const LOTTERY_ID = Helpers_Lottery::LATVIAN_KENO_ID;
    const LOTTERY_TYPE_ID = 73;
    const NUMBERS_POOL = 62;
    const NUMBERS_DRAWN = 20;
    const NUMBERS_PER_LINE_MIN = 1;
    const NUMBERS_PER_LINE_MAX = 10;
    const ODDS = 9.41;

    protected function columnsStaging(): array
    {
        return [];
    }

    protected function rowsStaging(): array
    {
        return [];
    }

    public function execute(): void
    {
        $dataTiers = [
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
                'prize' => 2,
                'odds' => 9.95,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2,
                'is_jackpot' => false,
                'slug' => 'keno-2-2',
            ],

            // SELECTED = 3
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 3,
                'match_b' => 3,
                'prize' => 5,
                'odds' => 33.18,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 5,
                'is_jackpot' => false,
                'slug' => 'keno-3-3',
            ],

            // SELECTED = 4
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 4,
                'match_b' => 3,
                'prize' => 2,
                'odds' => 11.65,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2,
                'is_jackpot' => false,
                'slug' => 'keno-4-3',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 4,
                'match_b' => 4,
                'prize' => 15,
                'odds' => 115.14,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 15,
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
                'prize' => 5,
                'odds' => 31.8,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 5,
                'is_jackpot' => false,
                'slug' => 'keno-5-4',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 5,
                'match_b' => 5,
                'prize' => 60,
                'odds' => 417.38,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 60,
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
                'prize' => 10,
                'odds' => 94.41,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 10,
                'is_jackpot' => false,
                'slug' => 'keno-6-5',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 6,
                'match_b' => 6,
                'prize' => 250,
                'odds' => 1586.03,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 250,
                'is_jackpot' => false,
                'slug' => 'keno-6-6',
            ],

            // SELECTED = 7
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 7,
                'match_b' => 0,
                'prize' => 2,
                'odds' => 18.23,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2,
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
                'prize' => 5,
                'odds' => 36.84,
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
                'odds' => 302.1,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 40,
                'is_jackpot' => false,
                'slug' => 'keno-7-6',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 7,
                'match_b' => 7,
                'prize' => 900,
                'odds' => 6344.12,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 900,
                'is_jackpot' => false,
                'slug' => 'keno-7-7',
            ],

            // SELECTED = 8
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 8,
                'match_b' => 0,
                'prize' => 4,
                'odds' => 28.65,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 4,
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
                'prize' => 15,
                'odds' => 101.31,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 15,
                'is_jackpot' => false,
                'slug' => 'keno-8-6',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 8,
                'match_b' => 7,
                'prize' => 150,
                'odds' => 1038.48,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 150,
                'is_jackpot' => false,
                'slug' => 'keno-8-7',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 8,
                'match_b' => 8,
                'prize' => 3500,
                'odds' => 26840.51,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 3500,
                'is_jackpot' => false,
                'slug' => 'keno-8-8',
            ],

            // SELECTED = 9
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 0,
                'prize' => 6,
                'odds' => 45.5,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 6,
                'is_jackpot' => false,
                'slug' => 'keno-9-0',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 5,
                'prize' => 2,
                'odds' => 11.69,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2,
                'is_jackpot' => false,
                'slug' => 'keno-9-5',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 6,
                'prize' => 5,
                'odds' => 45.59,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 5,
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
                'prize' => 500,
                'odds' => 3834.36,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 500,
                'is_jackpot' => false,
                'slug' => 'keno-9-8',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 9,
                'prize' => 15000,
                'odds' => 120782.28,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 15000,
                'is_jackpot' => false,
                'slug' => 'keno-9-9',
            ],

            // SELECTED = 10
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 10,
                'match_b' => 0,
                'prize' => 10,
                'odds' => 73.07,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 10,
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
                'prize' => 3,
                'odds' => 24.78,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 3,
                'is_jackpot' => false,
                'slug' => 'keno-10-6',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 10,
                'match_b' => 7,
                'prize' => 15,
                'odds' => 120.82,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 15,
                'is_jackpot' => false,
                'slug' => 'keno-10-7',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 10,
                'match_b' => 8,
                'prize' => 150,
                'odds' => 991.32,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 150,
                'is_jackpot' => false,
                'slug' => 'keno-10-8',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 10,
                'match_b' => 9,
                'prize' => 2000,
                'odds' => 15241.57,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2000,
                'is_jackpot' => false,
                'slug' => 'keno-10-9',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 10,
                'match_b' => 10,
                'prize' => 80000,
                'odds' => 581950.97,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 80000,
                'is_jackpot' => true,
                'slug' => 'keno-10-10',
            ],
        ];

        DB::update('lottery')
            ->set([
                    'is_multidraw_enabled' => 1,
                    'force_currency_id' => Currency::EUR,
                ])
                ->where('id', '=', self::LOTTERY_ID)
                ->execute();

        DB::insert('lottery_type')
            ->set([
                'id' => self::LOTTERY_TYPE_ID,
                'lottery_id' => self::LOTTERY_ID,
                'odds' => self::ODDS,
                'ncount' => self::NUMBERS_DRAWN,
                'bcount' => 0,
                'nrange' => self::NUMBERS_POOL,
                'brange' => 0,
                'bextra' => 0,
                'def_insured_tiers' => 0,
                'date_start' => Carbon::now()->format('Y-m-d'),
            ])->execute();
        
        foreach($dataTiers as $datum):
            DB::insert('lottery_type_data')
                ->set([
                    'lottery_type_id' => $datum['lottery_type_id'],
                    'match_n' => $datum['match_n'],
                    'match_b' => $datum['match_b'],
                    'prize' => $datum['prize'],
                    'odds' => $datum['odds'],
                    'type' => $datum['type'],
                    'estimated' => $datum['estimated'],
                    'is_jackpot' => $datum['is_jackpot'],
                    'slug' => $datum['slug'],
                ])->execute();
        endforeach;
        
        DB::insert('lottery_type_numbers_per_line')
        ->set([
            'lottery_type_id' => self::LOTTERY_TYPE_ID,
            'min' => self::NUMBERS_PER_LINE_MIN,
            'max' => self::NUMBERS_PER_LINE_MAX,
        ])->execute();
    }
}
