<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Models\Lottery;

class Lotto_Lotteries_MegaSena extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = Lottery::MEGA_SENA_SLUG;

    public function get_results(): void
    {
        $lottery = $this->lottery;

        try {

            // todo: we take last draw only. we could search for past draws - listing contains 180 days
            $linkScrapper = Lotto_Scraperhtml::build("https://www.megasena.com/en/past-results")
                ->setDrawDateBoundaries('<strong><a href="/en/results/', '"');
            $drawNumber = $linkScrapper->extractDrawDate();

            $scrapper = Lotto_Scraperhtml::build("https://www.megasena.com/en/results/$drawNumber")
                ->setInitialBoundaries('<h1>Mega Sena ', '>Prize Winners in each State')
                ->setDrawDateBoundaries("Mega Sena $drawNumber Result: <span>", '</span>')
                ->setJackpotBoundaries('The estimated jackpot for ', 'Time Remaining') // todo: THIS IS NOT next jackpot to be set at lottery. it is draw jackpot only.
                ->setNumbersBoundaries('id="ballsAscending">', '</ul>')
                ->setPrizesBoundaries('Prize Breakdown', '</tbody>');

            $drawDate = $scrapper->extractDrawDate();

            $drawDate = DateTime::createFromFormat('d/m/Y', $drawDate);
            $drawDateTime = $this->prepareDrawDatetime($drawDate->format('d-m-Y'));

            $nextDrawDate = Carbon::parse($lottery['next_date_local'], $lottery['timezone']);
            if ($drawDateTime->format('dmY') !== $nextDrawDate->format('dmY')) {
                throw new Exception($this->lottery_slug . ' - unable to find draw');
            }

            $jackpot = ScraperHelper::getNumberOfMillions($scrapper->jackpotHTML, $scrapper->numberLocaleFlag);
            $numbersArray = $scrapper->extractNumbers(6, 0);
            $prizes = $scrapper->extractPrizesUsingDOM(2, 1);

            try {
                $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 6, 0, [1, 60], [], 3);
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
            echo "jackpot update or draw insertion trigger condition not met.";
        } catch (\Throwable $e) {
            echo $e->__toString();
        }
    }
}
