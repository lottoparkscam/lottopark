<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Carbon\Carbon;
use DateTime;
use DateTimeZone;
use Lotto_Lotteries_Ltech;
use ReflectionMethod;
use Test_Unit;

class LtechTest extends Test_Unit
{
    /** @test */
    public function getLtechSlug(): void
    {
        $get_ltech_slug = new ReflectionMethod('Lotto_Lotteries_Ltech', 'get_ltech_slug');
        $get_ltech_slug->setAccessible(true);
        $ltech = new class extends Lotto_Lotteries_Ltech {
        };

        $slug = $get_ltech_slug->invokeArgs($ltech, array(2,1));
        $this->assertSame('match-2-1', $slug);

        $slug = $get_ltech_slug->invokeArgs($ltech, array(3,0));
        $this->assertSame('match-3', $slug);
    }

    /** @test */
    public function validateDrawDate(): void
    {
        $lottery = [];
        $lottery['timezone'] = 'Australia/Sydney';
        $lottery['draw_dates'] = json_encode(["Sat 19:30"]);
        $lottery['name'] = "Asd";

        $validate_draw_date = new ReflectionMethod('Lotto_Lotteries_Ltech', 'validate_draw_date');
        $validate_draw_date->setAccessible(true);
        $ltech = new class extends Lotto_Lotteries_Ltech {
        };

        $date = Carbon::parse('2020-11-14' . " " . '19:30', new DateTimeZone('Australia/Sydney'));

        $isValid = $validate_draw_date->invokeArgs($ltech, array($lottery, $date));

        $this->assertTrue($isValid);
    }

    /**
     * @group skipped
     * @test
     */
    public function prepareLotteryDrawDate(): void
    {
        $this->markTestSkipped();
        $prepare_lottery_draw_date = new ReflectionMethod('Lotto_Lotteries_Ltech', 'prepare_lottery_draw_date');
        $prepare_lottery_draw_date->setAccessible(true);
        $ltech = new class extends Lotto_Lotteries_Ltech {
        };

        $date = new DateTime('2020-11-13' . " " . '21:00', new DateTimeZone('Australia/Sydney'));

        $dateToTest = $prepare_lottery_draw_date->invokeArgs($ltech, array('13.11.2020', '21:00', 'Australia/Sydney'));

        $this->assertEquals($date, $dateToTest);
    }
}
