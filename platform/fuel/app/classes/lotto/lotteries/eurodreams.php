<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Models\Lottery;

class Lotto_Lotteries_EuroDreams extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = Lottery::EURODREAMS_SLUG;

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
    private function fetchLotteryData(): array
    {
        try {
            return $this->getDataPrimary();
        } catch (Exception $exception) {
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'All Sources',
                exception: $exception,
            );
        }
    }

    public function getDataPrimary(): array
    {
        $nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);
        $nextDrawDateFormatted = $nextDrawDate->format('Ymd');
        $lastDrawDate = Carbon::parse($this->lottery['last_date_local'], $this->lottery['timezone']);
        $lastDrawDateFormatted = $lastDrawDate->format('Ymd');

        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.win2day.at/jam/drawgame/v1/public/drawResultInfo/eurodreams?payoutReleased=true&limit=1&sortBy=DATE_DESC');
        $data = $scraper->getJsonStructure();
        $data = $data['drawResults'];

        if (empty($data)) {
            throw new Exception('EuroDreams - primary source unable to fetch results');
        }

        $data = $data[0];

        // Draw date
        // API returns date as "2024-02-12" and time must be adjusted manually to 21:00:00 to match our database
        $drawDateTime = Carbon::parse($data['drawDate'], $this->lottery['timezone'])->setHour(21)->setMinute(0)->setSecond(0);
        $drawDateTimeFormatted = $drawDateTime->format('Ymd');

        if (
            $nextDrawDateFormatted !== $drawDateTimeFormatted &&
            $lastDrawDateFormatted !== $drawDateTimeFormatted
        ) {
            throw new Exception('EuroDreams - primary source unable to find draw');
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

        // Jackpot - 20 000,00 EUR for 30 years (360 months) - Amount from API is in cents (20 000 00)
        $jackpot = $data['ranks'][0]['payoutRates']['amount'] * 3.6 / 1000000;

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    private function processResults(array $data): void
    {
        try {
            [$numbersArray, $prizes, $drawDateTime, $jackpot] = $data;
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 6, 1, [1, 40], [1, 5], 6);
        } catch (Throwable $exception) {
            if (empty($jackpot)) {
                ScraperHelper::sendEmptyJackpotErrorAfterSixHours($jackpot, $this->lottery_slug);
                return;
            }

            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Validation',
                exception: $exception,
            );
            return;
        }

        if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) { // TODO: {Vordis 2021-11-17 11:38:30} should be separate (jackpot update and draw insertion logic), but for that we need to rebuilt set_lottery_with_data
            $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbersArray[0], $numbersArray[1], $prizes, $this->overwrite_jackpot, false);
            return;
        }
        echo 'Jackpot update or draw insertion trigger condition not met.';
    }

    public function is_type_data_winning(
        Model_Lottery_Type $type,
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        int $match_b,
        int $match_others = 0
    ): bool {
        if (
            // Match 6 & 1
            $wintype->match_n == 6 && 
            $wintype->match_b == 1 &&
            $wintype->match_n == $match_n &&
            $wintype->match_b == $match_b
        ) {
            return true;
        } elseif (
            // Match 6 & 0
            $wintype->match_n == 6 && 
            $wintype->match_b == 0 &&
            $wintype->match_n == $match_n &&
            $wintype->match_b == $match_b
        ) {
            return true;
        } elseif (
            // Match 5,4,3,2 and bonus 0 || 1
            ($wintype->match_n == 5 || $wintype->match_n == 4 || $wintype->match_n == 3 || $wintype->match_n == 2) &&
            $wintype->match_n == $match_n
        ) {
            return true;
        } else {
            return false;
        }
    }
}
