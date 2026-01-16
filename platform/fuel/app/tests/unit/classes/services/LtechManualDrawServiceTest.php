<?php

namespace Tests\Unit\Classes\Services;

use Carbon\Carbon;
use Helpers_Time;
use Lotto_Settings;
use LtechManualDrawService;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\LotteryRepository;
use Repositories\LotteryTypeDataRepository;
use Repositories\LtechManualDrawRepository;
use stdClass;
use Test_Unit;

class LtechManualDrawServiceTest extends Test_Unit
{
    private LotteryTypeDataRepository|MockObject $lotteryTypeDataRepositoryMock;
    private LotteryRepository|MockObject $lotteryRepositoryMock;
    private LtechManualDrawRepository|MockObject $ltechManualDrawRepositoryMock;

    public function setUp(): void
    {
        parent::setUp();

        $this->lotteryRepositoryMock = $this->createMock(LotteryRepository::class);
        $this->container->set(LotteryRepository::class, $this->lotteryRepositoryMock);
        $this->ltechManualDrawRepositoryMock = $this->createMock(LtechManualDrawRepository::class);
        $this->container->set(LtechManualDrawRepository::class, $this->ltechManualDrawRepositoryMock);

        $this->lotteryTypeDataRepositoryMock = $this->createPartialMock(
            LotteryTypeDataRepository::class,
            ['findByLotteryTypeId']
        );
        $this->container->set(LotteryTypeDataRepository::class, $this->lotteryTypeDataRepositoryMock);

        $this->lotteriesData = [
            [
                'id' => 1,
                'name' => 'Powerball',
                'slug' => 'powerball',
                'next_date_local' => Carbon::now()->addDays(2)->format(Helpers_Time::DATETIME_FORMAT),
                'timezone' => 'UTC',
                'normal_numbers_range' => 10,
                'normal_numbers_count' => 5,
                'bonus_numbers_range' => 20,
                'bonus_numbers_count' => 1,
                'currency_code' => 'USD',
                'other_data' => null,
                'draw_dates' => '["Mon 12:00"]',
                'type' => 'lottery',
            ],
            [
                'id' => 2,
                'name' => 'Lotto-Pl',
                'slug' => 'lotto-pl',
                'next_date_local' => Carbon::now()->addDays(2)->format(Helpers_Time::DATETIME_FORMAT),
                'timezone' => 'UTC',
                'normal_numbers_range' => 10,
                'normal_numbers_count' => 5,
                'bonus_numbers_range' => 20,
                'bonus_numbers_count' => 1,
                'type' => 'lottery',
                'currency_code' => 'USD',
                'other_data' => null,
                'draw_dates' => '["Mon 12:00"]',
            ],
            [
                'id' => 3,
                'name' => 'Lotto-gb',
                'slug' => 'lotto-gb',
                'next_date_local' => Carbon::now()->addDays(2)->format(Helpers_Time::DATETIME_FORMAT),
                'timezone' => 'UTC',
                'normal_numbers_range' => 10,
                'normal_numbers_count' => 5,
                'bonus_numbers_range' => 20,
                'bonus_numbers_count' => 1,
                'type' => 'lottery',
                'currency_code' => 'USD',
                'other_data' => null,
                'draw_dates' => '["Mon 12:00"]',
            ],
            [
                'id' => 4,
                'name' => 'Powerball',
                'slug' => 'powerball',
                'next_date_local' => Carbon::now()->addDays(2)->format(Helpers_Time::DATETIME_FORMAT),
                'timezone' => 'UTC',
                'normal_numbers_range' => 10,
                'normal_numbers_count' => 5,
                'bonus_numbers_range' => 20,
                'bonus_numbers_count' => 1,
                'currency_code' => 'USD',
                'other_data' => null,
                'draw_dates' => '["Mon 12:00", "Mon 13:00"]',
                'type' => 'lottery',
            ],
            [
                'id' => 5,
                'name' => 'unknown',
                'slug' => 'unknownslug',
                'next_date_local' => Carbon::now()->addDays(2)->format(Helpers_Time::DATETIME_FORMAT),
                'timezone' => 'UTC',
                'normal_numbers_range' => 10,
                'normal_numbers_count' => 5,
                'bonus_numbers_range' => 20,
                'bonus_numbers_count' => 1,
                'currency_code' => 'USD',
                'other_data' => null,
                'draw_dates' => '[]',
                'type' => 'lottery',
            ],
            [
                'id' => 8,
                'name' => 'La Primitiva',
                'slug' => 'la-primitiva',
                'next_date_local' => Carbon::now()->addDays(2)->format(Helpers_Time::DATETIME_FORMAT),
                'timezone' => 'UTC',
                'normal_numbers_range' => 10,
                'normal_numbers_count' => 5,
                'bonus_numbers_range' => 20,
                'bonus_numbers_count' => 1,
                'currency_code' => 'USD',
                'draw_dates' => '["Mon 12:00"]',
                'type' => 'lottery',
            ],
        ];
        $this->serviceUnderTest = $this->container->get(LtechManualDrawService::class);
    }

    /**
        * @test
        * @runTestsInSeparateProcess
        * @preserveGlobalState disabled
        */
    public function getLotteriesForManualDraw(): void
    {
        //Given
        Lotto_Settings::getInstance()->set('locale_default', 'en_GB');

        // Expected
        $this->lotteryRepositoryMock->expects($this->once())
            ->method('findWaitingForDraw')
            ->willReturn($this->lotteriesData);
        $this->ltechManualDrawRepositoryMock->expects($this->once())
            ->method('getPendingLotteryIds')
            ->willReturn([3]);

        $firstMatch = new stdClass();
        $firstMatch->match_n = 1;
        $firstMatch->match_b = 2;
        $firstMatch->additionalData = 'a:3:{s:6:"refund";i:1;s:10:"refund_min";i:0;s:10:"refund_max";i:9;}';
        $secondMatch = new stdClass();
        $secondMatch->match_n = 2;
        $secondMatch->match_b = 0;
        $secondMatch->additionalData = 'a:3:{s:6:"refund";i:1;s:10:"refund_min";i:0;s:10:"refund_max";i:9;}';

        $this->lotteryTypeDataRepositoryMock->expects($this->exactly(2))
            ->method('findByLotteryTypeId')
            ->willReturn([$firstMatch, $secondMatch]);

        // When
        $lotteries = $this->serviceUnderTest->getLotteriesForManualDraw();

        // Then
        // Filtered null data
        $this->assertArrayNotHasKey('other_data', $lotteries[0]);
        $this->assertArrayNotHasKey('id', $lotteries[0]);
        $this->assertSame('UTC', $lotteries[0]['timezone']);
        $this->assertSame('US$', $lotteries[0]['currency_sign']);

        // Tiers in expected format
        $expectedTiers = [
            [
                'normal_numbers' => 1,
                'bonus_numbers' => 2,
                'additional_number' => false,
            ],
            [
                'normal_numbers' => 2,
                'bonus_numbers' => 0,
                'additional_number' => false,
            ]
        ];
        $this->assertSame($expectedTiers, $lotteries[0]['tiers']);

        // Removed excluded lotteries
        $this->assertCount(2, $lotteries);

        // Additional property is set
        $this->assertSame('refund', $lotteries[1]['additionalNumberName']);
    }
}
