<?php

namespace Tests\Unit\Classes\Helpers;

use Carbon\Carbon;
use Helpers_Lottery;
use Test_Unit;

class LotteryHelperTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2021-02-02 12:00:00');
    }

    /**
     * @test
     * @dataProvider isPendingDataProvider
     */
    public function isPending(string $nextDateLocal, bool $expectBePending, string $timezone = 'UTC'): void
    {
        $lottery = [
            'next_date_local' => $nextDateLocal,
            'timezone' => $timezone,
        ];
        $isPending = Helpers_Lottery::isPending($lottery);
        $this->assertSame($expectBePending, $isPending);
    }

    public function isPendingDataProvider(): array
    {
        return [
            ['2021-02-02 11:59:59', true],
            ['2021-02-02 12:00:00', true],
            ['2021-02-02 12:00:01', false],
            ['2021-02-02 1:59:59', true, 'Pacific/Honolulu'],
            ['2021-02-02 2:00:00', true, 'Pacific/Honolulu'],
            ['2021-02-02 2:00:01', false, 'Pacific/Honolulu'],
        ];
    }

    /** @test */
    public function isGgrEnabled_SupportedLottery(): void
    {
        $result = Helpers_Lottery::isGgrEnabled(Helpers_Lottery::TYPE_KENO);
        $this->assertTrue($result);
    }

    /** @test */
    public function isGgrEnabled_UnsupportedLottery(): void
    {
        $result = Helpers_Lottery::isGgrEnabled(Helpers_Lottery::TYPE_LOTTERY);
        $this->assertFalse($result);
    }

    /** @test */
    public function isGgrNotEnabled(): void
    {
        $result = Helpers_Lottery::isGgrNotEnabled(Helpers_Lottery::TYPE_LOTTERY);
        $this->assertTrue($result);

        $result = Helpers_Lottery::isGgrNotEnabled(Helpers_Lottery::TYPE_KENO);
        $this->assertFalse($result);
    }
}
