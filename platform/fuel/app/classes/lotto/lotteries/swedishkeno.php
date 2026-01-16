<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_SwedishKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 3.5; // 500 000 * 20 (multiplier) / 1 000 000, but limited to 3.5 million in specification
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Stockholm';

    protected string $lottery_slug = Lottery::SWEDISH_KENO_SLUG;
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
        } catch (Throwable $e) {
            try {
                $scraper = Lotto_Scraperhtml::build('https://www.lotteryextreme.com/sweden/keno-results');
                $numbers = $this->getNumbersSecondary($scraper);
                $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
                return;
            } catch (Throwable $e) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'All Sources',
                    exception: $e,
                    nextDrawDateFormatted: $this->providerNextDrawDate->format('YmdHi'),
                    delayInHours: 6,
                );
            }
            echo 'Error in both sources.';
        }
    }

    public function getDrawNumber(): int
    {
        /**
         * First draw in API
         * Number: 7351
         * Date: 2013-02-13T18:30:00+01:00
         * https://api.www.svenskaspel.se/draw/1/keno/draws/7351/result
         */
        $diffInDaysFromFirstDrawInApi = Carbon::parse('2013-02-12 12:00:00', self::PROVIDER_TIMEZONE)->diffInDays($this->providerNextDrawDate);
        return 7350 + $diffInDaysFromFirstDrawInApi;
    }

    public function getPrimaryJsonResults(): array
    {
        $drawNumber = $this->getDrawNumber();

        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://api.www.svenskaspel.se/draw/1/keno/draws/{$drawNumber}/result");
        $results = $drawScraper->getJsonStructure();
        return $results ?? [];
    }

    public function getNumbersPrimary(): array
    {
        $data = $this->getPrimaryJsonResults();

        if (empty($data['result']['numbers'])) {
            throw new Exception($this->lottery_slug . ' - [Primary source] results not found in API response');
        }
        $data = $data['result'];

        // $data['regCloseTime'] has a format 2024-11-18T18:25:00+01:00
        $areDatesEqual = str_contains($data['regCloseTime'], $this->providerNextDrawDate->format('Y-m-d\T'));
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - [Primary source] unable to find draw');
        }
        
        sort($data['numbers']);
        return $data['numbers'] ?? [];
    }

    public function getNumbersSecondary(Lotto_Scraperhtml $scraper): array
    {
        
        $scraper = $scraper
            ->setInitialBoundaries("Keno &nbsp; {$this->providerNextDrawDate->format('d.m.Y')}", 'Kung Keno:')
            ->setNumbersBoundaries('<ul class=', '<p style=')
            ->setDrawDateBoundaries('Keno &nbsp; ', ' (');

        // DATE
        $date = Carbon::createFromFormat('d.m.Y', $scraper->extractDrawDate());
        if (!$date) {
            throw new Exception($this->lottery_slug . ' - [Secondary source] Invalid date format from scraper');
        }

        $areDatesEqual = $date->format('dmY') === $this->providerNextDrawDate->format('dmY');
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - [Secondary source] unable to find draw');
        }
        
        // NUMBERS
        $numbers = $scraper->getNumbersHTML();
        $numbers = str_replace('<li>', '%%%', $numbers);
        $numbers = strip_tags($numbers);
        $numbers = trim($numbers, '%');
        $numbers = explode('%%%', $numbers);

        return $numbers;
    }
}
