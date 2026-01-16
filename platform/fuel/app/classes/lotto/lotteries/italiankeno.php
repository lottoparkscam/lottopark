<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_ItalianKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 5; // 1 000 000 * 5 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 90];
    public const LOTTERY_INTERVAL_IN_MINUTES = 5;
    public const PROVIDER_TIMEZONE = 'Europe/Rome';

    protected string $lottery_slug = Lottery::ITALIAN_KENO_SLUG;
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

    public function calculateDrawNumber(): int
    {
        $minutesPastMidnight = $this->providerNextDrawDate->copy()->subSecond()->diffInMinutes($this->providerNextDrawDate->copy()->subSecond()->startOfDay());

        $drawNumber = ceil($minutesPastMidnight / self::LOTTERY_INTERVAL_IN_MINUTES);
        $drawNumber = intval($drawNumber);

        if ($drawNumber === 0) {
            $this->drawDateFix($this->providerNextDrawDate);
            throw new Exception($this->lottery_slug . ' - unable to find draw number');
        }

        return $drawNumber;
    }

    public function get_primary_json_results(): array
    {
        $drawNumber = $this->calculateDrawNumber();

        // Fix for draw at 00:00 (draw number = 288)
        $drawDate = $drawNumber === 288 ? $this->providerNextDrawDate->copy()->subDay()->format('Ymd') : $this->providerNextDrawDate->format('Ymd');

        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructureWithParameters('https://www.lotto-italia.it/del/estrazioni-e-vincite/10-e-lotto-estrazioni-ogni-5.json', [
            'data' => $drawDate,
            'progressivoGiornaliero' => $drawNumber,
        ]);
        $results = $drawScraper->getJsonStructure();
        return $results ?? [];
    }

    public function get_numbers_primary(): array
    {
        $data = $this->get_primary_json_results();

        $drawDateFromData = Carbon::createFromTimestamp($data['data'] / 1000, self::PROVIDER_TIMEZONE)->ceilMinute();
        $areDatesEqual = $drawDateFromData->format('YmdHi') === $this->providerNextDrawDate->format('YmdHi');

        if (!$areDatesEqual) {
            $this->drawDateFix($this->providerNextDrawDate);
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return $data['numeriEstratti'] ?? [];
    }
}
