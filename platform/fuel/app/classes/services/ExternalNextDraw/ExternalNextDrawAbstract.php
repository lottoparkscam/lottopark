<?php

namespace Services\ExternalNextDraw;

use Carbon\Carbon;
use Helpers_Time;
use LotteryHelper;

abstract class ExternalNextDrawAbstract
{
    /**
     * Sources can shows diff draw dates like closing sale time and real draw date.
     * Return true when sources use different draw dates to check are we closing lottery sales correct.
     */
    public bool $shouldCheckClosingTimes = false;

    abstract function getNextDrawFromFirstSource(): string;

    abstract function getNextDrawFromSecondSource(): string;

    /**
     * Lotteries page could use different timezone.
     * This method set lottery timezone.
     * Remember, lottery format not always contains timezone.
     * If timezone haven`t existed before use this function, add website timezone.
     */
    protected function setLotteryTimezone(string &$drawDate, string $lotterySlug): void
    {
        $timezone = LotteryHelper::getLotteryTimezonePerSlug($lotterySlug);
        $drawDate = Carbon::parse($drawDate)->setTimezone($timezone)->toString();
    }

    protected function getDateTimeWithOurFormat(string $drawDate): string
    {
        return Carbon::parse($drawDate)->format(Helpers_Time::DATETIME_FORMAT);
    }
}
