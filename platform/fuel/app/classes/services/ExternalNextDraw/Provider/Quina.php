<?php

namespace Services\ExternalNextDraw\Provider;

use Container;
use Exception;
use Lotto_Scraperhtml;
use Models\Lottery;
use Services\Logs\FileLoggerService;

class Quina
{
    public function getNextDrawDateTime(): string
    {
        try {
            return Lotto_Scraperhtml::build('https://www.24lottos.com/lottery/quina')
                ->setDrawDateBoundaries('class="timetodraw" data-lotterycountdown="', '">')->extractDrawDate();
        } catch (Exception) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error(Lottery::QUINA_SLUG . ' can not download next draw date');
            return '';
        }
    }
}
