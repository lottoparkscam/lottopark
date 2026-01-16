<?php

namespace Fuel\Tasks\Seeders;


use Model_Lottery_Provider;

final class GG_World_Raffle_Closed_New extends Seeder
{
    use \Without_Foreign_Key_Checks;

    private const RAFFLE_ID = 8;
    private const RAFFLE_RULE_ID = 8;
    private const RAFFLE_PROVIDER_ID = 8;
    private const TIMEZONE = 'Europe/Paris';
    private const CURRENCY = Currency::USD;
    private const RAFFLE_NAME = 'GG World Raffle';
    private const RAFFLE_SLUG = 'gg-world-raffle';
    private const IS_ENABLED = 1;

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
                [self::RAFFLE_ID, self::RAFFLE_RULE_ID, self::CURRENCY, 1.5, 0, 100, json_encode([[1, 100]])]
            ],
            'raffle' => [
                [
                    self::RAFFLE_ID, self::RAFFLE_RULE_ID, self::CURRENCY, self::RAFFLE_NAME, 'World', null, self::RAFFLE_SLUG,
                    self::IS_ENABLED, self::TIMEZONE, 100, null, null,
                    null, null, 0, 0,
                    0
                ]
            ],
            'whitelabel_raffle' => [
                [1, self::RAFFLE_ID, 0, 0, true, self::RAFFLE_PROVIDER_ID]
            ],
            'raffle_rule_tier' => [
                [self::RAFFLE_RULE_ID, self::CURRENCY, 'raffle-closed:1', json_encode([1]), 0, 100, 100, 100, 1],
            ],
            'raffle_provider' => [
                [
                    self::RAFFLE_PROVIDER_ID,
                    self::RAFFLE_ID, # raffle_id
                    Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER, # provider, 3 means LCS
                    1, # min_bets
                    100, # max_bets
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
}
