<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Container;
use Fuel\Core\DB;
use Models\WhitelabelLotteryPurchaseLimit;
use Repositories\WhitelabelLotteryPurchaseLimitRepository;
use Stwarog\UowFuel\FuelEntityManager;
use Test_Feature;

/**
 * @runTestsInSeparateProcesses to ensure database entries are cleared between each test.
 * @preserveGlobalState disabled
 * Not using fixtures here, as there is a problem with lottery -> lottery_source - both models reference each other ID
 */
class WhitelabelLotteryPurchaseLimitRepositoryTest extends Test_Feature
{
    private WhitelabelLotteryPurchaseLimitRepository $whitelabelLotteryPurchaseLimitRepository;
    private FuelEntityManager $fuelEntityManager;

    public function setUp(): void
    {
        parent::setUp();
        $this->fuelEntityManager = Container::get(FuelEntityManager::class);
        $this->whitelabelLotteryPurchaseLimitRepository = new WhitelabelLotteryPurchaseLimitRepository(new WhitelabelLotteryPurchaseLimit(), $this->fuelEntityManager);
    }

    /** @test */
    public function findOneByUserIdAndWhitelabelLotteryId_VerifyCorrectAndIncorrectCases(): void
    {
        // Workaround for a fixture
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();

        // Persist entry with all data
        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->id = 10;
        $whitelabelLotteryPurchaseLimit->whitelabelUserId = 11;
        $whitelabelLotteryPurchaseLimit->whitelabelLotteryId = 3;
        $whitelabelLotteryPurchaseLimit->counter = 5;
        $whitelabelLotteryPurchaseLimit->createdAt = Carbon::now();
        $whitelabelLotteryPurchaseLimit->updatedAt = Carbon::now();
        $whitelabelLotteryPurchaseLimit->save();

        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($whitelabelLotteryPurchaseLimit->whitelabelUserId, $whitelabelLotteryPurchaseLimit->whitelabelLotteryId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->id, $result->id);

        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId(100, $whitelabelLotteryPurchaseLimit->whitelabelLotteryId);
        $this->assertEmpty($result);

        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($whitelabelLotteryPurchaseLimit->whitelabelUserId, 100);
        $this->assertEmpty($result);

        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId(100, 100);
        $this->assertEmpty($result);
    }

    /** @test */
    public function insertOrUpdateEntries_FailsToAddWithoutDisabledForeignKeyChecks(): void
    {
        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->whitelabelUserId = 11;
        $whitelabelLotteryPurchaseLimit->whitelabelLotteryId = 3;
        $whitelabelLotteryPurchaseLimit->counter = 5;

        $isSaved = $this->whitelabelLotteryPurchaseLimitRepository->insertOrUpdateEntries([$whitelabelLotteryPurchaseLimit]);
        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($whitelabelLotteryPurchaseLimit->whitelabelUserId, $whitelabelLotteryPurchaseLimit->whitelabelLotteryId);
        $this->assertFalse($isSaved);
        $this->assertEmpty($result);
    }

    /** @test */
    public function insertOrUpdateEntries_AddNewEntry(): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();

        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->whitelabelUserId = 11;
        $whitelabelLotteryPurchaseLimit->whitelabelLotteryId = 3;
        $whitelabelLotteryPurchaseLimit->counter = 5;

        $isSaved = $this->whitelabelLotteryPurchaseLimitRepository->insertOrUpdateEntries([$whitelabelLotteryPurchaseLimit]);
        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($whitelabelLotteryPurchaseLimit->whitelabelUserId, $whitelabelLotteryPurchaseLimit->whitelabelLotteryId);

        $this->assertTrue($isSaved);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->whitelabelUserId, $result->whitelabelUserId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->whitelabelLotteryId, $result->whitelabelLotteryId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->counter, $result->counter);
    }

    /** @test */
    public function insertOrUpdateEntries_AddNewEntries(): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();

        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->whitelabelUserId = 11;
        $whitelabelLotteryPurchaseLimit->whitelabelLotteryId = 3;
        $whitelabelLotteryPurchaseLimit->counter = 5;

        $whitelabelLotteryPurchaseLimit2 = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit2->whitelabelUserId = 11;
        $whitelabelLotteryPurchaseLimit2->whitelabelLotteryId = 5;
        $whitelabelLotteryPurchaseLimit2->counter = 1;

        $isSaved = $this->whitelabelLotteryPurchaseLimitRepository->insertOrUpdateEntries([$whitelabelLotteryPurchaseLimit, $whitelabelLotteryPurchaseLimit2]);
        $this->assertTrue($isSaved);

        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($whitelabelLotteryPurchaseLimit->whitelabelUserId, $whitelabelLotteryPurchaseLimit->whitelabelLotteryId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->whitelabelUserId, $result->whitelabelUserId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->whitelabelLotteryId, $result->whitelabelLotteryId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->counter, $result->counter);

        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($whitelabelLotteryPurchaseLimit2->whitelabelUserId, $whitelabelLotteryPurchaseLimit2->whitelabelLotteryId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit2->whitelabelUserId, $result->whitelabelUserId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit2->whitelabelLotteryId, $result->whitelabelLotteryId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit2->counter, $result->counter);
    }

    /** @test */
    public function insertOrUpdateEntries_EntryExists_UpdateCounterAndDate(): void
    {
        DB::query('SET FOREIGN_KEY_CHECKS=0;')
            ->execute();

        $whitelabelLotteryPurchaseLimit = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit->id = 10;
        $whitelabelLotteryPurchaseLimit->whitelabelUserId = 11;
        $whitelabelLotteryPurchaseLimit->whitelabelLotteryId = 3;
        $whitelabelLotteryPurchaseLimit->counter = 5;
        $whitelabelLotteryPurchaseLimit->createdAt = Carbon::now();
        $whitelabelLotteryPurchaseLimit->updatedAt = Carbon::now();
        $whitelabelLotteryPurchaseLimit->save();

        $whitelabelLotteryPurchaseLimit2 = new WhitelabelLotteryPurchaseLimit();
        $whitelabelLotteryPurchaseLimit2->whitelabelUserId = 11;
        $whitelabelLotteryPurchaseLimit2->whitelabelLotteryId = 3;
        $whitelabelLotteryPurchaseLimit2->counter = 2;

        Carbon::setTestNow('2020-05-01 15:00:00'); // // 2001-03-10 17:16:18 (the MySQL DATETIME format)
        $expectedUpdatedAt = Carbon::now();
        $isSaved = $this->whitelabelLotteryPurchaseLimitRepository->insertOrUpdateEntries([$whitelabelLotteryPurchaseLimit2]);
        $this->assertTrue($isSaved);

        $result = $this->whitelabelLotteryPurchaseLimitRepository->findOneByUserIdAndWhitelabelLotteryId($whitelabelLotteryPurchaseLimit->whitelabelUserId, $whitelabelLotteryPurchaseLimit->whitelabelLotteryId);
        $this->assertEquals($whitelabelLotteryPurchaseLimit->id, $result->id);
        $counterSum = $whitelabelLotteryPurchaseLimit->counter + $whitelabelLotteryPurchaseLimit2->counter;
        $this->assertEquals($counterSum, $result->counter);
        $this->assertEquals($expectedUpdatedAt, $result->updatedAt);
    }
}
