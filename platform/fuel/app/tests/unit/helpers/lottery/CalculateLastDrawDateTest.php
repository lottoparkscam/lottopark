<?php

namespace Tests\Unit\Helpers\Lottery;

use Carbon\Carbon;
use Helpers_Lottery;
use Helpers_Time;
use Test_Unit;

final class CalculateLastDrawDateTest extends Test_Unit
{
    private $draw_date;
    private $draw_date_times;
    private $now;

    public function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(false);
        $this->now = Carbon::now();
        $this->draw_date_times = ['Wed 21:00', 'Sat 21:00'];
        $this->draw_date = Carbon::parse('last Wed 21:00');
    }

    private function assertCalculationCorrect()
    {
        $this->assertEquals(
            Helpers_Lottery::calculate_last_draw_datetime($this->draw_date_times, $this->now),
            $this->draw_date->format(Helpers_Time::DATETIME_FORMAT)
        );
    }

    /**
     * Now day is greater than any of draw days.
     * In this case it's Thursday and since we have closest last date 3 then it will be selected
     *
     * @test
     * @return void
     */
    public function nowDay_Greater(): void
    {
        $this->now = Carbon::parse('last Thu');
        $this->assertCalculationCorrect();
    }

    /**
     * Now day is the same as one of draw days.
     * In this case it's Thursday and since time elapsed then see that it will be selected
     *
     * @test
     * @return void
     */
    public function nowDay__Equals(): void
    {
        $this->now = Carbon::parse('last Wed 22:00:00');
        $this->draw_date = Carbon::parse('last Wed 21:00');
        $this->assertCalculationCorrect();
    }

    /**
     * Now day is the same as one of draw days.
     * In this case it's Thursday and since time didn't elapsed then see that it will not be selected
     *
     * @test
     * @return void
     */
    public function nowDay__EqualsLesser(): void
    {
        $this->now = Carbon::parse('last Wed 20:00:00');
        $this->draw_date = Carbon::parse('last Sat 21:00');
        $this->assertCalculationCorrect();
    }

    /**
     * Now day is lesser than any of draw days.
     * See that last item will be selected
     *
     * @test
     * @return void
     */
    public function nowDay_Lesser(): void
    {
        $this->now = Carbon::parse('last Mon');
        $this->draw_date = Carbon::parse('last Sat 21:00');
        $this->assertCalculationCorrect();
    }
}
