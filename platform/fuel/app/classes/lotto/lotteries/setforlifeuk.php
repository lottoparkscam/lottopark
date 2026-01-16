<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

/**
 *
 */
class Lotto_Lotteries_SetForLifeUK extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'set-for-life-uk';

    public function get_results(): void
    {
        $lottery = $this->lottery;
        try {
            $nextDrawDateFormatted = Carbon::parse($lottery['next_date_local'])->format('d-m-Y'); // todo: what about lotteries starting with null date?

            $scraper = Lotto_Scraperhtml::build("https://www.lottery.co.uk/set-for-life/results-$nextDrawDateFormatted", ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
                ->setInitialBoundaries('<h1>Set For Life Results', '<tr class="totals">')
                ->setNumbersBoundaries('<span id="ballsAscending">', '</td>')
                ->setDrawDateBoundaries('<h1>Set For Life Results', '</h1>')
                ->setPrizesBoundaries('<table class="table setForLife"', '<tr class="totals">');

            $drawDate = $scraper->extractDrawDate();

            if (!strtotime($drawDate)) {
                throw new Exception($this->lottery_slug . ' - Invalid date format from scraper');
            }

            $drawDateTime = $this->prepareDrawDatetime($drawDate);

            if ($drawDateTime->format('d-m-Y') !== $nextDrawDateFormatted) {
                throw new Exception($this->lottery_slug . ' - unable to find draw');
            }

            $numbersArray = $scraper->extractNumbers(5, 1);
            $prizes = $scraper->extractPrizesUsingDOM(1, 3);
            $prizes = array_values($prizes); // todo: should be done by scrapper - it should always return array with keys starting with 0
            foreach ($prizes as &$prize) { // we fetched prize fund and need to calculate it per winners    
                $prize[1] = $prize[0] > 0 ? $prize[1] / $prize[0] : 0; // fund should be already zeroed for zero winners
            }

            $jackpotScraper = Lotto_Scraperhtml::build('https://www.lottery.co.uk/set-for-life', ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
                ->setInitialBoundaries('<td>5 + Life Ball</td>', '<td>1 in 15,339,390</td>')
                ->setJackpotBoundaries('<td>£', 'every month');
            $jackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag) * 360; // 10 000 per month for 30 years and then cast into millions.

            try {
                $this->validateResults([$jackpot, $drawDate, $numbersArray[0], $numbersArray[1], $prizes], 5, 1, [1, 47], [1, 10], 8);
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

            // todo: prizes are counted from 1 instead of 0 but it seems for now it works
            if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) { // TODO: {Vordis 2021-11-17 11:38:30} should be separate (jackpot update and draw insertion logic), but for that we need to rebuilt set_lottery_with_data
                $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbersArray[0], $numbersArray[1], $prizes, $this->overwrite_jackpot, false);
                return;
            }
            echo "jackpot update or draw insertion trigger condition not met.";
        } catch (\Throwable $e) {
            echo $e->__toString();
        }
    }
}
