<?php

use Carbon\Carbon;
use Models\Lottery;
use Carbon\Exceptions\InvalidFormatException;

class Lotto_Lotteries_UkrainianKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 0.375; // 37 500 * 10 (multiplier) / 1 000 000 (jackpot in millions)
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 80];
    public const PROVIDER_TIMEZONE = 'Europe/Kyiv';

    protected string $lottery_slug = Lottery::UKRAINIAN_KENO_SLUG;
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
            try {
                $scraper = Lotto_Scraperhtml::build('https://www.lotteryextreme.com/ukraine/keno-results');
                $numbers = $this->getNumbersSecondary($scraper);
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
            }

            echo 'Error in both sources.';
        }
    }

    public function getPrimaryJsonResults(): array
    {
        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://unl.ua/api/dynamicdata');
        $results = $drawScraper->getJsonStructure();
        return $results ?? [];
    }

    public function getNumbersPrimary(): array
    {
        $data = $this->getPrimaryJsonResults();
        if (!isset($data['keno']['results'][0])) {
            throw new Exception($this->lottery_slug . ' - results not found in API response');
        }
        $data = $data['keno']['results'][0];

        $areDatesEqual = $data['date'] === $this->providerNextDrawDate->format('Y-m-d');
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return $data['balls'] ?? [];
    }

    public function getNumbersSecondary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries("Keno &nbsp; {$this->providerNextDrawDate->format('d.m.Y')}", '</ul></tr>')
            ->setNumbersBoundaries('<TD class', '</ul></tr>')
            ->setDrawDateBoundaries('Keno &nbsp; ', ' (');

        // DATE
        $date = preg_replace('/[^0-9.]+/', '', $scraper->extractDrawDate());

        try {
            $date = Carbon::createFromFormat('d.m.Y', $date);
        } catch (InvalidFormatException $e) {
            throw new Exception('[Second source] Invalid date format from scraper');
        }

        $areDatesEqual = $date->format('dmY') === $this->providerNextDrawDate->format('dmY');
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
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
