<?php

namespace Unit\Lotto\Lotteries\Helper\Draw;

use Tests\Unit\Classes\Lotto\Lotteries\Helper\Draw\LottoLotteriesDrawParent;

final class GetNextDrawTest extends LottoLotteriesDrawParent
{
    protected array $lottery;

    public function setUp(): void
    {
        // NOTE: I dont care about delay - its' only for SuperEnalotto
        $this->lottery = [
            'id' => 13,
            'draw_dates' => json_encode(["Mon 20:30", "Mon 20:40", "Mon 20:50"]),
            'timezone' => 'Australia/Melbourne',
        ];
    }

    /** @test */
    public function beforeMonFirst_ExpectsMonFirst(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-01 20:29:00", "2021-02-01 20:30:00");
    }

    /** @test */
    public function beforeMonSecond_ExpectsMonSecond(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-01 20:39:00", "2021-02-01 20:40:00");
    }

    /** @test */
    public function beforeMonThird_ExpectsMonThird(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-01 20:49:00", "2021-02-01 20:50:00");
    }

    /** @test */
    public function afterMonThird_ExpectsMonFirst(): void
    {
        $this->assertNowToNextDrawDateCorrelation($this->lottery, "2021-02-01 20:51:00", "2021-02-08 20:30:00");
    }
}
