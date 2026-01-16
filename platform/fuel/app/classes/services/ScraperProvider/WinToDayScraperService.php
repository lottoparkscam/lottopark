<?php

namespace Services\ScraperProvider;

use Container;
use Lotto_Scraperhtml;
use Models\Lottery;
use Services\Logs\FileLoggerService;
use Throwable;

/**
 * This source doesn't work anymore and we can't get any data from it
 */
class WinToDayScraperService
{
    private const NEXT_DRAW_URL_PER_OUR_SLUG = [
      Lottery::LOTTO_AUSTRIA_SLUG => 'https://www.win2day.at/lotterie',
    ];

    public function getNextDraw(string $slug): string
    {
        try {
            return $this->getScrapedNextDrawPage($slug)
                ->setDrawDateBoundaries('data-date="', '">')
                ->extractDrawDate();
        } catch (Throwable) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error($slug . ' can not download next draw date');
            return '';
        }
    }

    private function getScrapedNextDrawPage(string $slug): Lotto_Scraperhtml
    {
        $url = self::NEXT_DRAW_URL_PER_OUR_SLUG[$slug];
        return Lotto_Scraperhtml::build($url)
            ->setInitialBoundaries('data-href="/lotterie/lotto/lotto-spiel"', '</strong>');
    }
}
