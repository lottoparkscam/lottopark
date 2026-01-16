<?php

namespace Fuel\Tasks\Seeders;


use Model_Lottery_Provider;

final class Luminaria_Raffle extends Seeder
{
    use \Without_Foreign_Key_Checks;

    const RAFFLE_ID = 9;
    const RAFFLE_RULE_ID = 9;
    const RAFFLE_PROVIDER_ID = 9;

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
                [self::RAFFLE_RULE_ID, self::RAFFLE_ID, Currency::USD, 4, 0, 30000, json_encode([[1, 30000]])]
            ],
            'raffle' => [
                [
                    self::RAFFLE_ID, self::RAFFLE_RULE_ID, Currency::USD, 'Luminaria Raffle', 'World', null, 'luminaria-raffle', 1,
                    'UTC', 65000, null, null, null, null, 0, 0, 0
                ]
            ],
            'whitelabel_raffle' => [
                [1, self::RAFFLE_ID, 0, 0, true, self::RAFFLE_PROVIDER_ID]
            ],
            'raffle_rule_tier' => [
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:1', json_encode([1]), 0, 54.17, 30000, 65000, 1],
            ],
            'raffle_provider' => [
                [
                    self::RAFFLE_PROVIDER_ID,
                    self::RAFFLE_ID, # raffle_id
                    Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER, # provider, 3 means LCS
                    1, # min_bets
                    30000, # max_bets
                    0, # multiplier
                    null, # closing_time
                    'UTC', # timezone
                    0, # offset
                    0, # tax
                    0, # tax_min
                    null, # data
                ]
            ],
        ];
    }
}
