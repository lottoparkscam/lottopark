<?php

namespace Services\ExternalNextDraw;

use Models\Lottery;
use Services\ExternalNextDraw\Provider\MegaSena;
use Services\ScraperProvider\WeLoveLottoScraperService;

class MegaSenaService extends ExternalNextDrawAbstract
{
    public bool $shouldCheckClosingTimes = true;

    public function __construct(
        public WeLoveLottoScraperService $weLoveLottoService,
        public MegaSena                  $megaSena,
    )
    {
    }

    public function getNextDrawFromFirstSource(): string
    {
        $nextDrawFromMegaSenaProvider = $this->megaSena->getNextDrawDateTime();
        $this->setLotteryTimezone($nextDrawFromMegaSenaProvider, Lottery::MEGA_SENA_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawFromMegaSenaProvider);
    }

    public function getNextDrawFromSecondSource(): string
    {
        $nextDrawFromWeLoveLottoProvider = $this->weLoveLottoService->getNextDrawDateTime(Lottery::MEGA_SENA_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawFromWeLoveLottoProvider);
    }
}
