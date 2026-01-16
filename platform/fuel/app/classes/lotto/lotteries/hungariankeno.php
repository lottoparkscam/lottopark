<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_HungarianKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 7.5; // 1 500 000 * 5 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 80];
    public const PROVIDER_TIMEZONE = 'Europe/Budapest';

    protected string $lottery_slug = Lottery::HUNGARIAN_KENO_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->isFuture()) {
            return;
        }
        
        try {
            $scraper = Lotto_Scraperhtml::build("https://bet.szerencsejatek.hu/cmsfiles/keno.html");
            $numbers = $this->getNumbersPrimary($scraper);
            $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
            return;
        } catch (\Throwable $e) {
            try {
                $scraperSecondary = Lotto_Scraperhtml::build('https://www.lotteryextreme.com/HungarianLottery/Keno-Results_History');
                $numbers = $this->getNumbersSecondary($scraperSecondary);
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

    public function getNumbersPrimary(Lotto_Scraperhtml $scraper): array
    {
        $year = $this->providerNextDrawDate->format('Y');
        $weekOfYear = $this->providerNextDrawDate->weekOfYear;
        $dayOfWeekIso = $this->providerNextDrawDate->dayOfWeekIso;
        $drawDateFormatted = $this->providerNextDrawDate->format('Y.m.d.');
        $dateLimitFormatted = $this->providerNextDrawDate->clone()->subWeek()->format('Y.m.d.');

        $dateScraper = $scraper
            ->setInitialBoundaries("<table", $dateLimitFormatted)
            ->setDrawDateBoundaries("<td>{$year}</td><td>{$weekOfYear}</td><td>{$dayOfWeekIso}</td><td>", '</td>')
            ->extractDrawDate();

        if (empty($dateScraper) || $dateScraper !== $this->providerNextDrawDate->format('Y.m.d.')) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        $numbers = $scraper
            ->setInitialBoundaries('<table', $dateLimitFormatted)
            ->setNumbersBoundaries("<td>$drawDateFormatted</td>", '</td></tr>')
            ->getNumbersHTML();

        $numbers = preg_replace('/<td>[0-9]{4}\.[0-9]{2}\.[0-9]{2}\.<\/td>/', '', $numbers);
        $numbers = str_replace(['<td>', '</td>'], ['', ','], $numbers);
        $numbers = explode(',', $numbers);
        $numbers = array_map('intval', $numbers);

        return $numbers;
    }

    public function getNumbersSecondary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries("Keno &nbsp; {$this->providerNextDrawDate->format('Y.m.d')}", '</ul></tr>')
            ->setNumbersBoundaries('<TD class', '</ul></tr>')
            ->setDrawDateBoundaries('Keno &nbsp; ', ' (');

        // DATE
        $date = preg_replace('/[^0-9.]+/', '', $scraper->extractDrawDate());
        $date = Carbon::createFromFormat('Y.m.d', $date);
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
