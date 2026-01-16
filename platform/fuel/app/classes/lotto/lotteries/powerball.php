<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_Powerball extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'powerball';

    public function get_results(): void
    {
        try {
            $data = $this->fetchLotteryData();
            $this->processResults($data);
        } catch (Throwable $e) {
            echo $e->__toString();
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    protected function fetchLotteryData(): array
    {
        $drawDate = Carbon::parse($this->lottery['next_date_local'])->format('Y-m-d');

        try {
            $scraper = Lotto_Scraperhtml::build("https://www.powerball.com/draw-result?gc=powerball&date=$drawDate");
            return $this->getLotteryDataFromFirstSource($scraper);
        } catch (Exception $exception) {
            try {
                $scraper = Lotto_Scraperhtml::build("https://www.powerball.net/numbers/$drawDate");
                return $this->getLotteryDataFromSecondSource($scraper);
            } catch (Exception $exception) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'Both Sources',
                    exception: $exception,
                );
            }
        }
    }

    protected function processResults($data): void
    {
        try {
            [$numbersArray, $prizes, $drawDateTime, $jackpot] = $data;
            $prizes = array_values($prizes);

            if (!$this->validateAndLogResults($jackpot, $drawDateTime, $numbersArray, $prizes)) {
                return;
            }

        } catch (Throwable $exception) {
            if (empty($jackpot)) {
                ScraperHelper::sendEmptyJackpotErrorAfterSixHours($jackpot, $this->lottery_slug);
                return;
            }

            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Validation',
                exception: $exception,
                delayInHours: 2,
            );
            return;
        }

        if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) {
            $this->set_lottery_with_data(
                $this->lottery, $jackpot,
                $drawDateTime,
                $drawDateTime->clone()->setTimezone('UTC'),
                $numbersArray[0],
                $numbersArray[1],
                $prizes,
                $this->overwrite_jackpot
            );
            return;
        }

        echo 'jackpot update or draw insertion trigger condition not met.';
    }

    private function validateAndLogResults($jackpot, $drawDateTime, $numbersArray, $prizes): bool
    {
        try {
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 5, 1, [1, 69], [1, 26], 9);
            return true;
        } catch (Throwable $exception) {
            if (empty($jackpot)) {
                ScraperHelper::sendEmptyJackpotErrorAfterSixHours($jackpot, $this->lottery_slug);
                return false;
            }

            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Validation',
                exception: $exception,
            );
            return false;
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getLotteryDataFromFirstSource(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setDrawDateBoundaries('name="DrawDate" value="', '">')
            ->setNumbersBoundaries('item-powerball">', 'Power Play')
            ->setPrizesBoundaries('"Powerball Winners"', '</main>');

        $numbersArray = $scraper->extractNumbers(5, 1);
        $prizes = $scraper->extractPrizes('Powerball Winners', 'Powerball Prize', 'Powerball Prize', 'Power Play Winners');

        if (!strtotime($scraper->extractDrawDate())) {
            throw new Exception('[First source] Invalid date format from scraper');
        }

        $drawDateTime = $this->prepareDrawDatetime($scraper->extractDrawDate());

        // Fix Powerball Grand Prize
        $grandPrizeScrapper = $scraper
            ->setInitialBoundaries('Estimated Jackpot:', '</div>')
            ->setJackpotBoundaries('<span>', '</span>');
        $grandPrize = ScraperHelper::getNumberOfMillions($grandPrizeScrapper->jackpotHTML, $grandPrizeScrapper->numberLocaleFlag);

        $jackpot = null;
        if (!empty($numbersArray) && !empty($prizes)) {
            $grandPrizeWinnerCount = (int)$prizes[0][0];
            $drawJackpotFromPrizeTable = (int)$prizes[0][1];

            if ($grandPrizeWinnerCount >= 1 && $drawJackpotFromPrizeTable === 0) {
                $prizes[0][1] = strval($grandPrize / $grandPrizeWinnerCount * 1000000);
            }

            $jackpotScraper = Lotto_Scraperhtml::build('https://www.lotteryusa.com/powerball/')
                ->setInitialBoundaries('Next est. jackpot', 'c-state-short-info__subtitle--sub')
                ->setJackpotBoundaries('<div class="c-state-short-info__subtitle">', '<span');

            $jackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag);
        } else {
            throw new Exception('[First source] Cannot retrieve data for the Powerball lottery or the retrieved data is empty.');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getLotteryDataFromSecondSource(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries('<div class="container-fluid alt-bg marginTopMed">', '<h2>Power Play Prizes</h2>')
            ->setDrawDateBoundaries('<h1>Powerball Numbers for', '</h1>')
            ->setNumbersBoundaries('<ul class="balls">', '<li class="new power-play">')
            ->setJackpotBoundaries('Next Estimated Jackpot', '</span></div>')
            ->setPrizesBoundaries('<h2>Payouts</h2>', '</table>');

        $jackpot = ScraperHelper::getNumberOfMillions($scraper->jackpotHTML, $scraper->numberLocaleFlag);
        $numbersArray = $scraper->extractNumbers(5, 1);
        $prizes = $scraper->extractPrizesUsingDOM(2, 1);

        if (!strtotime($scraper->extractDrawDate())) {
            throw new Exception('[Second source] Invalid date format from scraper');
        }

        $drawDateTime = $this->prepareDrawDatetime($scraper->extractDrawDate());

        if (empty($jackpot) || empty($numbersArray[0]) || empty($numbersArray[1]) || empty($prizes) || empty($drawDateTime)) {
            throw new Exception('[Second source] Cannot retrieve data for the Powerball lottery or the retrieved data is empty.');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
