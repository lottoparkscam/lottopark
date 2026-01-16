<?php

use Fuel\Core\DB;

class Tests_E2e_Services_Raffle_Sync_Draw_Lister extends Test_Feature
{
    private const RAFFLE_SLUG = 'gg-world-raffle';
    private const RAFFLE_TYPE = 'closed';

    public function setUp(): void
    {
        parent::setUp();

        $this->skip_on_production_or_staging_env();

        DB::query('
        SET FOREIGN_KEY_CHECKS =false;
        TRUNCATE raffle_draw;
        TRUNCATE raffle_prize;
        SET FOREIGN_KEY_CHECKS =true;
        ')->execute();
    }

    public function test_it_returns_all_unsynced_draws(): void
    {
        $container = Container::forge();

        /** @var Services_Raffle_Sync_Draw_Lister $service */
        $service = $container->get(Services_Raffle_Sync_Draw_Lister::class);
        $result = $service->get_all_lcs_unsynchronized_draws(self::RAFFLE_SLUG, self::RAFFLE_TYPE);

        $this->assertIsArray($result);
    }
}
