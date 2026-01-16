<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;



/**
 * Skandinav Lotto seeder.
 */
final class SkandinavLotto extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production;

    const LOTTERY_ID = Helpers_Lottery::SKANDINAV_LOTTO_ID;
    const LOTTERY_SOURCE_ID = 39;
    const LOTTERY_TYPE_ID = 33;
    const LOTTERY_PROVIDER_ID = 43;

    /**
     * Tables disabled on production.
     *
     * @var string[]
     */
    private $disabled_tables_on_production = [
        'whitelabel_lottery'
    ];

    protected function columnsStaging(): array
    {
        return [
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'is_temporarily_disabled', 'playable'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
        ];
    }

    protected function rowsStaging(): array
    {
        [$draw_days, $draw_hour, $timezone] = ['3', '20:25:00', 'Europe/Budapest'];   // TODO: check draw hour
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                // ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'is_temporarily_disabled', 'playable'],
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Skandináv lottó', 'SKL', 'Hungary', 'HU', 'skandinav-lotto', 0, $timezone, $draw_dates_json, 0, Currency::HUF, 0, 0, 0.00, '2020-06-05', 300, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 0, 0],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'skandinavlotto', 'https://bet.szerencsejatek.hu'],
            ],

            // Source: https://bet.szerencsejatek.hu/jatekok/skandinavlotto/leiras
            'lottery_type' => [
                //      ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 28, 7, 0, 35, 0, 0, 2],    // TODO: def_insured_tiers, check odds
            ],

            // Source: https://bet.szerencsejatek.hu/jatekok/skandinavlotto/leiras
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                //      ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
                [self::LOTTERY_TYPE_ID, 7, 0, 30.5, 3362260, 1, 0, 1],   // TODO: check prizes, estimated
                [self::LOTTERY_TYPE_ID, 6, 0, 12.0, 17155, 1, 0, 0],
                [self::LOTTERY_TYPE_ID, 5, 0, 12.0, 424, 1, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 45.5, 30, 1, 0, 0],
            ],

            // Source:
            // ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier',  'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout']
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::FEED, 1, 1, 0, '18:00:00', 'Europe/Budapest', 0, 0, 0, 0, 0],    // TODO: check closing time, fee
            ],
            // ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines']
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '0', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
