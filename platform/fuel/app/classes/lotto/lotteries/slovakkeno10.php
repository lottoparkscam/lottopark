<?php

use Carbon\Carbon;
use Models\Lottery;
use Carbon\Exceptions\InvalidFormatException;

class Lotto_Lotteries_SlovakKeno10 extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 2; // 100 000 * 20 (multiplier) / 1 000 000 (jackpot in millions)
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 80];
    public const PROVIDER_TIMEZONE = 'Europe/Bratislava';

    protected string $lottery_slug = Lottery::SLOVAK_KENO_10_SLUG;
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
                $scraper = Lotto_Scraperhtml::build("https://www.lotteryextreme.com/slovakia/keno10-results");
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
        $scraper = Services_Curl::postWithHeaders('https://www.tipos.sk/Millennium.CiselneLoterie/Keno10/GetForDate', [
            'datumZrebovania' => $this->providerNextDrawDate->format('Y-m-d')
        ], [
            'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
            'X-Requested-With: XMLHttpRequest'
        ]);

        $drawData = json_decode(json_decode($scraper, true), true);
        return $drawData ?? [];
    }

    public function getNumbersPrimary(): array
    {
        $data = $this->getPrimaryJsonResults();

        $areDatesEqual = $data['Date'] === $this->providerNextDrawDate->format('d. m. Y');
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return $data['Zrebovanie'] ?? [];
    }

    public function getNumbersSecondary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries("Keno 10 &nbsp; {$this->providerNextDrawDate->format('d.m.Y')}", 'Keno Plus:')
            ->setNumbersBoundaries('<ul class', 'Keno Plus:')
            ->setDrawDateBoundaries('Keno 10 &nbsp; ', ' (');

        // DATE
        $date = preg_replace('/\s{2}\d{1,2}$/', '', $scraper->extractDrawDate());

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
