<?php

namespace Fuel\Tasks\Seeders;

use Fuel\Tasks\Seeders\Faireum_Raffle_Two_Closed_Prize_In_Kind as KindSeeder;
use Model_Lottery_Provider;
use Models\Whitelabel;


final class Faireum_Raffle_Two_Closed extends Seeder
{
    use \Without_Foreign_Key_Checks,
        \Without_Tables_On_Production;

    const RAFFLE_ID = 6;
    const RAFFLE_RULE_ID = 6;
    const RAFFLE_PROVIDER_ID = 6;

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
                'raffle_rule_id', 'currency_id', 'slug', 'matches', 'prize_type', 'prize_fund_percent',
                'odds', 'prize', 'is_main_prize', 'lottery_rule_tier_in_kind_prize_id'
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
                [self::RAFFLE_ID, self::RAFFLE_RULE_ID, Currency::USD, 10, 0, 1000, json_encode([[1, 1000]])]
            ],
            'raffle' => [
                [
                    self::RAFFLE_ID, self::RAFFLE_RULE_ID, Currency::USD, 'Faireum Raffle', 'Singapore', 'SG', 'faireum-raffle-new',
                    0, 'Asia/Singapore', 325, null, null,
                    null, null, 0, 0,
                    0
                ]
            ],
            'whitelabel_raffle' => [
                [1, self::RAFFLE_ID, 0, 0, true, self::RAFFLE_PROVIDER_ID]
            ],
            'raffle_rule_tier' => [
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:1', json_encode([1]), 0, 14.931544610861, 1000, 325, 1, KindSeeder::POWERBALL],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:2_11', json_encode([[2, 11]]), 0, 14.4721124689883, 100, 31.5, 0, KindSeeder::MEGAMILIONS],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:12_31', json_encode([[12, 31]]), 0, 8.26977855370762, 50, 9, 0, KindSeeder::GGWORLDX],
                [self::RAFFLE_RULE_ID, Currency::USD, 'raffle-closed:32_1000', json_encode([[32, 1000]]), 0, 62.3265643664431, 1.03, 1.4, 0, KindSeeder::GGWORLDMILION],
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
                    'Asia/Singapore', # timezone
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
                'theme' => 'faireum'
            ]
        ]);

        $rows_staging = $this->rowsStaging();
        $rows_staging['whitelabel_raffle'] = [
            [$whitelabel->id, self::RAFFLE_ID, 0, 0, true, self::RAFFLE_PROVIDER_ID]
        ];

        return $rows_staging;
    }
}
