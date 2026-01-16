<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Model_Lottery_Provider;
use Helpers_Time;



/**
 * Lotto Multi Multi seeder.
 */
final class LottoMultiMulti extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production;

    const LOTTERY_ID = Helpers_Lottery::LOTTO_MULTI_MULTI_ID;
    const LOTTERY_SOURCE_ID = 40;
    const LOTTERY_TYPE_ID = 34;
    const LOTTERY_PROVIDER_ID = 44;

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
        [$draw_days, $draw_hour, $timezone] = ['1,2,3,4,5,6,7', '21:50:00', 'Europe/Warsaw'];   // TODO: check draw hour 14:00, 21:50
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                // ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'is_temporarily_disabled', 'playable',
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Lotto Multi Multi', 'MM', 'Poland', 'PL', 'multi-multi', 0, $timezone, '["Mon 14:00", "Mon 21:50", "Tue 14:00", "Tue 21:50", "Wed 14:00", "Wed 21:50", "Thu 14:00", "Thu 21:50", "Fri 14:00", "Fri 21:50", "Sat 14:00", "Sat 21:50", "Sun 14:00", "Sun 21:50"]', 0, Currency::PLN, 0, 0, 0.00, '2020-08-06', 10, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 0, 0,
                ],
            ],
            'lottery_source' => [
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'https://www.lotto.pl/multi-multi', 'https://www.lotto.pl/multi-multi'],
            ],

            // Source: 
            'lottery_type' => [
                //      ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 100, 20, 1, 80, 80, 0, 1],    // TODO: def_insured_tiers, check odds
            ],

            // Source: http://megalotto.pl/prawdopodobienstwo-wygranej/multi-multi
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                //      ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
                [self::LOTTERY_TYPE_ID, 10, 1, 2500000.0, 17823422, 0, 0, 1],
                [self::LOTTERY_TYPE_ID, 10, 0, 250000.0, 8911711, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 9, 1, 300000.0, 3068195, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 9, 0, 70000.0, 1380688, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 8, 1, 130000.0, 575287, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 8, 0, 22000.0, 230115, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 7, 1, 22000.0, 117084, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 7, 0, 6000.0, 40979, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 6, 1, 4300.0, 25843, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 6, 0, 1300.0, 7753, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 5, 1, 1800.0, 6202, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 5, 0, 700.0, 1551, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 1, 384.0, 1632, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 84.0, 326, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 1, 214.0, 480, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 54.0, 72, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 2, 1, 120.0, 166, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 2, 0, 16.0, 17, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 1, 1, 88.0, 80, 0, 0, 0],
                [self::LOTTERY_TYPE_ID, 1, 0, 4.0, 4, 0, 0, 0],
            ],

            // Source:
            // ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier',  'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout']
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::FEED, 1, 1, 0, '20:00:00', 'Europe/Warsaw', 0, 0, 0, 0, 0],    // TODO: check closing time, tax, fee
            ],
            // ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines']
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '0', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
