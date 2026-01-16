<?php

namespace Fuel\Tasks\Seeders;


use Model_Lottery_Provider;
use Models\Whitelabel;

final class Raffle_Happy_Together_Vietnam_Closed extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production;

    const RAFFLE_ID = 5;
    const RAFFLE_RULE_ID = 5;
    const RAFFLE_PROVIDER_ID = 5;
    const TIMEZONE = 'Asia/Phnom_Penh';
    const CURRENCY = Currency::USD;

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
                'raffle_rule_id', 'currency_id', 'slug', 'matches', 'prize_type', 'prize_fund_percent',
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
                [self::RAFFLE_RULE_ID, self::RAFFLE_ID, self::CURRENCY, 10, 0, 1000, json_encode([[1, 1000]])]
            ],
            'raffle' => [
                [
                    self::RAFFLE_ID, self::RAFFLE_RULE_ID, self::CURRENCY, 'Happy Together Vietnam', 'World', '', 'happy-together-vietnam',
                    1, self::TIMEZONE, 1000, null, null,
                    null, null, 0, 0,
                    0
                ]
            ],
            'whitelabel_raffle' => [
                [1, self::RAFFLE_ID, 0, 0, true, self::RAFFLE_PROVIDER_ID]
            ],
            'raffle_rule_tier' => [
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:1', json_encode([1]), 0, 20, 1000, 1000, 1],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:2_3', json_encode([[2, 3]]), 0, 10, 500, 250, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:4_5', json_encode([[4, 5]]), 0, 6, 500, 150, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:6_7', json_encode([[6, 7]]), 0, 4, 500, 100, 0],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:8_307', json_encode([[8, 307]]), 0,60, 3.3333333333333333, 10, 0]
            ],
            'raffle_provider' => [
                [
                    self::RAFFLE_PROVIDER_ID,
                    self::RAFFLE_ID, # raffle_id
                    Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER, # provider, 3 means LCS
                    1, # min_bets
                    1000, # max_bets
                    0, # multiplier
                    null, # closing_time
                    self::TIMEZONE, # timezone
                    0, # offset
                    0, # tax
                    0, # tax_min
                    null, # data
                ]
            ],
        ];
    }

    protected function rowsProduction(): array
    {
        $whitelabel = Whitelabel::find('first', [
            'where' => [
                'theme' => 'lotteryking'
            ]
        ]);

        $rows = $this->rowsStaging();
        $rows['whitelabel_raffle'] = [
            [$whitelabel->id, self::RAFFLE_ID, 0, 0, true, self::RAFFLE_PROVIDER_ID]
        ];

        return $rows;
    }
}
