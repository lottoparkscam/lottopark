<?php

namespace Services\ScraperProvider;

use Container;
use Lotto_Scraperapi;
use Services\Logs\FileLoggerService;
use Services\LotteryProvider\TheLotterService;
use Throwable;

class TheLotterScraperService
{
    public function getNextDrawDateTimePerOurSlug(string $slug): string
    {
        try {
            $lotteryModel = $this->getLotteryModelPerOurSlug($slug);
            return $lotteryModel['d']['State']['DrawData']['drawLocalDateTime'];
        } catch (Throwable) {
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error($slug . ' can not download next draw date');
            return '';
        }
    }

    private function getLotteryModelPerOurSlug(string $slug): array
    {
        return Lotto_Scraperapi::build()
            ->fetchJsonDataStructureWithParameters('https://www.thelotter.com/__ajax/__play.asmx/getplaymodel', [
                'lotteryId' => TheLotterService::LOTTERY_ID_PER_OUR_SLUG[$slug],
                'lotteryType' => 0,
                'formType' => 0,
                'numberOfLines' => 0,
            ])->jsonStructure;
    }
}
