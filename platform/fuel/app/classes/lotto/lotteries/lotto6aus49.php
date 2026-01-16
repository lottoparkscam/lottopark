<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

/**
 *
 */
class Lotto_Lotteries_Lotto6Aus49 extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'lotto-6aus49';
    protected $download_draw_hour_delay_limit = 24;

    public function get_results(): void
    {
        try {
            $scrapper = Lotto_Scraperhtml::build(
                'https://www.westlotto.de/lotto-6aus49/gewinnzahlen/gewinnzahlen.html',
                ScraperHelper::$NUMBER_LOCALE_DOT_COMMA
            )
                ->setInitialBoundaries('Ergebnisse vom ', 'LOTTO 6aus49 Ziehungsvideo')
                ->setDrawDateBoundaries(', den', '</span>')
                ->setNumbersBoundaries('Gezogene Reihenfolge</p>', '</ul>')
                ->setPrizesBoundaries('<table class="table table--foldable is-foldable"', '</table>');

            $numbersArray = $scrapper->extractNumbers(6, 0);
            $prizes = $scrapper->extractPrizesUsingDOM(1, 2);
            $drawDate = $scrapper->extractDrawDate();

            if (!strtotime($drawDate)) {
                throw new Exception($this->lottery_slug . ' - invalid date format from scraper');
            }

            $nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);
            if (Carbon::parse($drawDate, $this->lottery['timezone'])->format('dmY') !== $nextDrawDate->format('dmY')) {
                throw new Exception($this->lottery_slug . ' - unable to find draw');
            }

            $jackpotScrapper = Lotto_Scraperhtml::build(
                'https://www.westlotto.de/lotto-6aus49/gewinnzahlen/gewinnzahlen.html',
                ScraperHelper::$NUMBER_LOCALE_DOT_COMMA
            )
                ->setInitialBoundaries('<div class="prize-level__body">', '</div>')
                ->setJackpotBoundaries('<p class="prize-level__sum">', '<sup>');

            $jackpot = ScraperHelper::getNumberOfMillions($jackpotScrapper->jackpotHTML, $jackpotScrapper->numberLocaleFlag);

            $superNumberScrapper = Lotto_Scraperhtml::build(
                'https://www.westlotto.de/lotto-6aus49/gewinnzahlen/gewinnzahlen.html',
                ScraperHelper::$NUMBER_LOCALE_DOT_COMMA
            )
                ->setInitialBoundaries('Superzahl', 'Gezogene Reihenfolge')
                ->setNumbersBoundaries('Superzahl', 'Gezogene Reihenfolge');
            $superNumber = $superNumberScrapper->extractNumbers(0, 1); // todo: add validation for extra/super number

            try {
                $this->validateResults(
                    [
                        $jackpot,
                        $drawDate,
                        $numbersArray[0],
                        $numbersArray[1],
                        $prizes,
                        ['super' => (int)$superNumber[1][0]]
                    ],
                    6,
                    0,
                    [1, 49],
                    [],
                    9,
                    ['super', 0, 9]
                );
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
            if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) {
                $this->set_lottery_with_data(
                    $this->lottery,
                    $jackpot,
                    $drawDateTime,
                    $drawDateTime->clone()->setTimezone('UTC'),
                    $numbersArray[0],
                    $numbersArray[1],
                    $prizes,
                    $this->overwrite_jackpot,
                    false,
                    ['super' => (int)$superNumber[1][0]]
                );

                return;
            }

            echo 'jackpot update or draw insertion trigger condition not met.';
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
        $super = $additional_data_wintype['super'] ?? null;
        return ($type['bextra'] == 0 && $wintype->match_n == $match_n &&
            ($wintype->match_b == 0 || ($wintype->match_b != 0 &&
                $wintype->match_b == $match_b)) &&
            !$additional_data_wintype) // 6,5,4,3,2
            || ($super == 1 &&
                $match_others == 1 &&
                $wintype->match_n == $match_n); // 6,5,4,3,2 + s
    }

    public function match_others($match_others, $line): int
    {
        $slip = Model_Whitelabel_User_Ticket_Slip::find_by_id($line->whitelabel_user_ticket_slip_id);
        $additional_data_slip = unserialize($slip[0]->additional_data);
        $super = $additional_data_slip['super'] ?? null;

        if (
            $super == $this->additional_data['super'] &&
            $this->additional_data['super'] !== null
        ) {
            $match_others++;
        }

        return $match_others;
    }
}
