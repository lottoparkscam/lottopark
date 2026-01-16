<?php

namespace Services\ExternalNextDraw\Provider;

use Container;
use Exception;
use Lotto_Scraperhtml;
use Models\Lottery;
use Services\Logs\FileLoggerService;

class MegaSena
{
    public function getNextDrawDateTime(): string
    {
        try {
            return Lotto_Scraperhtml::build('https://www.lottoexposed.com/mega-sena-lottery-exposed/')
                ->setDrawDateBoundaries('data-datetime="', '">')->extractDrawDate();
        } catch (Exception) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error(Lottery::MEGA_SENA_SLUG . ' can not download next draw date');
            return '';
        }
    }
}
