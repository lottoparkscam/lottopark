<?php

namespace Tests\Feature\Classes\Orm\Criteria;

use Classes\Orm\Criteria\CriteriaOrderByCustom;
use Classes\Orm\Criteria\Model_Orm_Criteria_Order;
use Fuel\Core\DB;
use Fuel\Tasks\Factory\Utils\Faker;
use Models\SlotGame;
use Repositories\SlotGameRepository;
use Test_Feature;

final class OrderByCustomTest extends Test_Feature
{
    private SlotGame $slotGame;
    private SlotGameRepository $slotGameRepository;
    private Faker $faker;

    public function setUp(): void
    {
        parent::setUp();
        $this->slotGame = $this->container->get(SlotGame::class);
        $this->slotGameRepository = $this->container->get(SlotGameRepository::class);
        $this->faker = $this->container->get(Faker::class);

        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        DB::query('SET FOREIGN_KEY_CHECKS=1;')
            ->execute();
    }

    private function createFakeGame(string $gameName)
    {
        $slotGame = new SlotGame();
        $slotGame->slotProviderId = 1;
        $slotGame->uuid = $this->faker->forge()->name();
        $slotGame->name = $gameName;
        $slotGame->image = $this->faker->forge()->gravatarUrl();
        $slotGame->type = 'slots';
        $slotGame->technology = 'html5';
        $slotGame->provider = 'provider';
        $slotGame->hasLobby = false;
        $slotGame->hasFreespins = false;
        $slotGame->isMobile = false;
        $slotGame->freespinValidUntilFullDay = false;
        $slotGame->save();
    }

    /** @test */
    public function CriteriaOrderByCustom_OneOrderBy_ShouldReturnOrdered(): void
    {
        $gameNames = ['game1', 'game2', 'game3'];
        $expectedOrder = ['game2', 'game1', 'game3'];
        for ($i = 0; $i < 3; $i++) {
            $this->createFakeGame($gameNames[$i]);
        }

        $games = $this->slotGameRepository->pushCriteria(
            new CriteriaOrderByCustom('name', $expectedOrder)
        )->getResults(10);

        for ($i = 0; $i < 3; $i++) {
            $this->assertSame($expectedOrder[$i], $games[$i]->name);
        }
    }


    /** @test */
    public function CriteriaOrderByCustom_WithDefaultOrderBy_ShouldReturnOrdered(): void
    {
        $gameNames = ['game1', 'game2', 'game3', '#a'];
        $expectedOrder = ['game2', 'game1', 'game3', '#a'];
        for ($i = 0; $i < 3; $i++) {
            $this->createFakeGame($gameNames[$i]);
        }

        $games = $this->slotGameRepository->pushCriterias([
            new CriteriaOrderByCustom('name', $expectedOrder),
            new Model_Orm_Criteria_Order('name', 'ASC'),
        ])->getResults(10);

        for ($i = 0; $i < 3; $i++) {
            $this->assertSame($expectedOrder[$i], $games[$i]->name);
        }
    }
}
