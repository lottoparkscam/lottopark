<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_MiniMegaMillions extends Lotto_Lotteries_MegaMillions
{
    const JACKPOT_PERCENT = 10;
    const PRIZES_VALUE = [0, 100000, 1000, 50, 20, 1, 1, .4, .2];

    protected string $lottery_slug = Lottery::MINI_MEGA_MILLIONS_SLUG;
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
            if ($index == 0) {
                $prizes[$index] = [$prize[0], (float) $prize[1] / self::JACKPOT_PERCENT];
            } else {
                $prizes[$index] = [$prize[0], self::PRIZES_VALUE[$index]];
            }
        }

        $jackpot = $jackpot / self::JACKPOT_PERCENT;

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
