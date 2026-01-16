<?php

namespace Tests\Unit\Classes\Services;

use Services\LotteryAdditionalDataService;
use Test_Unit;

class LotteryAdditionalDataServiceTest extends Test_Unit
{
    private LotteryAdditionalDataService $lineServices;
    private const SUPER_ADDITIONAL_DATA = 'a:1:{s:5:"super";i:5;}';
    private const REFUND_ADDITIONAL_DATA = 'a:1:{s:6:"refund";i:5;}';
    private const EMPTY_ADDITIONAL_DATA = 'a:0:{}';

    public function setUp(): void
    {
        parent::setUp();
        $this->lineServices = new LotteryAdditionalDataService();
    }

    /** @test */
    public function isSetSuperAdditionalData_isSet(): void
    {
        $additionalData = unserialize(self::SUPER_ADDITIONAL_DATA);
        $result = $this->lineServices->isSetSuperAdditionalData($additionalData);
        $this->assertTrue($result);
    }

    /** @test */
    public function isSetSuperAdditionalData_isNotSet(): void
    {
        $result = $this->lineServices->isSetSuperAdditionalData([]);
        $this->assertFalse($result);
    }

    /** @test */
    public function isSetRefundAdditionalData_isSet(): void
    {
        $additionalData = unserialize(self::REFUND_ADDITIONAL_DATA);
        $result = $this->lineServices->isSetRefundAdditionalData($additionalData);
        $this->assertTrue($result);
    }

    /** @test */
    public function isSetRefundAdditionalData_isNotSet(): void
    {
        $result = $this->lineServices->isSetRefundAdditionalData([]);
        $this->assertFalse($result);
    }

    /** @test */
    public function additionalRefund(): void
    {
        $this->assertEquals('refund', LotteryAdditionalDataService::ADDITIONAL_REFUND);
    }

    /** @test */
    public function additionalSuper(): void
    {
        $this->assertEquals('super', LotteryAdditionalDataService::ADDITIONAL_SUPER);
    }

    /** @test */
    public function shortNameRefund(): void
    {
        $this->assertEquals('R', LotteryAdditionalDataService::REFUND_BALL_SHORT_NAME);
    }

    /** @test */
    public function shortNameSuper(): void
    {
        $this->assertEquals('S', LotteryAdditionalDataService::SUPER_BALL_SHORT_NAME);
    }

    /**
     * @test
     * @dataProvider shortNameBallProvider
     * @param string $additionalData
     * @param string $expectedResults
     */
    public function getBallShortName(string $additionalData, string $expectedResults): void
    {
        $additionalData = unserialize($additionalData);
        $result = $this->lineServices->getBallShortName($additionalData);
        $this->assertEquals($expectedResults, $result);
    }

    public function shortNameBallProvider(): array
    {
        return [
            [self::REFUND_ADDITIONAL_DATA, 'R'],
            [self::SUPER_ADDITIONAL_DATA, 'S'],
            [self::EMPTY_ADDITIONAL_DATA, ''],
        ];
    }

    /**
     * @test
     * @dataProvider extraBallProvider
     * @param string|null $additionalData
     * @param string|null $expectedResults
     */
    public function getExtraBall(?string $additionalData, ?string $expectedResults): void
    {
        $result = $this->lineServices->getExtraBall($additionalData);
        $this->assertEquals($expectedResults, $result);
    }

    /**
     * @test
     * @dataProvider getAdditionalDataForLottery_dataProvider
     */
    public function getAdditionalDataForLottery(array $lottery, array $lotteryType, array $lotteryDraw, array $expectedResult): void
    {
        $actual = $this->lineServices->getAdditionalDataForLottery(
            $lottery,
            $lotteryType,
            $lotteryDraw
        );

        $this->assertEquals($expectedResult, $actual);
    }

    public function extraBallProvider(): array
    {
        return [
            [self::REFUND_ADDITIONAL_DATA, '5 R'],
            [self::SUPER_ADDITIONAL_DATA, '5 S'],
            [self::EMPTY_ADDITIONAL_DATA, null],
            ['', null],
            [null, null],
            ['N;', null],
        ];
    }

    public function getAdditionalDataForLottery_dataProvider(): array
    {
        return [
            [
                ['additional_data' => self::REFUND_ADDITIONAL_DATA],
                ['additional_data' => self::REFUND_ADDITIONAL_DATA],
                ['additional_data' => self::REFUND_ADDITIONAL_DATA],
                ['refund' => 5]
            ],
            [
                ['additional_data' => self::EMPTY_ADDITIONAL_DATA],
                ['additional_data' => self::EMPTY_ADDITIONAL_DATA],
                ['additional_data' => self::EMPTY_ADDITIONAL_DATA],
                []
            ],
        ];
    }
}
