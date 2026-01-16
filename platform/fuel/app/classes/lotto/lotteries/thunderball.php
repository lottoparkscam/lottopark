<?php

use Carbon\Carbon;
use Fuel\Core\CacheNotFoundException;
use Helpers\ScraperHelper;
use Services\CacheService;
use Services\Logs\FileLoggerService;

class Lotto_Lotteries_Thunderball extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'thunderball';

    public function get_results(): void
    {
        $lottery = $this->lottery;

        try {
            $nextDrawDateFormatted = Carbon::parse($lottery['next_date_local'])->format('d-m-Y');

            $scrapper = Lotto_Scraperhtml::build(
                "https://www.lottery.co.uk/thunderball/results-$nextDrawDateFormatted",
                ScraperHelper::$NUMBER_LOCALE_COMMA_DOT
            )
                ->setInitialBoundaries('<h2>Results', '<tr class="totals">')
                ->setDrawDateBoundaries('<strong>', '</strong>')
                ->setNumbersBoundaries('<span id="ballsAscending">', '</table>')
                ->setPrizesBoundaries('<table class="table thunderball mobFormat"', '<tr class="totals">');

            $drawDate = $scrapper->extractDrawDate();

            if (!strtotime($drawDate)) {
                throw new Exception($this->lottery_slug . ' - Invalid date format from scraper');
            }

            if (Carbon::parse($drawDate, $this->lottery['timezone'])->format('d-m-Y') !== $nextDrawDateFormatted) {
                throw new Exception($this->lottery_slug . ' - unable to find draw');
            }

            $numbersArray = $scrapper->extractNumbers(5, 1);
            $prizes = $scrapper->extractPrizesUsingDOM(1, 2);

            $jackpotScrapper = Lotto_Scraperhtml::build(
                'https://www.lottery.co.uk/thunderball/results',
                ScraperHelper::$NUMBER_LOCALE_COMMA_DOT
            )
                ->setInitialBoundaries('<div class="resultBox withSide">', '</span></div><br')
                ->setJackpotBoundaries('<span class="resultJackpot">', '</span>');

            // jackpot value must be in millions
            $jackpot = ScraperHelper::getNumberOfMillions($jackpotScrapper->jackpotHTML, $jackpotScrapper->numberLocaleFlag);

            try {
                $this->validateResults(
                    [
                        $jackpot,
                        $drawDate,
                        $numbersArray[0],
                        $numbersArray[1],
                        $prizes
                    ],
                    5,
                    1,
                    [1, 39],
                    [1, 14],
                    9,
                );
            } catch (Throwable $exception) {
                if (empty($jackpot)) {
                    ScraperHelper::sendEmptyJackpotErrorAfterSixHours($jackpot, $this->lottery_slug);
                    return;
                }

                $cacheService = Container::get(CacheService::class);
                $cacheKey = 'thunderballLastErrorAttempt';
                $fiveHourInSecond = 18000;
                try {
                    $lastUpdateAttempt = $cacheService->getGlobalCache($cacheKey);
                } catch (CacheNotFoundException) {
                    $lastUpdateAttempt = Carbon::now()->format(Helpers_Time::DATETIME_FORMAT);
                    $cacheService->setGlobalCache($cacheKey, $lastUpdateAttempt, $fiveHourInSecond);
                }

                $shouldSentError = Carbon::parse($lastUpdateAttempt)->diffInHours(Carbon::now()) >= 4;
                if ($shouldSentError) {
                    $fileLoggerService = Container::get(FileLoggerService::class);
                    $fileLoggerService->error($exception->getMessage());
                }

                return;
            }

            $drawDateTime = $this->prepareDrawDatetime($drawDate);
            if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) {
                $this->set_lottery_with_data(
                    $this->lottery,
                    $jackpot,
                    $drawDateTime,
                    $drawDateTime->clone()->setTimezone('UTC'),
                    $numbersArray[0],
                    $numbersArray[1],
                    $prizes,
                    $this->overwrite_jackpot,
                );

                return;
            }

            echo 'jackpot update or draw insertion trigger condition not met.';
        } catch (\Throwable $e) {
            echo $e->__toString();
        }
    }
}
