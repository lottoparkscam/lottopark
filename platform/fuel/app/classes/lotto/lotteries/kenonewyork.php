<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_KenoNewYork extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 1; // 100 000 * 10 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 80];
    public const PROVIDER_TIMEZONE = 'America/New_York';

    protected string $lottery_slug = Lottery::KENO_NEW_YORK_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->isFuture()) {
            return;
        }

        try {
            $numbers = $this->getNumbersPrimary();
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

    public function getPrimaryJsonResults(int $page): array
    {
        $dateLimit = $this->providerNextDrawDate->copy()->subDays(3)->timestamp;

        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://nylottery.ny.gov/drupal-api/api/v2/winning_numbers?_format=json&nid=400&page={$page}&sort_by=draw_number&date_limit={$dateLimit}&winning_numbers=");
        $results = $drawScraper->getJsonStructure();

        if (!array_key_exists('rows', $results)) {
            throw new Exception('No data in API response');
        }

        return $results['rows'] ?? [];
    }

    public function getNumbersPrimary(): array
    {
        for ($page = 0; $page <= 3; $page++) {
            $data = $this->getPrimaryJsonResults($page);

            $drawIndex = -1;
            for ($index = 0; $index < count($data); $index++) {
                $areDatesEqual = $data[$index]['date'] === $this->providerNextDrawDate->format('Y-m-d');
                $areTimesEqual = $data[$index]['draw_time'] === $this->providerNextDrawDate->format('H:i:s');
                if ($areDatesEqual && $areTimesEqual) {
                    $drawIndex = $index;
                    break 2;
                }
            }
        }

        if ($drawIndex === -1) {
            $this->drawDateFix($this->providerNextDrawDate);
            throw new Exception($this->lottery_slug . ' - primary source unable to find draw');
        }

        return $data[$drawIndex]['winning_numbers'] ?? [];
    }
}
