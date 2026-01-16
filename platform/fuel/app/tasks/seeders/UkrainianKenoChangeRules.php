<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Fuel\Core\DB;
use Helpers_Lottery;
use Model_Lottery_Type_Data;

final class UkrainianKenoChangeRules extends Seeder
{
    const LOTTERY_ID = Helpers_Lottery::UKRAINIAN_KENO_ID;
    const LOTTERY_TYPE_ID = 74;
    const NUMBERS_POOL = 80;
    const NUMBERS_DRAWN = 20;
    const NUMBERS_PER_LINE_MIN = 2;
    const NUMBERS_PER_LINE_MAX = 10;
    const ODDS = 9.82;

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

            // SELECTED = 2
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 2,
                'match_b' => 2,
                'prize' => 3.75,
                'odds' => 16.63,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 3.75,
                'is_jackpot' => false,
                'slug' => 'keno-2-2',
            ],

            // SELECTED = 3
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 3,
                'match_b' => 2,
                'prize' => 0.75,
                'odds' => 7.21,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0.75,
                'is_jackpot' => false,
                'slug' => 'keno-3-2',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 3,
                'match_b' => 3,
                'prize' => 10,
                'odds' => 72.07,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 10,
                'is_jackpot' => false,
                'slug' => 'keno-3-3',
            ],

            // SELECTED = 4
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 4,
                'match_b' => 2,
                'prize' => 0.63,
                'odds' => 4.7,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0.63,
                'is_jackpot' => false,
                'slug' => 'keno-4-2',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 4,
                'match_b' => 3,
                'prize' => 1.75,
                'odds' => 23.12,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 1.75,
                'is_jackpot' => false,
                'slug' => 'keno-4-3',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 4,
                'match_b' => 4,
                'prize' => 17.5,
                'odds' => 326.44,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 17.5,
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
                'prize' => 7.5,
                'odds' => 82.7,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 7.5,
                'is_jackpot' => false,
                'slug' => 'keno-5-4',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 5,
                'match_b' => 5,
                'prize' => 110,
                'odds' => 1550.57,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 110,
                'is_jackpot' => false,
                'slug' => 'keno-5-5',
            ],

            // SELECTED = 6
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 6,
                'match_b' => 3,
                'prize' => 0.63,
                'odds' => 7.7,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0.63,
                'is_jackpot' => false,
                'slug' => 'keno-6-3',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 6,
                'match_b' => 4,
                'prize' => 2,
                'odds' => 35.04,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2,
                'is_jackpot' => false,
                'slug' => 'keno-6-4',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 6,
                'match_b' => 5,
                'prize' => 20,
                'odds' => 323.04,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 20,
                'is_jackpot' => false,
                'slug' => 'keno-6-5',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 6,
                'match_b' => 6,
                'prize' => 500,
                'odds' => 7752.84,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 500,
                'is_jackpot' => false,
                'slug' => 'keno-6-6',
            ],

            // SELECTED = 7
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 7,
                'match_b' => 4,
                'prize' => 2,
                'odds' => 19.16,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2,
                'is_jackpot' => false,
                'slug' => 'keno-7-4',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 7,
                'match_b' => 5,
                'prize' => 6.25,
                'odds' => 115.76,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 6.25,
                'is_jackpot' => false,
                'slug' => 'keno-7-5',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 7,
                'match_b' => 6,
                'prize' => 62.5,
                'odds' => 1365.98,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 62.5,
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
                'match_b' => 4,
                'prize' => 1,
                'odds' => 12.27,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 1,
                'is_jackpot' => false,
                'slug' => 'keno-8-4',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 8,
                'match_b' => 5,
                'prize' => 5,
                'odds' => 54.64,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 5,
                'is_jackpot' => false,
                'slug' => 'keno-8-5',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 8,
                'match_b' => 6,
                'prize' => 12.5,
                'odds' => 422.53,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 12.5,
                'is_jackpot' => false,
                'slug' => 'keno-8-6',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 8,
                'match_b' => 7,
                'prize' => 125,
                'odds' => 6232.27,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 125,
                'is_jackpot' => false,
                'slug' => 'keno-8-7',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 8,
                'match_b' => 8,
                'prize' => 6250,
                'odds' => 230114.61,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 6250,
                'is_jackpot' => false,
                'slug' => 'keno-8-8',
            ],

            // SELECTED = 9
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 0,
                'prize' => 1,
                'odds' => 15.69,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 1,
                'is_jackpot' => false,
                'slug' => 'keno-9-0',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 5,
                'prize' => 2,
                'odds' => 30.67,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2,
                'is_jackpot' => false,
                'slug' => 'keno-9-5',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 6,
                'prize' => 7.5,
                'odds' => 174.84,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 7.5,
                'is_jackpot' => false,
                'slug' => 'keno-9-6',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 7,
                'prize' => 37.5,
                'odds' => 1690.11,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 37.5,
                'is_jackpot' => false,
                'slug' => 'keno-9-7',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 8,
                'prize' => 1500,
                'odds' => 30681.95,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 1500,
                'is_jackpot' => false,
                'slug' => 'keno-9-8',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 9,
                'match_b' => 9,
                'prize' => 16250,
                'odds' => 1380687.65,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 16250,
                'is_jackpot' => false,
                'slug' => 'keno-9-9',
            ],

            // SELECTED = 10
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 10,
                'match_b' => 0,
                'prize' => 1,
                'odds' => 21.84,
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
                'odds' => 19.44,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 1,
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
                'prize' => 25,
                'odds' => 620.68,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 25,
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
                'prize' => 2500,
                'odds' => 163381.37,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 2500,
                'is_jackpot' => false,
                'slug' => 'keno-10-9',
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 10,
                'match_b' => 10,
                'prize' => 37500,
                'odds' => 8911711.18,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 37500,
                'is_jackpot' => true,
                'slug' => 'keno-10-10',
            ],
        ];

        DB::update('lottery')
            ->set([
                    'is_multidraw_enabled' => 1,
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
