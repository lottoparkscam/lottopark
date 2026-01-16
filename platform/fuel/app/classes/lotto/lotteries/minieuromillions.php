<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_MiniEuromillions extends Lotto_Lotteries_Euromilions
{
    const PRIZE_PERCENT = 10;

    protected string $lottery_slug = Lottery::MINI_EUROMILLIONS_SLUG;
    protected Carbon $nextDrawDate;

    public function get_results(): void
    {
        $this->nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        if ($this->nextDrawDate->isFuture()) {
            echo "{$this->lottery_slug} - next draw date is in the future" . PHP_EOL;
            return;
        }

        try {
            $data = $this->fetchLotteryData();
            $data = $this->getUpdatedResults($data);
            $this->processResults($data);
        } catch (Throwable $e) {
            echo $e->__toString();
        }

    }

    public function getUpdatedResults(array $data): array
    {
        [$numbersArray, $scrapedPrizes, $drawDateTime, $jackpot] = $data;

        $prizes = [];
        foreach ($scrapedPrizes as $index => $prize) {
            $prizes[$index] = [$prize[0], (float) $prize[1] / self::PRIZE_PERCENT];
        }

        $jackpot = $jackpot / self::PRIZE_PERCENT;

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
