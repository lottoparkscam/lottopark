<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_UKLottery extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'lotto-uk';

    public function get_results(): void
    {
        $lottery = $this->lottery;
        try {
            $nextDrawDateFormatted = Carbon::parse($lottery['next_date_local'])->format('d-m-Y'); // todo: what about lotteries starting with null date?

            $scraper = Lotto_Scraperhtml::build("https://www.lottery.co.uk/lotto/results-$nextDrawDateFormatted", ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
                ->setInitialBoundaries('<h1>Lotto Results', '<tr class="totals">')
                ->setNumbersBoundaries('<span id="ballsAscending">', '<span id="ballsDrawn"')
                ->setDrawDateBoundaries('<h1>Lotto Results', '</h1>')
                ->setPrizesBoundaries('<h2>Prize Breakdown</h2>', '<tr class="totals">');

            $drawDate = $scraper->extractDrawDate();

            if (!strtotime($drawDate)) {
                throw new Exception($this->lottery_slug . ' - Invalid date format from scraper');
            }

            if (Carbon::parse($drawDate, $lottery['timezone'])->format('d-m-Y') !== $nextDrawDateFormatted) {
                throw new Exception($this->lottery_slug . ' - unable to find draw');
            }

            $numbersArray = $scraper->extractNumbers(6, 1);
            $prizes = $scraper->extractPrizesUsingDOM(1, 2);

            $jackpotScraper = Lotto_Scraperhtml::build('https://www.lottery.co.uk/lotto')
                ->setInitialBoundaries('<div class="bigJackpotWhite">', '<div class="jackpot-cap">')
                ->setJackpotBoundaries('<span class="mainJackpot">', '</span>');
            $jackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag);

            try {
                $this->validateResults([$jackpot, $drawDate, $numbersArray[0], $numbersArray[1], $prizes], 6, 1, [1, 59], [1, 59], 6);
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

            $drawDateTime = $this->prepareDrawDatetime($drawDate);
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
