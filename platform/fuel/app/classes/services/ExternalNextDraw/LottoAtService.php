<?php

namespace Services\ExternalNextDraw;

use Models\Lottery;
use Services\LotteryProvider\TheLotterService;

class LottoAtService extends ExternalNextDrawAbstract
{

    public bool $shouldCheckClosingTimes = true;

    public function __construct(
        public TheLotterService $theLotterService,
    )
    {
    }

    public function getNextDrawFromFirstSource(): string
    {
        $nextDrawDateTimeFromTheLotter = $this->theLotterService->getNextDrawDateTimePerOurSlug(Lottery::LOTTO_AUSTRIA_SLUG);
        $this->setLotteryTimezone($nextDrawDateTimeFromTheLotter, Lottery::LOTTO_AUSTRIA_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawDateTimeFromTheLotter);
    }

    public function getNextDrawFromSecondSource(): string
    {
        return $this->getNextDrawFromFirstSource();
    }
}
