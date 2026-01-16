<?php

namespace Services\ExternalNextDraw\Provider;

use Container;
use Exception;
use Lotto_Scraperhtml;
use Models\Lottery;
use Services\Logs\FileLoggerService;

class Lotto6Aus49
{
    public function getNextDrawDateTime(): string
    {
        try {
            $scrapedDrawDate = Lotto_Scraperhtml::build('https://www.lottohelden.de/lotto/')
                ->setInitialBoundaries('<div class="drawing"', '</div>')
                ->setDrawDateBoundaries('Ziehung:', 'Uhr')->extractDrawDate();
        } catch (Exception) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error(Lottery::LOTTO_6AUS49_SLUG . ' can not download next draw date');
        }

        return $this->prepareDrawDate($scrapedDrawDate ?? '');
    }

    private function prepareDrawDate(string $date): string
    {
        if (empty($date)) {
            return '';
        }

        $dateElements = explode(', ', $date);

        if (!array_key_exists(1, $dateElements) || !array_key_exists(2, $dateElements)) {
            return '';
        }

        $date = $dateElements[1];
        $hour = $dateElements[2];
        return $date . ' ' . $hour;
    }
}
