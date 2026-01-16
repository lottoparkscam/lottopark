<?php
use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_EuromillionsSuperdraw extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'euromillions-superdraw';
    protected Carbon $nextDrawDate;

    public function get_results(): void
    {
        // REMOVE WHEN MANUALLY ADDING RESULTS
        return;

        $this->nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        try {
            $results = [
                'numbersArray' => [
                    'regular' => [1,2,3,4,5], // 5 numbers
                    'bonus' => [1,2], // 2 numbers
                ],
                'prizes' => [
                    /**
                     * Prizes should be fetched from Totals table
                     */
                    // [count, prize]
                    [0, 0], // 5-2 JACKPOT
                    [0, 0], // 5-1
                    [0, 0], // 5-0
                    [0, 0], // 4-2
                    [0, 0], // 4-1
                    [0, 0], // 3-2
                    [0, 0], // 4-0
                    [0, 0], // 2-2
                    [0, 0], // 3-1
                    [0, 0], // 3-0
                    [0, 0], // 1-2
                    [0, 0], // 2-1
                    [0, 0], // 2-0
                ],
                'drawDateTime' => Carbon::parse('2024-09-27 18:00', 'Europe/Brussels'), // next draw date
                'jackpot' => 150, // in millions
            ];

            $this->processResults($results);
        } catch (Throwable $e) {
            echo $e->__toString();
        }
    }

    private function processResults(array $data): void
    {
        if (empty($data)) {
            throw new Exception($this->lottery_slug . ' - no data to process');
        }

        try {
            $numbersArray = $data['numbersArray'];
            $prizes = $data['prizes'];
            $drawDateTime = $data['drawDateTime'];
            $jackpot = $data['jackpot'];

            $this->validateResults([$jackpot, $drawDateTime, $numbersArray['regular'], $numbersArray['bonus'], $prizes], 5, 2, [1, 50], [1, 12], 13);
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
            $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbersArray['regular'], $numbersArray['bonus'], $prizes, $this->overwrite_jackpot, false);
            return;
        }
        echo 'Jackpot update or draw insertion trigger condition not met.';
    }
}
