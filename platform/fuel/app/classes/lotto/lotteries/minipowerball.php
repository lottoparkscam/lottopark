<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Models\Lottery;

class Lotto_Lotteries_MiniPowerball extends Lotto_Lotteries_Powerball
{
    use Lotto_Hasscraping;

    const PRIZES_VALUE = [
        0 => 0,
        1 => 100000,
        2 => 5000,
        3 => 10,
        4 => 10,
        5 => 0.7,
        6 => 0.7,
        7 => 0.4,
        8 => 0.4,
    ];

    const JACKPOT_PERCENT = 10;

    protected string $lottery_slug = Lottery::MINI_POWERBALL_SLUG;

    public function get_results(): void
    {
        try {
            $data = $this->fetchLotteryData();
            $data = $this->getUpdatedResults($data);
            $this->processResults($data);
        } catch (Throwable $e) {
            echo $e->__toString();
        }
    }

    /**
     * @param array $data
     * @return array
     */
    public function getUpdatedResults(array $data): array
    {
        [$numbersArray, $scrapedPrizes, $drawDateTime, $jackpot] = $data;

        $prizes = [];
        foreach ($scrapedPrizes as $index => $prize) {
            if ($index == 0) {
                $prizes[$index] = [$prize[0], (int) $prize[1] / self::JACKPOT_PERCENT];
            } else {
                $prizes[$index] = [$prize[0], self::PRIZES_VALUE[$index]];
            }
        }

        //Jackpot is 10% of Powerball jackpot
        $jackpot = $jackpot / self::JACKPOT_PERCENT;

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
