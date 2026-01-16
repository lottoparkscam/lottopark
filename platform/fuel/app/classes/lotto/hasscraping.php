<?php

use Carbon\Carbon;
use Helpers\ArrayHelper;

/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2023-07-04
 * Time: 19:04:48
 */
trait Lotto_Hasscraping
{
    protected function validateResults(array $fetchedResultsArray, int $numbersCount, int $bonusNumbersCount, array $numbersRange, array $bonusNumbersRange, int $prizesCount, array $additionalDataRules = []): void
    { // todo: this validation should be done better
        [$jackpot, $drawDate, $numbers, $bonusNumbers, $prizes, $additionalData] = $fetchedResultsArray + [5 => null];

        $this->validation = $validation = Validation::forge(__CLASS__ . microtime());

        // DRAW DATE
        $validation->add('drawDate', 'drawDate', [], ['required', 'valid_date']);

        // PRIZES
        if ($this->isNotKeno()) {
            $validation->add('prizes', 'prizes', [], ['required', function (array $prizes) use ($prizesCount): bool {
                $totalWinners = 0;
                $totalPrizes = 0;
                foreach ($prizes as $prize) {
                    if (is_numeric($prize[0] ?? null) && is_numeric($prize[1] ?? null)) {
                        $totalWinners += $prize[0];
                        $totalPrizes += $prize[1];
                    }
                }
                $isPrizeDataCorrect = ($totalWinners > 0) && ($totalPrizes > 0);
                return count($prizes) === $prizesCount && $isPrizeDataCorrect;
            }]);
        }

        // IMPORTANT: this is jackpot for the next draw (should be set in lottery.current_jackpot).
        $validation->add('jackpot', 'jackpot', [], ['required', 'is_numeric', ['numeric_min', 0.001], ['numeric_max', 9999.99999999]]);

        // NUMBERS
        $validation->add('numbers', 'numbers', [], ['required', function (array $numbers) use ($numbersCount, $numbersRange): bool {
            foreach ($numbers as $number) {
                if (!($number >= $numbersRange[0] && $number <= $numbersRange[1])) {
                    return false;
                }
            }
            return ArrayHelper::countUniqueNumericValues($numbers) === $numbersCount;
        }]);

        // BONUS NUMBERS
        if ($bonusNumbersCount > 0) {
            $validation->add('bonusNumbers', 'bonusNumbers', [], ['required', function (array $bonusNumbers) use ($bonusNumbersCount, $bonusNumbersRange): bool {
                foreach ($bonusNumbers as $bonusNumber) {
                    if (!($bonusNumber >= $bonusNumbersRange[0] && $bonusNumber <= $bonusNumbersRange[1])) {
                        return false;
                    }
                }
                return ArrayHelper::countUniqueNumericValues($bonusNumbers) === $bonusNumbersCount;
            }]);
        }

        // ADDITIONAL DATA
        if (!empty($additionalData)) {
            $validation->add('additionalData', 'additionalData', [], [function (array $additionalData) use ($additionalDataRules): bool {
                $additionalDataKey = array_keys($additionalData)[0];
                $additionalDataValue = $additionalData[$additionalDataKey];
                if ($additionalDataKey !== $additionalDataRules[0] && $additionalDataValue <= $additionalDataRules[1] && $additionalDataValue >= $additionalDataRules[2]) {
                    return false;
                }
                return true;
            }]);
        }

        if (!$validation->run(
            [
                'drawDate' => $drawDate,
                'prizes' => $prizes,
                'jackpot' => $jackpot,
                'numbers' => $numbers,
                'bonusNumbers' => $bonusNumbers,
                'additionalData' => $additionalData,
            ]
        )) {
            throw new Exception("Unable to read fetched data: " . var_export([$this->lottery['slug'], $validation->error_message(), $drawDate, $numbers, $bonusNumbers, $jackpot, $prizes, $additionalData], true));
        }
    }

    /**
     * NOTE: this function expects validated input.
     */
    public function prepareDrawDatetime(string $drawDate, string $locale = 'en_US'): Carbon
    {
        $drawDatetime = Carbon::parseFromLocale($drawDate, $locale, $this->lottery['timezone']);
        foreach (json_decode($this->lottery['draw_dates'], true) as $draw_date) {
            $isTheSameAsDrawDay = strpos($draw_date, $drawDatetime->shortDayName) !== false;
            if ($isTheSameAsDrawDay) {
                $drawTime = explode(' ', $draw_date)[1];
                break;
            }
        }
        if (!isset($drawTime)) {
            throw new \InvalidArgumentException('unable to find draw time');
        }
        return $drawDatetime->setTimeFromTimeString($drawTime);
    }

    public function shouldProcessJackpot(string $jackpot): bool
    {
        return $this->checkIfJackpotIsOutdated($jackpot) || $this->lottery['draw_jackpot_set'] === '0';
    }

    public function shouldInsertDrawAndPrizes(Carbon $drawDatetime): bool
    {
        if ($this->lottery['next_date_local'] === null) {
            return true; // (fresh instance of lottery)
        }

        $retrievedDateIsElapsed = $drawDatetime->isPast(); // in other words it is past draw time.
        $nextDateLocalPointsToRetrievedDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->toString() === $drawDatetime->toString();
        return $retrievedDateIsElapsed && $nextDateLocalPointsToRetrievedDate;
    }

    public function shouldUpdateLottery(Carbon &$drawDatetime, string $jackpot): bool
    {
        $this->isDrawDateDue = $shouldInsertDrawAndPrizes = $this->shouldInsertDrawAndPrizes($drawDatetime);
        $this->overwrite_jackpot = $shouldUpdateJackpot = $this->shouldProcessJackpot($jackpot);

        return $shouldInsertDrawAndPrizes || $shouldUpdateJackpot;
    }

    public function process_lottery(): void
    {
        if ($this->isDrawDateDue) {
            $this->lottery_to_update->last_date_local = $this->lottery_to_update->next_date_local;
        }
        parent::process_lottery();
    }
}
