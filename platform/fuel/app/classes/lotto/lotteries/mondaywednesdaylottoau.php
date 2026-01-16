<?php
use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_MondayWednesdayLottoAU extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'monday-wednesday-lotto-au';
    protected Carbon $nextDrawDate;

    public function get_results(): void
    {
        $this->nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

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

    private function processResults(array $data): void
    {
        try {
            [$numbersArray, $prizes, $drawDateTime, $jackpot] = $data;
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 6, 2, [1, 45], [1, 45], 6);
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

        if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) {
            $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbersArray[0], $numbersArray[1], $prizes, $this->overwrite_jackpot, false);
            return;
        }
        echo 'Jackpot update or draw insertion trigger condition not met.';
    }

    public function getDataPrimary(): array
    {
        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructureWithParameters('https://data.api.thelott.com/sales/vmax/web/data/lotto/latestresults', [
            'CompanyId' => 'NTLotteries',
            'MaxDrawCountPerProduct' => 1,
            'OptionalProductFilter' => ['MonWedLotto'],
        ]);

        $data = $scraper->getJsonStructure();
        $data = $data['DrawResults'];

        if (empty($data)) {
            throw new Exception($this->lottery_slug . ' - primary source unable to fetch results');
        }

        $data = $data[0];

        // Draw date
        $drawDateTime = Carbon::parse($data['DrawDate'], $this->lottery['timezone'])->setHour(20)->setMinute(30)->setSecond(0);

        if ($this->nextDrawDate->format('Ymd') !== $drawDateTime->format('Ymd')) {
            throw new Exception($this->lottery_slug . ' - primary source unable to find draw');
        }

        // Numbers
        $numbersArray = [
            $data['PrimaryNumbers'],
            $data['SecondaryNumbers'],
        ];

        // Prizes
        $prizes = $scraper->extractPrizes(['DrawResults', 0, 'Dividends'], 'BlocDividend', 'BlocNumberOfWinners');

        // Jackpot
        $jackpot = 1;

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    public function is_type_data_winning(
        Model_Lottery_Type $type,
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        int $match_b,
        int $match_others = 0
    ): bool
    {
        if ($match_b == 2 && $wintype->match_n == 3 && $wintype->match_b == 1) {
            $match_b = 1;
        } elseif ($match_b == 2 && $wintype->match_n == 5 && $wintype->match_b == 1) {
            $match_b = 1;
        } elseif ($match_b == 2 && $match_n == 2 && $wintype->match_n == 1 && $wintype->match_b == 2) {
            $match_n = 1;
        }

        return parent::is_type_data_winning($type, $wintype, $match_n, $match_b, $match_others) ||
            ($type['bextra'] == 2 && $wintype->match_n == $match_n &&
                ($wintype->match_b == 0 || ($wintype->match_b != 0 &&
                        $wintype->match_b == $match_b)));
    }
}
