<?php

namespace Fuel\Tasks\Seeders;

use Model_Lottery_Provider;
use Models\Raffle;

final class CosmicFateRaffle extends Seeder
{
    use \Without_Foreign_Key_Checks;

    const RAFFLE_ID = 12;
    const RAFFLE_RULE_ID = 12;
    const RAFFLE_PROVIDER_ID = 12;
    const SLUG = Raffle::COSMIC_FATE_RAFFLE_SLUG;
    const NAME = 'Cosmic Fate Raffle';
    const COUNTRY_NAME = 'World';
    const COUNTRY_ISO = null;
    const TIMEZONE = 'UTC';
    const JACKPOT = 37500;
    const TICKET_PRICE = 37.5;
    const TICKET_COUNT = 2000;
    const CURRENCY = Currency::EUR;
    const MAX_LINES_PER_TICKET = 2000;
    const PROVIDER = Model_Lottery_Provider::LOTTERY_CENTRAL_SERVER;

    protected function columnsStaging(): array
    {
        return [
            'raffle_rule' => ['id', 'raffle_id', 'currency_id', 'line_price', 'fee', 'max_lines_per_draw', 'ranges'],
            'raffle' => ['id', 'raffle_rule_id', 'currency_id', 'name', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'main_prize', 'last_draw_date', 'last_draw_date_utc', 'next_draw_date', 'next_draw_date_utc', 'last_prize_total', 'draw_lines_count', 'last_ticket_count'],
            'whitelabel_raffle' => ['whitelabel_id', 'raffle_id', 'income', 'income_type', 'is_enabled', 'raffle_provider_id', 'is_bonus_balance_in_use', 'is_margin_calculation_enabled'],
            'raffle_rule_tier' => ['raffle_rule_id', 'currency_id', 'slug', 'matches', 'prize_type', 'prize_fund_percent', 'odds', 'prize', 'is_main_prize'],
            'raffle_provider' => ['id', 'raffle_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'data'],
        ];
    }

    protected function rowsStaging(): array
    {
        return [
            'raffle_rule' => [
                [
                    'id' => self::RAFFLE_RULE_ID,
                    'raffle_id' => self::RAFFLE_ID,
                    'currency_id' => self::CURRENCY,
                    'line_price' => self::TICKET_PRICE,
                    'fee' => 0,
                    'max_lines_per_draw' => self::MAX_LINES_PER_TICKET,
                    'ranges' => json_encode([[1, self::TICKET_COUNT]]),
                ]
            ],
            'raffle' => [
                [
                    'id' => self::RAFFLE_ID,
                    'raffle_rule_id' => self::RAFFLE_RULE_ID,
                    'currency_id' => self::CURRENCY,
                    'name' => self::NAME,
                    'country' => self::COUNTRY_NAME,
                    'country_iso' => self::COUNTRY_ISO,
                    'slug' => self::SLUG,
                    'is_enabled' => 1,
                    'timezone' => self::TIMEZONE,
                    'main_prize' => self::JACKPOT,
                    'last_draw_date' => null,
                    'last_draw_date_utc' => null,
                    'next_draw_date' => null,
                    'next_draw_date_utc' => null,
                    'last_prize_total' => 0,
                    'draw_lines_count' => 0,
                    'last_ticket_count' => 0,
                ]
            ],
            'whitelabel_raffle' => [
                [
                    'whitelabel_id' => 1,
                    'raffle_id' => self::RAFFLE_ID,
                    'income' => 0,
                    'income_type' => 0,
                    'is_enabled' => true,
                    'raffle_provider_id' => self::RAFFLE_PROVIDER_ID,
                    'is_bonus_balance_in_use' => false,
                    'is_margin_calculation_enabled' => false,
                ]
            ],
            'raffle_rule_tier' => [
                [
                    'raffle_rule_id' => self::RAFFLE_RULE_ID,
                    'currency_id' => self::CURRENCY,
                    'slug' => 'raffle-closed:1',
                    'matches' => json_encode([1]),
                    'prize_type' => 0,
                    'prize_fund_percent' => 84.75,
                    'odds' => 2000,
                    'prize' => self::JACKPOT,
                    'is_main_prize' => 1,
                ],
                [
                    'raffle_rule_id' => self::RAFFLE_RULE_ID,
                    'currency_id' => self::CURRENCY,
                    'slug' => 'raffle-closed:2_10',
                    'matches' => json_encode([[2, 10]]),
                    'prize_type' => 0,
                    'prize_fund_percent' => 15.25,
                    'odds' => 222.22,
                    'prize' => 750,
                    'is_main_prize' => 0,
                ],
            ],
            'raffle_provider' => [
                [
                    'id' => self::RAFFLE_PROVIDER_ID,
                    'raffle_id' => self::RAFFLE_ID,
                    'provider' => self::PROVIDER,
                    'min_bets' => 1,
                    'max_bets' => self::MAX_LINES_PER_TICKET,
                    'multiplier' => 0,
                    'closing_time' => null,
                    'timezone' => self::TIMEZONE,
                    'offset' => 0,
                    'tax' => 0,
                    'tax_min' => 0,
                    'data' => null,
                ]
            ],
        ];
    }
}
