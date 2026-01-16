<?php

namespace Fuel\Tasks\Seeders;


use Model_Lottery_Provider;

final class Raffle_Lotteryking_Closed extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production;

    const RAFFLE_ID = 2;
    const RAFFLE_RULE_ID = 2;
    const RAFFLE_DRAW_ID = 2;
    const RAFFLE_RAFFLE_ID = 2;
    const RAFFLE_PROVIDER_ID = 2;
    const RAFFLE_MAX_BETS = 10000;
    /**
     * Tables disabled on production.
     * @var string[]
     */
    private $disabled_tables_on_production = [
        'whitelabel_raffle'
    ];

    protected function columnsStaging(): array
    {
        return [
            'raffle_rule' => [
                'id', 'raffle_id', 'currency_id', 'line_price', 'fee', 'max_lines_per_draw', 'ranges'
            ],
            'raffle' => [
                'id', 'raffle_rule_id', 'currency_id', 'name', 'country', 'country_iso', 'slug',
                'is_enabled', 'timezone', 'main_prize', 'last_draw_date', 'last_draw_date_utc',
                'next_draw_date', 'next_draw_date_utc', 'last_prize_total', 'draw_lines_count',
                'last_ticket_count'
            ],
            'whitelabel_raffle' => [
                'whitelabel_id', 'raffle_id', 'income', 'income_type', 'is_enabled', 'raffle_provider_id'
            ],
            'raffle_rule_tier' => [
                'raffle_rule_id', 'currency_id', 'slug', 'matches', 'prize_type', 'prize_fund_percent', 'lottery_rule_tier_in_kind_prize_id',
                'odds', 'prize', 'is_main_prize'
            ],
            'raffle_provider' => [
                'id', 'raffle_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'data'
            ],
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'raffle_rule' => [
                [self::RAFFLE_RULE_ID, self::RAFFLE_ID, Currency::USD, 10, 0, self::RAFFLE_MAX_BETS, json_encode([[1, self::RAFFLE_MAX_BETS]])]
            ],
            'raffle' => [
                [
                    self::RAFFLE_ID,
                    self::RAFFLE_RULE_ID,
                    Currency::USD,
                    'Lottery King Raffle',
                    'World',
                    null,
                    'lottery-king-raffle',
                    1,
                    'Asia/Seoul',
                    45000,
                    null,
                    null,
                    null,
                    null,
                    0,
                    0,
                    0
                ],
            ],
            'whitelabel_raffle' => [
                [1, self::RAFFLE_ID, 0, 0, true, self::RAFFLE_PROVIDER_ID]
            ],
            'raffle_rule_tier' => [
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:1', json_encode([1]), 0, 55.36, Raffle_Lotteryking_Prize_In_Kind::TESLA_ID, 10000, 40000, 1],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:2_11', json_encode([[2, 11]]), 0, 23.53, Raffle_Lotteryking_Prize_In_Kind::CHAIR_ID, 1000.00, 1700, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:12_21', json_encode([[12, 21]]), 0, 2.77, Raffle_Lotteryking_Prize_In_Kind::GINSENG_ID, 1000.00, 200, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:22_41', json_encode([[22, 41]]), 0, 2.77, Raffle_Lotteryking_Prize_In_Kind::HAIR_FORMULA_ID, 500, 100, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:42_71', json_encode([[42, 71]]), 0, 2.08, Raffle_Lotteryking_Prize_In_Kind::VITAMIN_SET_ID, 333.33, 75, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:72_101', json_encode([[72, 101]]), 0, 1.04, Raffle_Lotteryking_Prize_In_Kind::PORK_CUTLET_ID, 333.33, 25, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:102_501', json_encode([[102, 501]]), 0, 5.54, null, 25.00, 10, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:502_1501', json_encode([[502, 1501]]), 0, 6.92, null, 10.00, 5, 0],
            ],
            'raffle_provider' => [
                [
                    self::RAFFLE_PROVIDER_ID,
                    self::RAFFLE_ID, # raffle_id
                    Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER, # provider, 3 means LCS
                    1, # min_bets
                    self::RAFFLE_MAX_BETS, # max_bets
                    0, # multiplier
                    null, # closing_time
                    'Asia/Seoul', # timezone
                    0, # offset
                    0, # tax
                    0, # tax_min
                    null, # data
                ]
            ],
        ];
    }
}
