<?php

namespace Feature\Classes\Lotto;

use Carbon\Carbon;
use Helpers_Time;
use Lotto_Helper;
use Models\Lottery;
use Models\LotteryDelay;
use Repositories\LotteryRepository;
use Test_Feature;

class LottoHelperTest extends Test_Feature
{
    private Lottery $superenaLottery;

    public function setUp(): void
    {
        parent::setUp();
        $lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->superenaLottery = $lotteryRepository->findOneBySlug('superenalotto');
    }

    /** @test */
    public function getLotteryNextDraw_withDoubleDelayAndAfterNextDraw(): void
    {
        // Given
        $lastDrawDateTime = '2022-12-17 20:00:00'; // Sat
        $nextDrawDateTime = '2022-12-20 20:00:00'; // Tue
        $nextNextDrawDateTime = '2022-12-22 20:00:00'; // Thu
        $plannedDrawDateTimeOutsideDrawDates = '2022-12-23 20:00:00'; // Fri

        $lotteryData = [
            'last_date_local' => $lastDrawDateTime,
            'next_date_local' => $nextNextDrawDateTime,
            'next_date_utc' => $nextNextDrawDateTime,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'UTC',
        ];
        $this->superenaLottery->set($lotteryData);
        $this->superenaLottery->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextDrawDateTime,
            'date_delay' => $nextNextDrawDateTime
        ]))->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextNextDrawDateTime,
            'date_delay' => $plannedDrawDateTimeOutsideDrawDates
        ]))->save();

        // It's after first delayed draw
        // The soonest draw should be 23 because its 22 delayed to 23 despite now it's after 22
        Carbon::setTestNow('2022-12-22 21:00:00');

        $expected = $plannedDrawDateTimeOutsideDrawDates;

        // When
        $superenaLottery = $this->superenaLottery->to_array();
        $actual = (Lotto_Helper::get_lottery_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);
        $actualReal = (Lotto_Helper::get_lottery_real_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);

        // Then
        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $actualReal);
    }

    /** @test */
    public function getLotteryNextDraw_withDoubleDelayAndBeforeNextDraw(): void
    {
        // Given
        $lastDrawDateTime = '2022-12-17 20:00:00'; // Sat
        $nextDrawDateTime = '2022-12-20 20:00:00'; // Tue
        $nextNextDrawDateTime = '2022-12-22 20:00:00'; // Thu
        $plannedDrawDateTimeOutsideDrawDates = '2022-12-23 20:00:00'; // Fri

        $lotteryData = [
            'last_date_local' => $lastDrawDateTime,
            'next_date_local' => $nextNextDrawDateTime,
            'next_date_utc' => $nextNextDrawDateTime,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'UTC',
        ];
        $this->superenaLottery->set($lotteryData);
        $this->superenaLottery->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextDrawDateTime,
            'date_delay' => $nextNextDrawDateTime
        ]))->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextNextDrawDateTime,
            'date_delay' => $plannedDrawDateTimeOutsideDrawDates
        ]))->save();

        // It's after first delayed draw
        Carbon::setTestNow('2022-12-21 12:00:00');

        $expected = $nextNextDrawDateTime;

        // When
        $superenaLottery = $this->superenaLottery->to_array();
        $actual = (Lotto_Helper::get_lottery_real_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function getLotteryNextDraw_withSingleDelayOnDrawNotInDrawDatesAndAfterNextDraw(): void
    {
        // Given
        $lastDrawDateTime = '2022-12-17 20:00:00'; // Sat
        $nextDrawDateTime = '2022-12-20 20:00:00'; // Tue
        $nextNextDrawDateTime = '2022-12-22 20:00:00'; // Thu
        $plannedDrawDateTimeOutsideDrawDates = '2022-12-23 20:00:00'; // Fri

        $lotteryData = [
            'last_date_local' => $lastDrawDateTime,
            'next_date_local' => $nextDrawDateTime,
            'next_date_utc' => $nextDrawDateTime,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'UTC',
        ];
        $this->superenaLottery->set($lotteryData);
        $this->superenaLottery->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextNextDrawDateTime,
            'date_delay' => $plannedDrawDateTimeOutsideDrawDates
        ]))->save();


        // It's after nextDraw
        Carbon::setTestNow('2022-12-20 21:00:00');

        $expected = $plannedDrawDateTimeOutsideDrawDates;

        // When
        $superenaLottery = $this->superenaLottery->to_array();
        $actual = (Lotto_Helper::get_lottery_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);
        $actualReal = (Lotto_Helper::get_lottery_real_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);

        // Then
        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $actualReal);
    }


    /** @test */
    public function getLotteryNextDraw_withSingleDelayOnDrawNotInDrawDatesAndAfterNextNextDraw(): void
    {
        // Given
        $lastDrawDateTime = '2022-12-17 20:00:00'; // Sat
        $nextDrawDateTime = '2022-12-20 20:00:00'; // Tue
        $nextNextDrawDateTime = '2022-12-22 20:00:00'; // Thu
        $plannedDrawDateTimeOutsideDrawDates = '2022-12-23 20:00:00'; // Fri

        $lotteryData = [
            'last_date_local' => $lastDrawDateTime,
            'next_date_local' => $nextDrawDateTime,
            'next_date_utc' => $nextDrawDateTime,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'UTC',
        ];
        $this->superenaLottery->set($lotteryData);
        $this->superenaLottery->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextNextDrawDateTime,
            'date_delay' => $plannedDrawDateTimeOutsideDrawDates
        ]))->save();


        // It's after nextDraw
        // But because of single delay in this case 2021-12-23 is skipped and regular draw from drawDates is returned
        Carbon::setTestNow('2022-12-22 21:10:00');

        $expected = '2022-12-24 20:00:00'; // Sat

        // When
        $superenaLottery = $this->superenaLottery->to_array();
        $actual = (Lotto_Helper::get_lottery_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);
        $actualReal = (Lotto_Helper::get_lottery_real_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);

        // Then
        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $actualReal);
    }

    /** @test */
    public function getLotteryNextDraw_withSingleDelayOnDrawNotInDrawDates(): void
    {
        // Given
        $lastDrawDateTime = '2022-12-17 20:00:00'; // Sat
        $nextNextDrawDateTime = '2022-12-22 20:00:00'; // Thu
        $plannedDrawDateTimeOutsideDrawDates = '2022-12-23 20:00:00'; // Fri

        $lotteryData = [
            'last_date_local' => $lastDrawDateTime,
            'next_date_local' => null,
            'next_date_utc' => null,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'UTC',
        ];
        $this->superenaLottery->set($lotteryData);
        $this->superenaLottery->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextNextDrawDateTime,
            'date_delay' => $plannedDrawDateTimeOutsideDrawDates
        ]))->save();


        // It's after nextDraw
        Carbon::setTestNow('2022-12-20 21:00:00');

        $expected = $plannedDrawDateTimeOutsideDrawDates;

        // When
        $superenaLottery = $this->superenaLottery->to_array();
        $actual = (Lotto_Helper::get_lottery_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);
        $actualReal = (Lotto_Helper::get_lottery_real_next_draw($superenaLottery))->format(Helpers_Time::DATETIME_FORMAT);

        // Then
        $this->assertSame($expected, $actual);
        $this->assertSame($expected, $actualReal);
    }

    /** @test */
    public function getLotteryNextDraw_withSingleDelayOnDrawNotInDrawDatesAndBeforeNextNextDraw(): void
    {
        // Given
        $lastDrawDateTime = '2022-12-17 20:00:00'; // Sat
        $nextNextDrawDateTime = '2022-12-22 20:00:00'; // Thu
        $plannedDrawDateTimeOutsideDrawDates = '2022-12-23 20:00:00'; // Fri

        $lotteryData = [
            'last_date_local' => $lastDrawDateTime,
            'next_date_local' => $plannedDrawDateTimeOutsideDrawDates,
            'next_date_utc' => $plannedDrawDateTimeOutsideDrawDates,
            'draw_dates' => json_encode(["Tue 20:00", "Thu 20:00", "Sat 20:00"]),
            'timezone' => 'UTC',
        ];
        $this->superenaLottery->set($lotteryData);
        $this->superenaLottery->save();

        (new LotteryDelay([
            'lottery_id' => $this->superenaLottery->id,
            'date_local' => $nextNextDrawDateTime,
            'date_delay' => $plannedDrawDateTimeOutsideDrawDates
        ]))->save();

        Carbon::setTestNow();

        $expected = '2022-12-23 20:00:00'; // Sat

        // When
        $superenaLottery = $this->superenaLottery->to_array();
        $actual = (Lotto_Helper::get_lottery_next_draw(
            $superenaLottery,
            true,
            Carbon::parse('2022-12-21 21:00:00')
        ))->format(Helpers_Time::DATETIME_FORMAT);

        // Then
        $this->assertSame($expected, $actual);
    }
}
