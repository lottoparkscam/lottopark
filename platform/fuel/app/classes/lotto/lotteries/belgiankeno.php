<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_BelgianKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 2.5; // 250 000 * 10 (multiplier) / 1 000 000 (jackpot in millions)
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Brussels';

    protected string $lottery_slug = Lottery::BELGIAN_KENO_SLUG;
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
                $scraper = Lotto_Scraperhtml::build('https://www.lotteryextreme.com/belgium/keno-results');
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
        // Date must be adjusted to 00:00 UTC. DateTo is +1 day.
        $midnightUTC = $this->providerNextDrawDate->clone()->setTimezone('UTC')->setHour(0)->setMinute(0)->setSecond(0);
        $dateFrom = $midnightUTC->timestamp * 1000;
        $dateTo = $midnightUTC->clone()->addDay()->timestamp * 1000;

        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://apim.prd.natlot.be/api/v4/draw-games/draws?status=PAYABLE&date-from={$dateFrom}&date-to={$dateTo}&size=1&game-names=Keno");
        $results = $drawScraper->getJsonStructure();
        return $results ?? [];
    }

    public function getNumbersPrimary(): array
    {
        $data = $this->getPrimaryJsonResults();
        if (!isset($data['draws'][0]['results'][0]['primary'])) {
            throw new Exception($this->lottery_slug . ' - results not found in API response');
        }
        $data = $data['draws'][0];

        $areDatesEqual = $data['drawTime'] === $this->providerNextDrawDate->timestamp * 1000;
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return $data['results'][0]['primary'] ?? [];
    }

    public function getNumbersSecondary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries("Keno  &nbsp; {$this->providerNextDrawDate->format('d/m/Y')}", '</ul></tr>')
            ->setNumbersBoundaries('<TD class', '</ul></tr>')
            ->setDrawDateBoundaries('Keno &nbsp; ', '</tr><TR>');

        // DATE
        $date = preg_replace('/[^0-9\/]+/', '', $scraper->extractDrawDate());
        $date = Carbon::createFromFormat('d/m/Y', $date);
        if (!$date) {
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
