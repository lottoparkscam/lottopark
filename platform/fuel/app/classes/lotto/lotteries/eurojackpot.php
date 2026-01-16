<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_Eurojackpot extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'eurojackpot';
    protected Carbon $nextDrawDate;

    public function get_results(): void
    {
        $this->nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        if ($this->nextDrawDate->isFuture()) {
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

    protected function fetchLotteryData(): array
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

    protected function processResults($data): void
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
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 5, 2, [1, 50], [1, 12], 12);
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

    public function getPrimaryJsonResults(): array
    {
        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://api.opap.gr/draws/v3.0/5149/last/2");
        $data = $scraper->getJsonStructure();
        return $data ?? [];
    }

    public function getDataPrimary(): array
    {
        $data = $this->getPrimaryJsonResults();

        if (empty($data)) {
            throw new Exception($this->lottery_slug . ' - [primary source] unable to fetch results');
        }

        $drawIndex = -1;
        for ($index = 0; $index < count($data); $index++) {
            if (isset($data[$index]) && is_array($data[$index]) && isset($data[$index]['status']) && isset($data[$index]['drawTime'])) {
                $isStatusValid = $data[$index]['status'] === 'results';
                $areDatesEqual = Carbon::createFromTimestamp($data[$index]['drawTime'] / 1000, 'UTC')->setTimezone($this->lottery['timezone'])->format('YmdHi') === $this->nextDrawDate->format('YmdHi');
                if ($isStatusValid && $areDatesEqual) {
                    $drawIndex = $index;
                    break;
                }
            } else {
                throw new Exception($this->lottery_slug . ' - [primary source] Invalid data in API');
            }
        }

        if ($drawIndex === -1) {
            throw new Exception($this->lottery_slug . ' - [primary source] unable to find draw');
        }

        $draw = $data[$drawIndex];
        
        // Draw date
        $drawDateTime = Carbon::createFromTimestamp($data[$index]['drawTime'] / 1000)->setTimezone($this->lottery['timezone']);
        
        // Numbers
        $numbersArray = [
            $draw['winningNumbers']['list'],
            $draw['winningNumbers']['bonus'],
        ];

        // Prizes
        $prizes = [];
        foreach($draw['prizeCategories'] as $tier) {
            if ($tier['id'] === 13) {
                break;
            }
            $prizes[] = [$tier['winnersAll'], $tier['divident']];
        }

        // Jackpot
        $jackpot = 0;
        if ($data[0]['status'] === 'active') {
            $jackpot = $data[0]['prizeCategories'][0]['jackpot'] / 1000000;
        }
        
        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
