<?php

namespace Fuel\Tasks\Seeders;

use Helpers_Lottery;
use Helpers_Time;
use Model_Lottery_Provider;


/**
 * Lotto AT seeder.
 */
final class LottoAT extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production,
        \Without_Tables_On_Staging;

    const LOTTERY_ID = Helpers_Lottery::LOTTO_AT_ID;
    const LOTTERY_SOURCE_ID = 37;
    const LOTTERY_TYPE_ID = 31;
    const LOTTERY_PROVIDER_ID = 41;

    /**
     * Tables disabled on production.
     *
     * @var string[]
     */
    private $disabled_tables_on_production = [
        'whitelabel_lottery'
    ];

    private $disabled_tables_on_staging = [
        'whitelabel_lottery'
    ];

    protected function columnsStaging(): array
    {
        return [
            'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'scans_enabled', 'is_multidraw_enabled'],
            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
            'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
            'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
            'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier', 'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout', 'closing_times'],
            'whitelabel_lottery' => ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
        ];
    }

    protected function rowsStaging(): array
    {
        // Source: https://www.lottoland.com/nz/austrian-lottery/help
        [$draw_days, $draw_hour, $timezone] = ['3,7', '19:15:00', 'Europe/Vienna'];  //TODO: double check draw hour (18:48, 19:15)
        $draw_dates_json = Helpers_Time::generate_draw_days_json($draw_days, $draw_hour);
        $draw_dates = Helpers_Lottery::calculate_draw_datetimes($draw_dates_json, $timezone);

        return [
            'lottery' => [
                // 'lottery' => ['id', 'source_id', 'name', 'shortname', 'country', 'country_iso', 'slug', 'is_enabled', 'timezone', 'draw_dates', 'draw_jackpot_set', 'currency_id', 'last_total_prize', 'last_total_winners', 'last_jackpot_prize', 'last_update', 'price', 'estimated_updated', 'next_date_local', 'next_date_utc', 'last_date_local', 'scans_enabled', 'is_multidraw_enabled'],
                [self::LOTTERY_ID, self::LOTTERY_SOURCE_ID, 'Lotto Austria', 'AT', 'Austria', 'AT', 'lotto-at', 1, $timezone, $draw_dates_json, 0, Currency::EUR, 0, 0, 0.00, '2020-06-30', 1.20, 0, $draw_dates['next_date_local'], $draw_dates['next_date_utc'], $draw_dates['last_date_local'], 1, 1],
            ],
            'lottery_source' => [
//                            'lottery_source' => ['id', 'lottery_id', 'name', 'website'],
                [self::LOTTERY_SOURCE_ID, self::LOTTERY_ID, 'LTECH API', 'LTECH API'],
            ],
            // Source: https://www.lottoland.co.uk/magazine/learn-which-game-gives-you-the-best-lotto-odds.html
            // https://www.lottoland.com/en/austrian-lottery/help
            'lottery_type' => [
                //             'lottery_type' => ['id', 'lottery_id', 'odds', 'ncount', 'bcount', 'nrange', 'brange', 'bextra', 'def_insured_tiers'],
                [self::LOTTERY_TYPE_ID, self::LOTTERY_ID, 12, 6, 0, 45, 0, 1, 3],  // TODO: check def_insured_tiers
            ],
            // Source: https://www.lottoland.com/en/austrian-lottery/help
            // https://www.smv.at/fileadmin/01_spielerschutz/Oesterreichische_lotterien/downloads/english/LOTTO.pdf
            'lottery_type_data' => [
                // prize, estimated based on type
                // JACKPOT: prize = 0, estimated = prize
                // FIXED: prize = prize, estimated = 0
                // 1 - PARIMUTUEL: prize = prize_fund_percent, estimated = prize
                //             'lottery_type_data' => ['lottery_type_id', 'match_n', 'match_b', 'prize', 'odds', 'type', 'estimated', 'is_jackpot'],
                [self::LOTTERY_TYPE_ID, 6, 0, 0.4, 8145060, 1, 0, 1],
                [self::LOTTERY_TYPE_ID, 5, 1, 0.055, 1357510, 1, 182762.20, 0],
                [self::LOTTERY_TYPE_ID, 5, 0, 0.06, 35724.9, 1, 1401.50, 0],
                [self::LOTTERY_TYPE_ID, 4, 1, 0.018, 14290.6, 1, 185.30, 0],
                [self::LOTTERY_TYPE_ID, 4, 0, 0.1, 772.4, 1, 51.60, 0],
                [self::LOTTERY_TYPE_ID, 3, 1, 0.045, 579.3, 1, 16.20, 0],
                [self::LOTTERY_TYPE_ID, 3, 0, 0.181, 48.3, 1, 5.50, 0],
                [self::LOTTERY_TYPE_ID, 0, 1, 1.20, 16.2, 0, 0, 0],

            ],
            //             'lottery_provider' => ['id', 'lottery_id', 'provider', 'min_bets', 'max_bets', 'multiplier',  'closing_time', 'timezone', 'offset', 'tax', 'tax_min', 'fee', 'max_payout', 'closing_times']
            'lottery_provider' => [
                [self::LOTTERY_PROVIDER_ID, self::LOTTERY_ID, Model_Lottery_Provider::LOTTORISQ, 1, 12, 0, "18:00", 'Europe/Vienna', 0, 0, 0, 0.1, 0, '{"3":"18:30:00", "7":"18:00:00"}'],  // check closing times
            ],
            //  ['whitelabel_id', 'lottery_id', 'lottery_provider_id', 'is_enabled', 'model', 'income', 'income_type', 'tier', 'volume', 'min_lines'],
            'whitelabel_lottery' => [
                ['1', self::LOTTERY_ID, self::LOTTERY_PROVIDER_ID, '1', '0', '1.00', '0', '0', '1000', '1'],
            ],
        ];
    }
}
