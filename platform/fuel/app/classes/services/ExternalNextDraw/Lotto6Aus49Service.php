<?php

namespace Services\ExternalNextDraw;

use Models\Lottery;
use Services\ExternalNextDraw\Provider\Lotto6Aus49;
use Services\LotteryProvider\TheLotterService;

class Lotto6Aus49Service extends ExternalNextDrawAbstract

{
    public function __construct(
        public TheLotterService $theLotterService,
        public Lotto6Aus49             $lotto6Aus49,
    )
    {
    }

    public function getNextDrawFromFirstSource(): string
    {
        $nextDrawDateTimeFromLotto6Aus49 = $this->lotto6Aus49->getNextDrawDateTime();
        return $this->getDateTimeWithOurFormat($nextDrawDateTimeFromLotto6Aus49);
    }

    public function getNextDrawFromSecondSource(): string
    {
        $nextDrawDateTimeFromTheLotter = $this->theLotterService->getNextDrawDateTimePerOurSlug(Lottery::LOTTO_6AUS49_SLUG);
        $this->setLotteryTimezone($nextDrawDateTimeFromTheLotter, Lottery::LOTTO_6AUS49_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawDateTimeFromTheLotter);
    }
}
