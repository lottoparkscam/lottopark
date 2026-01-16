<?php

namespace Services\LotteryProvider;

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Lotto_Scraperapi;
use Models\Lottery;
use Services_Curl;

/**
 * Documentation -> https://gginternational.slite.com/app/docs/A72pTyfKZnuiqL
 */
class TheLotterService
{
    /**
     * Lottery id is from the lotter api getplaymodel after enter lottery page.
     * For Example:
     * 1. Enter this link with enabled dev tools -> https://www.thelotter.com/lottery-tickets/euromillions/?player=0
     * 2. Find in network fetch to getplaymodel
     * 3. Open payload tab and find lotteryId
     */
    public const LOTTERY_ID_PER_OUR_SLUG = [
        Lottery::SUPERENA_SLUG => 149,
        Lottery::LOTTO_6AUS49_SLUG => 20,
        Lottery::LOTTO_AUSTRIA_SLUG => 1,
        Lottery::BONOLOTO_SLUG => 146,
        Lottery::LA_PRIMITIVA_SLUG => 11,
    ];

    public function getNextDrawDateTimePerOurSlug(string $slug): string
    {
        $lotteryModel = $this->getLotteryModelPerOurSlug($slug);
        return $lotteryModel ? $this->createDateFromCustomFormat((string) $lotteryModel->entry->next_draw_date) : '';
    }

    public function getNextJackpot(string $slug): string
    {
        $lotteryModel = $this->getLotteryModelPerOurSlug($slug);
        return $lotteryModel ? $lotteryModel->entry->next_draw_jackpot_amount : '';
    }

    private function getLotteryModelPerOurSlug(string $slug): Object|False
    {
        $response = Services_Curl::getXmlAsBrowser($this->getLotteryModelUrl($slug));
        return simplexml_load_string($response);
    }

    private function getLotteryModelUrl(string $slug): string
    {
        $lotteryId = self::LOTTERY_ID_PER_OUR_SLUG[$slug];
        return "https://www.thelotter.com/rss.xml?lotteryIds=$lotteryId";
    }

    private function createDateFromCustomFormat(string $date): string
    {
        $datetime = Carbon::createFromFormat('d/m/Y H:i T', $date);
        return $datetime ? (string) $datetime : '';
    }

    /**
     * This api is slow, time for response is more than 30 sec.
     * @deprecated
     */
    public function getJackpotFromGetPlayModelPage(string $slug): string
    {
        $lotteryId = self::LOTTERY_ID_PER_OUR_SLUG[$slug];
        $jackpotScraper = Lotto_Scraperapi::build()
            ->fetchJsonDataStructureWithParameters('https://www.thelotter.com/__ajax/__play.asmx/getplaymodel', [
                "lotteryId" => $lotteryId,
                "lotteryType" => 0,
                "formType" => 0,
                "numberOfLines" => 0,
            ]);
        return $jackpotScraper->extractJackpotFromText(['d', 'State', 'DrawData', 'jackpotText']);
    }
}
