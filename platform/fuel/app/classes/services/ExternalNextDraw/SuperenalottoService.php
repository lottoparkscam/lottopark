<?php

namespace Services\ExternalNextDraw;

use Models\Lottery;
use Services\ExternalNextDraw\Provider\Superena;
use Services\LotteryProvider\TheLotterService;

class SuperenalottoService extends ExternalNextDrawAbstract
{
    public function __construct(
        public TheLotterService $theLotterService,
        public Superena                $superena,
    )
    {
    }

    public function getNextDrawFromFirstSource(): string
    {
        $nextDrawDateTimeFromSuperenaProvider = $this->superena->getNextDrawDateTime();
        $this->setLotteryTimezone($nextDrawDateTimeFromSuperenaProvider, Lottery::SUPERENA_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawDateTimeFromSuperenaProvider);
    }

    public function getNextDrawFromSecondSource(): string
    {
        $nextDrawDateTimeFromTheLotter = $this->theLotterService->getNextDrawDateTimePerOurSlug(Lottery::SUPERENA_SLUG);
        $this->setLotteryTimezone($nextDrawDateTimeFromTheLotter, Lottery::SUPERENA_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawDateTimeFromTheLotter);
    }
}
