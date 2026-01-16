<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Models\Lottery;
use Services\LotteryProvider\TheLotterService;

class Lotto_Lotteries_Bonoloto extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = Lottery::BONOLOTO_SLUG;

    public function get_results(): void
    {
        $lottery = $this->lottery;

        try {
            $drawDate = Carbon::parse($lottery['next_date_local'])->format('Ymd');

            $scrapper = Lotto_Scraperhtml::build('https://www.elgordo.com/en/results/bonoloto', ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
                ->setInitialBoundaries('<div class="draw-result">', '<div class="aside">')
                ->setDrawDateBoundaries('<span class="main-date-draw c">', '</span>')
                ->setNumbersBoundaries('<span class="bet-break"><span class="numbers">', '<span class="text">R</span>')
                ->setPrizesBoundaries('<table class="new-table ">', '</table>');

            $theLotterService = Container::get(TheLotterService::class);
            $jackpotFromTheLotter = $theLotterService->getNextJackpot($this->lottery_slug);
            $jackpot = ScraperHelper::getNumberOfMillions($jackpotFromTheLotter);

            $numbers = $scrapper->extractNumbers(6, 2, false);
            $prizes = $scrapper->extractPrizesUsingDOM(1, 2);
            $drawDateExtracted = $scrapper->extractDrawDate();

            if (!strtotime($drawDateExtracted)) {
                throw new Exception('[First source] Invalid date format from scraper');
            }

            if (Carbon::parse($drawDateExtracted, $lottery['timezone'])->format('Ymd') !== $drawDate) {
                throw new Exception($this->lottery_slug . ' - unable to find draw');
            }

            $drawDateTime = $this->prepareDrawDatetime($drawDateExtracted);

            if (empty($jackpot) || empty($numbers[0]) || empty($numbers[1]) || empty($prizes) || empty($drawDateTime)) {
                throw new Exception('[First source] Cannot retrieve data for the Bonolotto lottery or the retrieved data is empty.');
            }

            try {
                $this->validateResults([$jackpot, $drawDate, $numbers[0], [$numbers[1][0]], $prizes, ['refund' => (int)$numbers[1][1]]], 6, 1, [1, 49], [1, 49], 6, ['refund', 0, 9]);
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

            if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) { // TODO: {Vordis 2021-11-17 11:38:30} should be separate (jackpot update and draw insertion logic), but for that we need to rebuilt set_lottery_with_data
                $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbers[0], [$numbers[1][0]], $prizes, $this->overwrite_jackpot, false, ['refund' => (int)$numbers[1][1]]); // reintegro bonus number is passed to additional data
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
        $additional_data_wintype = unserialize($wintype['additional_data']);
        $refund = $additional_data_wintype['refund'] ?? null;

        // Cound not it be done in simpler way?
        // For example sometimes is simpler to make false
        // conditions to return from function and otherwise return true
        return ($type['bextra'] == 1 &&
                $wintype->match_n == $match_n &&
                ($wintype->match_b == 0 || ($wintype->match_b != 0 &&
                    $wintype->match_b == $match_b)) &&
                !$additional_data_wintype) || // 3,4,5,5+1,6
            ($refund == 1 && $match_others == 1 && $wintype->match_n == $match_n) || // 6+R
            ($refund == 1 && $wintype->match_n == 0 && $match_others == 1);
    }

    public function match_others($match_others, $line): int
    {
        $slip = Model_Whitelabel_User_Ticket_Slip::find_by_id($line->whitelabel_user_ticket_slip_id);
        $additional_data_slip = unserialize($slip[0]->additional_data);
        $refund = $additional_data_slip['refund'] ?? null;

        if ($refund === $this->additional_data['refund'] &&
            $this->additional_data['refund'] !== null) {
            $match_others++;
        }

        return $match_others;
    }
}
