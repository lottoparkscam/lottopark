<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

/**
 *
 */
class Lotto_Lotteries_SaturdayLottoAU extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'saturday-lotto-au';

    public function get_results(): void
    {
        $lottery = $this->lottery;
        try {
            $jackpotScraper  = Lotto_Scraperapi::build()
                ->fetchJsonDataStructureWithParameters('https://data.api.thelott.com/sales/vmax/web/data/lotto/opendraws', [
                    "CompanyId" => 'NTLotteries',
                    "MaxDrawCount" => 1,
                    "OptionalProductFilter" => ["TattsLotto"]
                ]);

            $jackpot = $jackpotScraper->extractJackpot(['Draws', 0, 'Div1Amount']);
            $jackpot = ScraperHelper::getNumberOfMillions($jackpot);

            $drawScraper  = Lotto_Scraperapi::build()
                ->fetchJsonDataStructureWithParameters('https://data.api.thelott.com/sales/vmax/web/data/lotto/latestresults', [
                    "CompanyId" => 'NTLotteries',
                    "MaxDrawCountPerProduct" => 1, // todo: past draws
                    "OptionalProductFilter" => ["TattsLotto"]
                ]);

            $numbersArray = $drawScraper->extractNumbers(
                ['DrawResults', 0, 'PrimaryNumbers'],
                ['DrawResults', 0, 'SecondaryNumbers'],
                6,
                2
            ); // todo: it might be good to be able to set basic indice - eg DrawResults here
            $prizes = $drawScraper->extractPrizes(['DrawResults', 0, 'Dividends'], 'BlocDividend', 'BlocNumberOfWinners');
            $drawDate = $drawScraper->extractDrawDate(['DrawResults', 0, 'DrawDate']);

            if (Carbon::parse($drawDate, $lottery['timezone'])->format('dmY') !== Carbon::parse($lottery['next_date_local'])->format('dmY')) {
                throw new Exception($this->lottery_slug . ' - unable to find draw');
            }

            try {
                $this->validateResults([$jackpot, $drawDate, $numbersArray[0], $numbersArray[1], $prizes], 6, 2, [1, 45], [1, 45], 6);
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

    public function is_type_data_winning(
        Model_Lottery_Type $type,
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        int $match_b,
        int $match_others = 0
    ): bool {
        if ($match_b == 2 && $wintype->match_n == 3 && $wintype->match_b == 1) {
            $match_b = 1;
        } elseif ($match_b == 2 && $wintype->match_n == 5 && $wintype->match_b == 1) {
            $match_b = 1;
        } elseif ($match_b == 2 && $match_n == 2 && $wintype->match_n == 1 && $wintype->match_b == 2) {
            $match_n = 1;
        }

        return parent::is_type_data_winning($type, $wintype, $match_n, $match_b, $match_others) ||
            ($type['bextra'] == 2 &&
                $wintype->match_n == $match_n && ($wintype->match_b == 0 ||
                    ($wintype->match_b != 0 && $wintype->match_b == $match_b)));
    }
}
