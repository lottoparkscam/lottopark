<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_Elgordo extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'el-gordo-primitiva';

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
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Both Sources',
                exception: $exception,
            );
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
                $this->lottery, $jackpot,
                $drawDateTime,
                $drawDateTime->clone()->setTimezone('UTC'),
                $numbersArray[0],
                [],
                $prizes,
                $this->overwrite_jackpot,
                false,
                ['refund' => (int)$numbersArray[1]]
            );
            return;
        }
        echo 'jackpot update or draw insertion trigger condition not met.';
    }

    private function validateAndLogResults($jackpot, $drawDateTime, $numbersArray, $prizes): bool
    {
        try {
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], [], $prizes, ['refund' => (int)$numbersArray[1]]], 5, 0, [1, 54], [], 9, ['refund', 0, 9]);
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
    private function getLotteryDataFromFirstSource(): array
    {
        $drawDate = Carbon::parse($this->lottery['next_date_local'])->format('Y-m-d');

        $scraper = Lotto_Scraperhtml::build("https://www.lotteryextreme.com/el-gordo-de-la-primitiva/winning_numbers($drawDate)", ScraperHelper::$NUMBER_LOCALE_DOT_COMMA)
            ->setInitialBoundaries("table class='okno'>", '</body>')
            ->setDrawDateBoundaries('<h1>Results El Gordo de La Primitiva<br>&nbsp; ', '  &nbsp; ')
            ->setNumbersBoundaries("<ul class='displayball'>", "<table class='tbsg'")
            ->setPrizesBoundaries("<table class='tbsg'", '</table>');

        $numbers = $scraper->getnumbersHTML();
        $numbers = str_replace('<li>', ',', $numbers);
        $numbers = strip_tags($numbers);
        $numbers = preg_replace("/[^0-9,]/", "", $numbers);
        $numbers = preg_replace("/,+/", ",", $numbers);
        $numbers = trim($numbers, ',');
        $numbers = explode(',', $numbers);
        $numbers = array_map('intval', $numbers);

        $reintegro = [(int)$numbers[5]];
        unset($numbers[5]);

        $numbersArray = [$numbers, $reintegro];
        $prizes = $scraper->extractPrizesUsingDOM(1, 2);
        unset($prizes[10]);

        $date = $scraper->extractDrawDate();
        $date = str_replace('/', '-', $date);

        if (!strtotime($date)) {
            throw new Exception('[First source] Invalid date format from scraper');
        }
        $drawDateTime = $this->prepareDrawDatetime($date);

        // JACKPOT SCRAPER
        $jackpotScraper = Lotto_Scraperhtml::build('https://www.elgordo.com/es/resultados/gordo-primitiva')
            ->setInitialBoundaries('<span class="jackpot">', '</div>')
            ->setJackpotBoundaries('<span class="amount">', '</div>');
        $jackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag);
        $jackpot *= 1000000;

        if (empty($jackpot) || empty($numbersArray[0]) || empty($numbersArray[1]) || empty($prizes) || empty($drawDateTime)) {
            throw new Exception('[First source] Cannot retrieve data for the Powerball lottery or the retrieved data is empty.');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot
        ];
    }

    public function is_type_data_winning(
        Model_Lottery_Type $type,
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        int $match_b,
        int $match_others = 0
    ): bool {
        $additional_data_wintype = unserialize($wintype['additional_data']);
        $refund = $additional_data_wintype['refund'] ?? null;

        return ($type['bextra'] == 0 && $wintype->match_n == $match_n &&
                ($wintype->match_b == 0 || ($wintype->match_b != 0 &&
                    $wintype->match_b == $match_b)) &&
                !$additional_data_wintype) // 3,4,5,5+1,6
            || ($refund == 1 && $match_others == 1 && $wintype->match_n == $match_n) // 6+R
            || ($refund == 1 && $wintype->match_n == 0 && $match_others == 1); // 0+R
    }

    public function match_others($match_others, $line): int
    {
        $slip = Model_Whitelabel_User_Ticket_Slip::find_by_id($line->whitelabel_user_ticket_slip_id);
        $additional_data_slip = unserialize($slip[0]->additional_data);
        $refund = $additional_data_slip['refund'] ?? null;

        if ($refund === $this->additional_data['refund'] &&
            $this->additional_data['refund'] !== null
        ) {
            $match_others++;
        }
        
        return $match_others;
    }

    protected function set_additional_data(object $final_draw): void
    {
        if(isset($final_draw->numbers->key)){
            $this->additional_data['refund'] = $final_draw->numbers->key;
        }
    }
}
