<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_LottoAT extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'lotto-at';
    protected Carbon $nextDrawDate;

    public function get_results(): void
    {
        $this->nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        if ($this->nextDrawDate->isFuture()) {
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
            return $this->getDataPrimary();
        } catch (Exception $exception) {
            try {
                return $this->getDataSecondary();
            } catch (Exception $exception) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'Both Sources',
                    exception: $exception,
                );
                return [];
            }
        }
    }

    private function processResults(array $data): void
    {
        try {
            if (count($data) != 4) {
                throw new Exception($this->lottery_slug . ' - incomplete data in processResults');
            }
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
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 6, 1, [1, 45], [1, 45], 8);
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
    private function getDataPrimary(): array
    {
        $scrapper = Lotto_Scraperhtml::build(
            'https://www.lottohelden.de/lotto-6aus45/zahlen-quoten/',
            ScraperHelper::$NUMBER_LOCALE_DOT_COMMA
        )
            ->setInitialBoundaries('>Zahlen &amp; Quoten</p>', '</table>')
            ->setDrawDateBoundaries('LOTTO 6aus45 Zahlen vom', '</p>')
            ->setNumbersBoundaries('<div data-test="drawing-result" class="drawings-base"', '<div class="drawing-infos"')
            ->setPrizesBoundaries('Gewinn&shy;summe</span></th></tr></thead><tbody', '</table>')
            ->setJackpotBoundaries('Jackpot:', '€ </span>');

        // NUMBERS
        $numbersArray = $scrapper->extractNumbers(6, 1);

        // DRAW DATE
        $drawDateFromScraper = $scrapper->extractDrawDate();
        $drawDateFromScraper = preg_replace('/[a-zA-Z, ]/', '', $drawDateFromScraper);
        if (!strtotime($drawDateFromScraper)) {
            throw new Exception($this->lottery_slug . ' - invalid date format from scraper');
        }
        $drawDateTime = $this->prepareDrawDatetime($drawDateFromScraper, 'de_DE');

        // PRIZES
        $prizes = $scrapper->extractPrizes('€" class="current-data visible-small"', 'x<br>', 'x<br>', '€<\/abbr');

        // JACKPOT
        $jackpot = ScraperHelper::getNumberOfMillions($scrapper->jackpotHTML, $scrapper->numberLocaleFlag);
        if (empty($jackpot) || empty($numbersArray[0]) || empty($numbersArray[1]) || empty($prizes) || empty($drawDateTime)) {
            throw new Exception("[First source] Cannot retrieve data for the {$this->lottery_slug} lottery or the retrieved data is empty.");
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    public function getDataSecondary(): array
    {
        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.win2day.at/jam/drawgame/v1/public/drawResultInfo/lotto?payoutReleased=true&limit=1&sortBy=DATE_DESC');
        $data = $scraper->getJsonStructure();
        $data = $data['drawResults'];

        if (empty($data)) {
            throw new Exception($this->lottery_slug . ' - secondary source unable to fetch results');
        }

        $data = $data[0];

        // Draw date
        // API returns date as "2024-11-12" and time must be adjusted manually to 18:47:00 to match our database
        $drawDateTime = Carbon::parse($data['drawDate'], $this->lottery['timezone'])->setHour(18)->setMinute(47)->setSecond(0);

        // Fix hour for Sunday
        if ($drawDateTime->isSunday()) {
            $drawDateTime->setHour(19)->setMinute(17);
        }

        $areDatesEqual = $this->nextDrawDate->format('Ymd') !== $drawDateTime->format('Ymd');
        if ($areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - secondary source unable to find draw');
        }

        // Numbers
        $numbersArray = [
            $data['values'][0]['numbers'],
            $data['values'][1]['numbers'],
        ];

        // Prizes
        $prizes = $scraper->extractPrizes(['drawResults', 0, 'ranks'], 'amountPerWinner', 'numberOfWinners');
        $prizes = array_map(function ($innerArray) {
            return [$innerArray[0], $innerArray[1] / 100];
        }, $prizes);

        // Jackpot
        $jackpotScrapper = Lotto_Scraperhtml::build('https://www.magayo.com/lotto/austria/lotto-results/', ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
            ->setInitialBoundaries('Next Lotto Jackpot', '(~US')
            ->setJackpotBoundaries('&euro;', ' (~US');

        $jackpot = ScraperHelper::getNumberOfMillions($jackpotScrapper->jackpotHTML, $jackpotScrapper->numberLocaleFlag);

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
