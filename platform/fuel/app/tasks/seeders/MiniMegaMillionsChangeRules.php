<?php

namespace Fuel\Tasks\Seeders;

use Carbon\Carbon;
use Fuel\Core\DB;
use Helpers_Lottery;
use Model_Lottery_Type_Data;

final class MiniMegaMillionsChangeRules extends Seeder
{
    const LOTTERY_ID = Helpers_Lottery::MINI_MEGA_MILLIONS_ID;
    const LOTTERY_TYPE_ID = 76;

    const NUMBERS_POOL = 70;
    const NUMBERS_DRAWN = 5;
    const BONUS_NUMBERS_POOL = 24;
    const BONUS_NUMBERS_DRAWN = 1;
    const ODDS = 23.07;

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

            // MATCH_N = 5
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 5,
                'match_b' => 1,
                'prize' => 0.8235,
                'odds' => 290472336,
                'type' => Model_Lottery_Type_Data::PARIMUTUEL,
                'estimated' => 0,
                'is_jackpot' => true,
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 5,
                'match_b' => 0,
                'prize' => 200000,
                'odds' => 12629232,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],

            // MATCH_N = 4
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 4,
                'match_b' => 1,
                'prize' => 2000,
                'odds' => 893761.03,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 4,
                'match_b' => 0,
                'prize' => 100,
                'odds' => 38859.18,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],

            // MATCH_N = 3
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 3,
                'match_b' => 1,
                'prize' => 40,
                'odds' => 13965.02,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 3,
                'match_b' => 0,
                'prize' => 2,
                'odds' => 607.17,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],

            //MATCH_N = 2
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 2,
                'match_b' => 1,
                'prize' => 2,
                'odds' => 665,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],

            // MATCH_N = 1
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 1,
                'match_b' => 1,
                'prize' => 1.4,
                'odds' => 85.81,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],

            // MATCH_N = 0
            [
                'lottery_type_id' => self::LOTTERY_TYPE_ID,
                'match_n' => 0,
                'match_b' => 1,
                'prize' => 1,
                'odds' => 35.17,
                'type' => Model_Lottery_Type_Data::FIXED,
                'estimated' => 0,
                'is_jackpot' => false,
            ],
        ];

        DB::update('lottery')
            ->set([
                    'price' => 0.5,
                    'is_multidraw_enabled' => 1,
                    'force_currency_id' => Currency::USD,
                ])
                ->where('id', '=', self::LOTTERY_ID)
                ->execute();

        DB::insert('lottery_type')
            ->set([
                'id' => self::LOTTERY_TYPE_ID,
                'lottery_id' => self::LOTTERY_ID,
                'odds' => self::ODDS,
                'ncount' => self::NUMBERS_DRAWN,
                'bcount' => self::BONUS_NUMBERS_DRAWN,
                'nrange' => self::NUMBERS_POOL,
                'brange' => self::BONUS_NUMBERS_POOL,
                'bextra' => 0,
                'def_insured_tiers' => 3,
                'date_start' => Carbon::parse('2025-04-05')->format('Y-m-d'),
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

        /** @todo change price/fee/income in whitelabel_lottery */
    }
}

