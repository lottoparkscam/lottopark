<?php

namespace Factories;

use Carbon\Carbon;
use Helpers_Time;
use Models\Lottery;
use Models\LtechManualDraw;

class LtechManualDrawFactory
{
    public function createLtechManualDraw(Lottery $lottery): LtechManualDraw
    {
        $ltechManualDraw = new LtechManualDraw([
            'lottery_id' => $lottery->id,
            'next_draw_date' => Carbon::now($lottery->timezone)->format(Helpers_Time::DATETIME_FORMAT),
            'current_draw_date' => $lottery->nextDateLocal->format(Helpers_Time::DATETIME_FORMAT),
            'current_draw_date_utc' => $lottery->nextDateUtc->format(Helpers_Time::DATETIME_FORMAT),
            'next_jackpot' => 10000000.23,
            'currency_id' => 1,
            'created_at' => Carbon::now('UTC'),
        ]);

        $ltechManualDraw->normalNumbers = [1, 2, 3, 4, 5];
        $ltechManualDraw->bonusNumbers = [6];
        $ltechManualDraw->prizes = [
            'match-0-1' => 10,
            'match-1-1' => 20,
            'match-2-1' => 30,
            'match-3' => 40,
            'match-3-1' => 45,
            'match-4' => 50,
            'match-4-1' => 60,
            'match-5' => 70,
            'match-5-1' => 80,
        ];
        $ltechManualDraw->winners = [
            'match-0-1' => 1,
            'match-1-1' => 2,
            'match-2-1' => 3,
            'match-3' => 4,
            'match-3-1' => 10,
            'match-4' => 5,
            'match-4-1' => 6,
            'match-5' => 7,
            'match-5-1' => 8,
        ];

        return $ltechManualDraw;
    }
}
