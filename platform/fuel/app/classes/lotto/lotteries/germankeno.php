<?php

use Carbon\Carbon;
use Models\Lottery;
use Carbon\Exceptions\InvalidFormatException;

class Lotto_Lotteries_GermanKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 1; // 100 000 * 10 (multiplier) / 1 000 000 (jackpot in millions)
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Berlin';

    protected string $lottery_slug = Lottery::GERMAN_KENO_SLUG;
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
                $scraper = Lotto_Scraperhtml::build("https://www.lotteryextreme.com/germany-keno/results");
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
        // Next Draw Date must be set to 0:00:00 UTC
        $timestamp = $this->providerNextDrawDate->clone()->setTimezone('UTC')->setHour(0)->setMinute(0)->setSecond(0)->timestamp * 1000;

        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://www.lotto.de/api/stats/entities.keno/draw/{$timestamp}");
        $results = $drawScraper->getJsonStructure();
        return $results ?? [];
    }

    public function getNumbersPrimary(): array
    {
        $data = $this->getPrimaryJsonResults();

        if (!array_key_exists('drawDate', $data)) {
            throw new Exception('[First source] Invalid data in API response');
        }

        // Time in API is set to 0:00:00 Europe/Berlin
        $areDatesEqual = $data['drawDate'] === $this->providerNextDrawDate->clone()->setHour(0)->setMinute(0)->setSecond(0)->timestamp * 1000;
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return explode(', ', $data['drawNumbers']) ?? [];
    }

    public function getNumbersSecondary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries("Keno &nbsp; {$this->providerNextDrawDate->format('d.m.Y')}", 'Plus 5:')
            ->setNumbersBoundaries('<TD class', '>Plus 5:')
            ->setDrawDateBoundaries("Keno &nbsp;", '</tr>');

        // DATE
        $date = preg_replace('/[^0-9.]+/', '', $scraper->extractDrawDate());

        try {
            $date = Carbon::createFromFormat('d.m.Y', $date);
        } catch (InvalidFormatException $e) {
            throw new Exception($this->lottery_slug . ' - [Second source] Invalid date format from scraper');
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

        return $numbers ?? [];
    }
}
