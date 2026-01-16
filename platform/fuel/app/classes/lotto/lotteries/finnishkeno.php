<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_FinnishKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 2; // 200000 * 10 (multiplier) / 1000000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Helsinki';

    protected string $lottery_slug = Lottery::FINNISH_KENO_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->isFuture()) {
            return;
        }
        
        try {
            $numbers = $this->get_numbers_primary();
            $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
            return;
        } catch (\Throwable $e) {
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'All Sources',
                exception: $e,
                nextDrawDateFormatted: $this->providerNextDrawDate->format('YmdHi'),
                delayInHours: 6,
            );
            echo $e->__toString();
        }
    }

    public function get_primary_json_results(): array
    {
        $drawDate = $this->providerNextDrawDate->format('Y-m-d');
        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://www.veikkaus.fi/api/draw-results/v1/games/KENO/draws/by-day/{$drawDate}");
        return $drawScraper->getJsonStructure() ?? [];
    }

    public function getNextDrawTime(string $nextDrawTime): string
    {
        switch($nextDrawTime) {
            case '15:00':
                return 'Keno Päiväarvonta';
                break;
            case '20:58':
                return 'Keno Ilta-arvonta';
                break;
            case '23:00':
                return 'Keno Myöhäisillan arvonta';
                break;
            default:
                throw new Exception($this->lottery_slug . ' - unable to get next draw time');
        }
    }

    public function get_numbers_primary(): array
    {
        $drawData = $this->get_primary_json_results();
        $nextDrawTime = $this->getNextDrawTime($this->providerNextDrawDate->format('H:i'));

        $drawId = -1;
        for ($index = 0; $index < count($drawData); $index++) {
            $areDatesEqual = $drawData[$index]['brandName'] === $nextDrawTime;
            if ($areDatesEqual) {
                $drawId = $index;
                break;
            }
        }

        if ($drawId === -1) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return $drawData[$drawId]['results'][0]['primary'] ?? [];
    }
}
