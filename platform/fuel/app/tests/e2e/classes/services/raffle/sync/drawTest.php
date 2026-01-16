<?php

use Fuel\Core\DB;
use Models\Raffle;
use Fuel\Core\Config;

class Tests_E2e_Services_Raffle_Sync_Draw extends Test_Feature
{
    private const RAFFLE_SLUG = 'gg-world-raffle';
    private const RAFFLE_TYPE = 'closed';

    private const FIELDS_TO_BE_UPDATED = ['last_draw_date', 'last_draw_date_utc', 'last_prize_total', 'last_ticket_count'];

    protected $in_transaction = false; # todo st: must be in transaction due MySQL exception...

    /** Services_Raffle_Sync_Draw */
    private $draw_sync_service;

    /** @var Services_Lcs_Draws_Contract */
    private $draws_api;

    /** @var RafflePrize */
    private $prize_dao;

    /** @var Raffle */
    private $raffle_dao;

    public function setUp(): void
    {
        parent::setUp();

        $this->skip_on_production_or_staging_env();

        if (Config::get('mock_lcs')) {
            $this->skip_due_no_expected_data_retrieved('Test has been skipped due LCS is mocked and this case is not covered.');
        }

        /*
        note ST: this is why in my opinion we should use separated DB for testing
        in case that somebody has some dummy working instance, we'll truncate it
        or the worst scenario, user will test on testing instance
        and prepared that will not be "testable" for this case.
        */
        DB::query('
        SET FOREIGN_KEY_CHECKS =false;
        TRUNCATE raffle_draw;
        TRUNCATE raffle_prize;
        SET FOREIGN_KEY_CHECKS =true;
        ')->execute();

        $db_update_query = DB::update('raffle');
        $db_update_query->set([
            'last_draw_date' => null,
            'last_draw_date_utc' => null,
            'last_prize_total' => 0,
            'last_ticket_count' => 0,
        ]);
        $db_update_query->execute();

        $container = Container::forge();
        $this->draw_sync_service = $container->get(Services_Raffle_Sync_Draw::class);
        $this->draws_api = $container->get(Services_Lcs_Draws_Contract::class);
        $this->prize_dao = $container->get(RafflePrize::class);
        $this->raffle_dao = $container->get(Raffle::class);
    }

    /**
     * It calls API to get last 10 draws,
     * iterates throw WL DB and checks given draw_no exists,
     * if no then new draw with prizes is created,
     * raffle data is updated,
     * All errors and successes are store in LOG db.
     *
     * @throws Throwable
     */
    public function test_it_synchronizes_data(): void
    {
        $raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule(self::RAFFLE_SLUG)->to_array();
        $draws_api_result = $this->draws_api->request(self::RAFFLE_SLUG, self::RAFFLE_TYPE)->get_body();
        $this->draw_sync_service->synchronize(self::RAFFLE_SLUG, self::RAFFLE_TYPE);

        if (empty($draws_api_result)) {
            $this->skip_due_no_expected_data_retrieved();
        }
        foreach ($draws_api_result as $draw_data) {
            foreach ($draw_data['lottery_prizes'] as $prize_data) {
                $prizes = $this->find_by_prize_and_draw($prize_data['per_user'], $draw_data['draw_no']);
                $this->assertNotEmpty($prizes);
                $this->assertCount(1, $prizes);
            }
        }

        $updated_raffle = $this->raffle_dao->get_by_slug_with_currency_and_rule(self::RAFFLE_SLUG);

        foreach (self::FIELDS_TO_BE_UPDATED as $field_name) {
            $this->assertNotSame($raffle[$field_name], $updated_raffle[$field_name]);
        }
    }

    private function find_by_prize_and_draw(float $per_user, int $draw_no): array
    {
        return $this->prize_dao->push_criterias([
            new Model_Orm_Criteria_With_Relation('draw'),
            new Model_Orm_Criteria_Where('per_user', $per_user),
            new Model_Orm_Criteria_Where('draw.draw_no', $draw_no),
        ])->get_results();
    }
}
