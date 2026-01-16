<?php

namespace Feature\Helpers\Wordpress;

use Carbon\Carbon;
use Factories\LtechManualDrawFactory;
use Repositories\LotteryRepository;
use Repositories\LtechManualDrawRepository;
use Test_Feature;

class LtechManualDrawRepositoryTest extends Test_Feature
{
    public LtechManualDrawRepository $repositoryUnderTest;
    private LotteryRepository $lotteryRepository;
    private $lottery;
    private LtechManualDrawFactory $ltechManualDrawFactory;

    public function setUp(): void
    {
        parent::setUp();

        $this->repositoryUnderTest = $this->container->get(LtechManualDrawRepository::class);
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->ltechManualDrawFactory = $this->container->get(LtechManualDrawFactory::class);

        $this->lottery = $this->lotteryRepository->findOneBySlug('powerball');

        $dateInTheFuture = Carbon::now('UTC')->addDays();
        $this->lottery->nextDateLocal = $dateInTheFuture->setTimezone($this->lottery->timezone);
        $this->lottery->nextDateUtc = $dateInTheFuture;
    }

    /** @test */
    public function findForNextDraw_notFound(): void
    {
        // When
        $foundManualDraw = $this->repositoryUnderTest->findForNextDraw($this->lottery);

        // Then
        $this->assertNull($foundManualDraw);
    }

    /** @test */
    public function findForNextDraw_emptyNextDate(): void
    {
        // Given
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->save();

        $this->lottery->nextDateLocal = null;
        $this->lottery->save();

        // When
        $foundManualDraw = $this->repositoryUnderTest->findForNextDraw($this->lottery);

        // Then
        $this->assertNull($foundManualDraw);
    }

    /** @test */
    public function findForNextDraw_isProcessed(): void
    {
        // Given
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->isProcessed = true;
        $ltechManualDraw->save();

        // When
        $foundManualDraw = $this->repositoryUnderTest->findForNextDraw($this->lottery);

        // Then
        $this->assertNull($foundManualDraw);
    }

    /** @test */
    public function findForNextDraw_isForOtherDate(): void
    {
        // Given
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->currentDrawDate = Carbon::now('UTC');
        $ltechManualDraw->save();

        // When
        $foundManualDraw = $this->repositoryUnderTest->findForNextDraw($this->lottery);

        // Then
        $this->assertNull($foundManualDraw);
    }

    /** @test */
    public function findForNextDraw_foundCorrectly(): void
    {
        // Given
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->save();

        // When
        $foundManualDraw = $this->repositoryUnderTest->findForNextDraw($this->lottery);

        // Then
        $this->assertEquals($this->lottery->nextDateLocal, $foundManualDraw->currentDrawDate);
    }

    /** @test */
    public function getPendingLotteryIds_correctIds(): void
    {
        // Given
        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->currentDrawDate = (Carbon::now())->addDays(2);
        $ltechManualDraw->lotteryId = 3;
        $ltechManualDraw->isProcessed = true;
        $ltechManualDraw->save();

        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->currentDrawDate = (Carbon::now())->addDays(3);
        $ltechManualDraw->lotteryId = 4;
        $ltechManualDraw->isProcessed = true;
        $ltechManualDraw->save();

        $ltechManualDraw = $this->ltechManualDrawFactory->createLtechManualDraw($this->lottery);
        $ltechManualDraw->currentDrawDate = (Carbon::now())->addDays(4);
        $ltechManualDraw->lotteryId = 5;
        $ltechManualDraw->isProcessed = false;
        $ltechManualDraw->save();

        // When
        $pendingIds = $this->repositoryUnderTest->getPendingLotteryIds();

        // Then
        $this->assertCount(1, $pendingIds);
        $this->assertSame(5, $pendingIds[0]);
    }

    /** @test */
    public function getPendingLotteryIds_emptyArray(): void
    {
        // When
        $pendingIds = $this->repositoryUnderTest->getPendingLotteryIds();

        // Then
        $this->assertEmpty($pendingIds);
    }
}
