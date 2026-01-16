<?php

namespace Tests\Feature\Classes\Services;

use Exceptions\WrongLotteryNumbersException;
use Repositories\LotteryRepository;
use Services\QuickPickService;
use Test_Feature;

class QuickPickServiceTest extends Test_Feature
{
    private QuickPickService $quickPickService;
    private LotteryRepository $lotteryRepository;

    private const LOTTERY_SLUG = 'powerball';

    public function setUp(): void
    {
        parent::setUp();
        $this->quickPickService = $this->container->get(QuickPickService::class);
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
    }

    /** @test */
    public function shouldGetFirstLineFromUser_withoutUrlQuery_returnFalse(): void
    {
        // When
        $actual = $this->quickPickService->shouldGetFirstLineFromUser();

        // Then
        $this->assertFalse($actual);
    }

    /** @test */
    public function shouldGetFirstLineFromUser_withUrlQuery_returnTrue(): void
    {
        // Given
        $this->setInput('GET', [
            'numbers' => [1]
        ]);

        // When
        $actual = $this->quickPickService->shouldGetFirstLineFromUser();

        // Then
        $this->assertTrue($actual);
    }

    /** @test */
    public function getUsersFirstLineNumbers_correctNumbers(): void
    {
        // Given
        $expectedNormalNumbers = [1,2,3,4,5];
        $expectedBonusNumbers = [6];

        $this->setInput('GET', [
            'numbers' => '1,2,3,4,5',
            'bnumbers' => '6',
        ]);

        $lottery = $this->lotteryRepository->findOneBySlug(self::LOTTERY_SLUG);

        // When
        $actualNumbers = $this->quickPickService->getUsersFirstLineNumbers($lottery);
        ['normalNumbers' => $actualNormalNumbers, 'bonusNumbers' => $actualBonusNumbers] = $actualNumbers;

        // Then
        $this->assertSame($expectedNormalNumbers, $actualNormalNumbers);
        $this->assertSame($expectedBonusNumbers, $actualBonusNumbers);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function getUsersFirstLineNumbers_missingNumbers(): void
    {
        // Given - empty GET data
        $lottery = $this->lotteryRepository->findOneBySlug(self::LOTTERY_SLUG);

        // Expected
        $this->expectException(WrongLotteryNumbersException::class);

        // When
        $this->quickPickService->getUsersFirstLineNumbers($lottery);
    }
}
