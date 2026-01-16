<?php

namespace Tests\Unit\Classes\Services;

use Helpers\FlashMessageHelper;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\WhitelabelRepository;
use Services\GgrService;
use Services\Logs\FileLoggerService;
use Test_Unit;

class GgrServiceTest extends Test_Unit
{
    private FileLoggerService|MockObject $fileLoggerServiceMock;
    private GgrService $ggrServiceUnderTest;
    private WhitelabelRepository|MockObject $whitelabelRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();
        $this->fileLoggerServiceMock = $this->createMock(FileLoggerService::class);
        $this->whitelabelRepositoryMock = $this->createMock(WhitelabelRepository::class);
        $this->ggrServiceUnderTest = new GgrService($this->fileLoggerServiceMock, $this->whitelabelRepositoryMock);
    }

    /**
     * @test
     * @dataProvider ggrWhitelottoIncomeProvider
     */
    public function calculateWhitelottoGgrIncome(float $ggr, float $whitelabelMargin, float $expectedResult): void
    {
        $result = $this->ggrServiceUnderTest->calculateWhitelottoGgrRoyalties($ggr, $whitelabelMargin);
        $this->assertSame($expectedResult, $result);
    }

    public function ggrWhitelottoIncomeProvider(): array
    {
        return [
            [0.0, 30, 0.0],
            [100, 30, 30],
            [-1123123100, 30, 0.0],
            [1123123100, -30, 0.0],
            [1231.32, 20, 246.27],
            [1231.32123123, 46, 566.41],
            [1231.32123123, 46.28, 569.86],
        ];
    }

    /**
     * @test
     * @dataProvider ggrLotteryProvider
     */
    public function calculateGgr(float $salesTicketSum, float $winTicketPrizeSum, float $expectedResult): void
    {
        $result = $this->ggrServiceUnderTest->calculateGgr($salesTicketSum, $winTicketPrizeSum);
        $this->assertSame($expectedResult, $result);
    }

    public function ggrLotteryProvider(): array
    {
        return [
            [1, 2, 0.0],
            [1, 1, 0.0],
            [-101, 32, 0.0],
            [101, -32, 0.0],
            [10, 2, 8],
            [10.30, 32.213, 0.0],
            [132.49, 99.32, 33.17],
            [99.99, 99.99, 0.0],
            [1130.123, 130.38, 999.75], //1130.123 - 130.38 = 999.742
            [1130.12323123, 130.38123123, 999.75], //1130.12323123 - 130.38123123 = 999.742
            [1130.120, 130.380, 999.74], //If third numbers is 0 don`t round it.
        ];
    }

    /** @test */
    public function calculateGgr_salesTicketSumIsNegative_showWarningFlashMessageAndSendWarningLog(): void
    {
        $this->fileLoggerServiceMock->expects($this->once())
            ->method('warning');

        $result = $this->ggrServiceUnderTest->calculateGgr(-123, 100);
        $this->assertEquals(0, $result);
        $this->assertEquals('Sales ticket sum is negative.', FlashMessageHelper::getLast());
    }

    /** @test */
    public function calculateGgr_winTicketsPrizeSumIsNegative_showWarningFlashMessageAndSendWarningLog(): void
    {
        $this->fileLoggerServiceMock->expects($this->once())
            ->method('warning');

        $result = $this->ggrServiceUnderTest->calculateGgr(123, -100);
        $this->assertEquals(0, $result);
        $this->assertEquals('Win tickets sum is negative.', FlashMessageHelper::getLast());
    }
}
