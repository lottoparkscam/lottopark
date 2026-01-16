<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;

class Lotto_Lotteries_LottoFR extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    const DAYS = [
        1 => 'lundi',
        2 => 'mardi',
        3 => 'mercredi',
        4 => 'jeudi',
        5 => 'vendredi',
        6 => 'samedi',
        7 => 'dimanche',
    ];

    const MONTHS = [
        1 => 'janvier',
        2 => 'février',
        3 => 'mars',
        4 => 'avril',
        5 => 'mai',
        6 => 'juin',
        7 => 'juillet',
        8 => 'août',
        9 => 'septembre',
        10 => 'octobre',
        11 => 'novembre',
        12 => 'décembre',
    ];

    protected Carbon $providerNextDrawDate;
    protected array $providerNextDrawDateFragments;
    protected string $lottery_slug = 'lotto-fr';

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
                $numbersArray[1],
                $prizes,
                $this->overwrite_jackpot
            );
            return;
        }

        echo 'jackpot update or draw insertion trigger condition not met.';
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
            try {
                return $this->getLotteryDataFromSecondSource();
            } catch (Exception $exception) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'Both Sources',
                    exception: $exception,
                );
            }
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getLotteryDataFromFirstSource(): array
    {
        $lottery = $this->lottery;
        $drawDate = Carbon::parse($lottery['next_date_local'])->format('F-d-Y');

        $scraper = Lotto_Scraperhtml::build('https://www.lotto.net/french-loto/results/' . strtolower($drawDate), ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
            ->setInitialBoundaries('<h1>French Loto Results for', '<footer>')
            ->setDrawDateBoundaries('<div class="date">Draw Date', '</div>')
            ->setNumbersBoundaries('<div class="table-balls">', '</ul>')
            ->setJackpotBoundaries('<div class="box current">', '</span>')
            ->setPrizesBoundaries('<tr><td align="left"><strong>Match 5 plus Bonus</strong>', '<strong>Totals</strong>');

        $jackpot = ScraperHelper::getNumberOfMillions($scraper->jackpotHTML, $scraper->numberLocaleFlag);
        $numbersArray = $scraper->extractNumbers(5, 1);
        $prizes = $scraper->extractPrizesUsingDOM(2, 1);
        $prizes = array_values($prizes);

        if (!strtotime($scraper->extractDrawDate())) {
            throw new Exception('[First source] Invalid date format from scraper');
        }

        $drawDateTime = $this->prepareDrawDatetime($scraper->extractDrawDate());

        if (empty($jackpot) || empty($numbersArray[0]) || empty($numbersArray[1]) || empty($prizes) || empty($drawDateTime)) {
            throw new Exception('[First source] Cannot retrieve data for the LottoFrance lottery or the retrieved data is empty.');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getLotteryDataFromSecondSource(): array
    {
        throw new Exception('[Second source] Temporarily disabled');

        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        $lottery = $this->lottery;
        $drawDate = Carbon::parse($lottery['next_date_local'])->format('F-d-Y');

        $this->setProviderNextDrawDateFragments();
        $datetime = $this->removeFrenchAccents("{$this->providerNextDrawDateFragments['dayName']}-{$this->providerNextDrawDateFragments['dayNumber']}-{$this->providerNextDrawDateFragments['monthName']}-{$this->providerNextDrawDateFragments['year']}"); // dimanche-24-septembre-2023

        $scraper = Lotto_Scraperhtml::build("https://www.fdj.fr/jeux-de-tirage/loto/resultats/lundi-15-avril-2024", ScraperHelper::$NUMBER_LOCALE_SPACE_COMMA);

        // DATE
        $dateScraper = $scraper
            ->setInitialBoundaries('id="datepicker"', '</div>')
            ->setDrawDateBoundaries('</svg>', '</button>');
        $drawDate = $dateScraper->extractDrawDate();
        $drawDate = str_replace('/', '-', $drawDate);
        $drawDateTime = $this->prepareDrawDatetime($drawDate);

//        if ($drawDateTime->format('dmY') !== $this->providerNextDrawDate->format('dmY')) {
//            return;
//        }

        // JACKPOT
        $jackpotScraper = $scraper
            ->setInitialBoundaries('id="title-pdj-loto"', '</sup>')
            ->setJackpotBoundaries('text-display-5">', '</div>');
        $jackpot = ScraperHelper::getNumberOfMillions(str_replace('text-display-5">', '', $jackpotScraper->jackpotHTML), $jackpotScraper->numberLocaleFlag);

        // NUMBERS
        $numbers = $scraper
            ->setInitialBoundaries('<div data-theme="loto" id="result-grid-1"', '</div>')
            ->setNumbersBoundaries('<ul', '</ul>')
            ->getNumbersHTML();

        $numbers = str_replace('</h4>', ',', $numbers);
        $numbers = strip_tags($numbers);
        $numbers = explode(',', rtrim($numbers, ','));
        $numbers = array_map('intval', $numbers);

        // BONUS NUMBERS
        $bonusNumbers = $scraper
            ->setInitialBoundaries('<div data-theme="loto" id="result-grid-1"', '</div>')
            ->setNumbersBoundaries('</svg>', '</ul>')
            ->getNumbersHTML();

        $bonusNumbers = str_replace('</h5>', ',', $bonusNumbers);
        $bonusNumbers = (int)strip_tags($bonusNumbers);

        $numbersArray = [$numbers, [$bonusNumbers]];

        // PRIZES
        $prizesScraper = $scraper
            ->setInitialBoundaries('<table id="distribution-table"', '</table>')
            ->setPrizesBoundaries('<table id="distribution-table"', '</table>');

        $prizes = $prizesScraper->extractPrizesUsingDOM(1, 2);


        if (!strtotime($scraper->extractDrawDate())) {
            throw new Exception('[First source] Invalid date format from scraper');
        }

        $drawDateTime = $this->prepareDrawDatetime($scraper->extractDrawDate());

        if (empty($jackpot) || empty($numbersArray[0]) || empty($numbersArray[1]) || empty($prizes) || empty($drawDateTime)) {
            throw new Exception('[First source] Cannot retrieve data for the LottoFrance lottery or the retrieved data is empty.');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    private function validateAndLogResults($jackpot, $drawDateTime, $numbersArray, $prizes): bool
    {
        try {
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 5, 1, [1, 69], [1, 26], 9);
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

    public function setProviderNextDrawDateFragments(): void
    {
        $this->providerNextDrawDateFragments = [
            'dayName' => self::DAYS[$this->providerNextDrawDate->dayOfWeekIso],
            'dayNumber' => $this->providerNextDrawDate->format('d'),
            'dayNumberWithoutZeros' => $this->providerNextDrawDate->format('j'),
            'monthName' => self::MONTHS[$this->providerNextDrawDate->month],
            'year' => $this->providerNextDrawDate->year,
        ];
    }

    public function removeFrenchAccents(string $input): string
    {
        $accents = [
            'é' => 'e',
            'û' => 'u',
        ];

        return strtr($input, $accents);
    }

    public function is_type_data_winning(
        Model_Lottery_Type $type,
        Model_Lottery_Type_Data $wintype,
        int $match_n,
        int $match_b,
        int $match_others = 0
    ): bool {
        return parent::is_type_data_winning($type, $wintype, $match_n, $match_b, $match_others) ||
            ($wintype->match_n == 0 && $wintype->match_b == 1 && $match_n == 1 && $match_b == 1);
    }
}
