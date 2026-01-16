<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_SlovakKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 0.0072; // 1 200 * 6 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 80];
    public const PROVIDER_TIMEZONE = 'UTC';

    protected string $lottery_slug = Lottery::SLOVAK_KENO_SLUG;
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
                delayInHours: 2,
            );
            echo $e->__toString();
        }
    }

    public function get_primary_json_results(): array
    {
        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructureWithParameters('https://numbers-betradar.api-gaming-engine.com/api/betradar/pastdraws', [
            'drawType' => 'by-lottery',
            'gameSlug' => 'slovakia-eklub-keno-20-80',
            'account' => 'test-betradar-com',
        ]);
        $results = $drawScraper->getJsonStructure();
        return $results['results'] ?? [];
    }

    public function get_numbers_primary(): array
    {
        $data = $this->get_primary_json_results();

        $drawIndex = -1;
        for ($index = 0; $index < count($data); $index++) {
            $areDatesEqual = $data[$index]['draw_date'] === $this->providerNextDrawDate->copy()->subMinute()->format('d-m-Y H:i:00 +00:00');
            if ($areDatesEqual) {
                $drawIndex = $index;
                break;
            }
        }

        if ($drawIndex === -1) {
            $this->drawDateFix($this->providerNextDrawDate);
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return $data[$index]['balls'][0] ?? [];
    }
}
