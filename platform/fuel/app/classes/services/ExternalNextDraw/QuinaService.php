<?php

namespace Services\ExternalNextDraw;

use Models\Lottery;
use Services\ExternalNextDraw\Provider\Quina;
use Services\ScraperProvider\WeLoveLottoScraperService;

class QuinaService extends ExternalNextDrawAbstract
{

    public bool $shouldCheckClosingTimes = true;

    public function __construct(
        public WeLoveLottoScraperService $weLoveLottoService,
        public Quina                     $quina,
    )
    {
    }

    public function getNextDrawFromFirstSource(): string
    {
        $nextDrawFromQuinaProvider = $this->quina->getNextDrawDateTime();
        $this->setLotteryTimezone($nextDrawFromQuinaProvider, Lottery::QUINA_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawFromQuinaProvider);
    }

    public function getNextDrawFromSecondSource(): string
    {
        $nextDrawFromWeLoveLottoProvider = $this->weLoveLottoService->getNextDrawDateTime(Lottery::QUINA_SLUG);
        return $this->getDateTimeWithOurFormat($nextDrawFromWeLoveLottoProvider);
    }
}
