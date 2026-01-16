<?php

namespace Tests;

use Carbon\Carbon;
use Helpers_Currency;
use Helpers_Time;
use Lotto_Helper;
use Test_Factory_Lottery;
use Test_Unit;

/**
 * Calculate Last Draw Date Test.
 * @group skipped
 */
class Tests_Unit_Helpers_Lottery_Get_Real_Next_Draw extends Test_Unit
{
    private $lottery;
    protected $in_transaction = true;

    protected function assert_next_draw_date_equals_to_datetime(string $datetime, int $next = 1)
    {
        self::assertEquals(
            Carbon::createFromTimeString($datetime)->format(Helpers_Time::DATETIME_FORMAT),
            Lotto_Helper::get_lottery_real_next_draw($this->lottery, $next)->format(Helpers_Time::DATETIME_FORMAT)
        );
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->markTestIncomplete('Unit with database access, bad fixtures and cases');

        // Mocking current date and time (UTC)
        Carbon::setTestNow("2020-10-05 11:00"); // Monday

        // Mocking lottery
        $draw_dates = [
            "Mon 10:00", "Mon 20:00",
            "Tue 10:00", "Tue 20:00",
            "Wed 10:00", "Wed 20:00",
            "Thu 10:00", "Thu 20:00",
            "Fri 10:00", "Fri 20:00",
            "Sat 10:00", "Sat 20:00",
            "Sun 10:00", "Sun 20:00",
        ];
        $this->lottery = Test_Factory_Lottery::create([
            'timezone' => "Europe/Paris",
            'draw_dates' => json_encode($draw_dates),
            'last_update' => Carbon::now()->format(Helpers_Time::DATETIME_FORMAT),
            'currency_id' => Helpers_Currency::USD_ID,
            'next_date_local' => Carbon::tomorrow()->format(Helpers_Time::DATETIME_FORMAT)
        ])->get_result()['lottery'][0];
    }

    public function test_next_draw_same_day()
    {
        $this->assert_next_draw_date_equals_to_datetime("2020-10-05 20:00");
    }

    public function test_next_draw_different_day()
    {
        Carbon::setTestNow("2020-10-05 21:37");
        $this->assert_next_draw_date_equals_to_datetime("2020-10-06 10:00");
    }

    public function test_next_next_draw()
    {
        $this->assert_next_draw_date_equals_to_datetime("2020-10-06 10:00", 2);
        $this->assert_next_draw_date_equals_to_datetime("2020-10-06 20:00", 3);
        $this->assert_next_draw_date_equals_to_datetime("2020-10-07 10:00", 4);
        $this->assert_next_draw_date_equals_to_datetime("2020-10-07 20:00", 5);
    }
}
