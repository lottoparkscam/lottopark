<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_LottoAmerica extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'lotto-america';

    public function get_results(): void
    {
        $nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);
        if ($nextDrawDate->isFuture()) {
            echo "{$this->lottery_slug} - next draw date is in the future" . PHP_EOL;
            return;
        }

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
    private function fetchLotteryData(): array
    {
        try {
            return $this->getLotteryDataFromFirstSource();
        } catch (Exception $exception) {
            try {
                return $this->getLotteryDataFromSecondSource();
            } catch (Exception $exception) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'Both Sources',
                    exception: $exception,
                );
            }
        }
    }

    private function processResults($data): void
    {
        try {
            [$numbersArray, $prizes, $drawDateTime, $jackpot] = $data;
            $prizes = array_values($prizes);
    
            if (!$this->validateAndLogResults($jackpot, $drawDateTime, $numbersArray, $prizes)) {
                return;
            }

            $nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);
            if ($drawDateTime->format('dmY') !== $nextDrawDate->format('dmY')) {
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
                $this->lottery,
                $jackpot,
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
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 5, 1, [1, 52], [1, 10], 9);
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

    private function getLotteryDataFromFirstSource(): array
    {
        $lottery = $this->lottery;
        $drawDate = Carbon::parse($lottery['next_date_local'], $lottery['timezone'])->format('Y-m-d');

        $scraper = Lotto_Scraperhtml::build("https://www.powerball.com/draw-result?gc=lotto-america&date=$drawDate")
            ->setInitialBoundaries('<h1 id="draw-results">Draw Results</h1>', '<footer class="bg-light">')
            ->setDrawDateBoundaries('name="DrawDate" value="', '">')
            ->setNumbersBoundaries('game-ball-group mx-auto">', 'All Star Bonus')
            ->setPrizesBoundaries('"Lotto America Winners"', '</table>');

        $numbersArray = $scraper->extractNumbers(5, 1);
        $prizes = $scraper->extractPrizes(
            'Lotto America Winners',
            'Lotto America Prize',
            'Lotto America Prize',
            'All Star Bonus Winners',
        );

        $grandPrizeScrapper = $scraper
            ->setInitialBoundaries('Estimated Jackpot:', '</div>')
            ->setJackpotBoundaries('<span>', '</span>');

        $grandPrize = ScraperHelper::getNumberOfMillions($grandPrizeScrapper->jackpotHTML, $grandPrizeScrapper->numberLocaleFlag);
        if (!empty($numbersArray) && !empty($prizes)) {
            $grandPrizeWinnerCount = (int)$prizes[0][0];
            $drawJackpotFromPrizeTable = (int)$prizes[0][1];

            if ($grandPrizeWinnerCount >= 1 && $drawJackpotFromPrizeTable === 0) {
                $prizes[0][1] = strval($grandPrize / $grandPrizeWinnerCount * 1000000);
            }
        }

        $jackpotScraper = Lotto_Scraperhtml::build('https://www.lottoamerica.com', ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
            ->setJackpotBoundaries('<p>Next Estimated Jackpot:</p>', 'Time until draw:');
        $jackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag);

        $extractedDrawDate = $scraper->extractDrawDate();
        if (!strtotime($extractedDrawDate)) {
            throw new Exception('[First source] Invalid date format from scraper');
        }

        $drawDateTime = $this->prepareDrawDatetime($extractedDrawDate);

        if (empty($jackpot) || empty($numbersArray[0]) || empty($numbersArray[1]) || empty($prizes) || empty($drawDateTime)) {
            throw new Exception('[First source] Cannot retrieve data for the Lotto America lottery or the retrieved data is empty.');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    private function getLotteryDataFromSecondSource(): array
    {
        $lottery = $this->lottery;
        $drawDate = Carbon::parse($lottery['next_date_local'], $lottery['timezone'])->format('Y-m-d');

        $scraper = Lotto_Scraperhtml::build("https://www.lottoamerica.com/numbers/$drawDate", ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
            ->setInitialBoundaries('Lotto America Numbers for', 'Number of Winners in Each State')
            ->setNumbersBoundaries('<ul class="balls"', '</ul>')
            ->setDrawDateBoundaries('Lotto America Numbers for ', '</title>')
            ->setJackpotBoundaries('Next Estimated Jackpot:', '</div>')
            ->setPrizesBoundaries('<table ', '</table>');

        $extractedDrawDate = $scraper->extractDrawDate();
        if (!strtotime($extractedDrawDate)) {
            throw new Exception('[Second source] Invalid date format from scraper');
        }

        $drawDateTime = $this->prepareDrawDatetime($extractedDrawDate);

        $numbersArray = $scraper->extractNumbers(5, 1);
        $prizes = $scraper->extractPrizesUsingDOM(2, 1, 9);
        $jackpot = ScraperHelper::getNumberOfMillions($scraper->jackpotHTML, $scraper->numberLocaleFlag);

        if (empty($jackpot) || empty($numbersArray[0]) || empty($numbersArray[1]) || empty($prizes) || empty($drawDateTime)) {
            throw new Exception('[Second source] Cannot retrieve data for the Lotto America lottery or the retrieved data is empty.');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
