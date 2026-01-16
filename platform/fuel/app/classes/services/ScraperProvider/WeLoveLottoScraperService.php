<?php

namespace Services\ScraperProvider;

use Container;
use Lotto_Scraperhtml;
use Models\Lottery;
use Services\Logs\FileLoggerService;
use Throwable;

class WeLoveLottoScraperService
{
    private const LOTTERY_TICKET_BUY_PAGE_PER_OUR_SLUG = [
        Lottery::QUINA_SLUG => 'https://www.welovelotto.com/play-lottery/quina-lottery',
        Lottery::MEGA_SENA_SLUG => 'https://www.welovelotto.com/play-lottery/mega-sena',
    ];

    public function getNextDrawDateTime(string $slug): string
    {
        try {
            return $this->getScrapedLotteryBuyPage($slug)
                ->setDrawDateBoundaries('<span data-countdown="', '">')
                ->extractDrawDate();
        } catch (Throwable) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error($slug . ' can not download next draw date');
            return '';
        }
    }

    public function getScrapedLotteryBuyPage(string $slug): Lotto_Scraperhtml
    {
        $url = self::LOTTERY_TICKET_BUY_PAGE_PER_OUR_SLUG[$slug];
        return Lotto_Scraperhtml::build($url)
            ->setInitialBoundaries('Closing in:', '</span>');
    }
}
