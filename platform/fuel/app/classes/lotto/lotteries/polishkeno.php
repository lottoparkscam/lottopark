<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_PolishKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 0.5; // 50 000 * 10 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Bucharest';

    protected string $lottery_slug = Lottery::POLISH_KENO_SLUG;
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
            $useSecondarySource = true;
            echo $e->__toString();
        }

        try {
            if ($useSecondarySource) {
                $numbers = $this->get_numbers_secondary();
                $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
                return;
            }
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

        return;

        // OLD SOURCE - html changed and it doesn't work anymore
        try {
            $scraper = Lotto_Scraperhtml::buildWithParametersAndHeaders('https://www.wynikilotto.net.pl/keno/wyniki/', [
                'wybor' => 'order',
                'ile' => 500,
            ], [
                'Content-Type: application/x-www-form-urlencoded',
            ]);
            $numbers = $this->process_numbers_html($scraper);
            $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
            return;
        } catch (\Throwable $e) {
            echo $e->__toString();
        }
    }

    public function process_numbers_html(Lotto_Scraperhtml $scraper): array
    {
        $nextDrawDay = $this->providerNextDrawDate->format('d.m.Y');
        $nextDrawTime = $this->providerNextDrawDate->format('H:i');

        $scraper
            ->setInitialBoundaries("<td> {$nextDrawDay}<br>🕑 {$nextDrawTime}</td>", '</tr>')
            ->setNumbersBoundaries("</td><td>", '</tr>');

        return $scraper->extractNumbers(self::LOTTERY_NUMBERS_COUNT, 0)[0];
    }

    public function get_primary_json_results(): array
    {
        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://lottowizz.com/api-data/json-numbers-ro-12/polonia_keno_20_70');
        return $drawScraper->getJsonStructure();
    }

    public function get_secondary_json_results(): array
    {
        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://lottowizz.com/api-data/all-rez/polonia_keno_20_70?start=0&length=800');
        $results = $drawScraper->getJsonStructure();

        return $results['data'] ?? [];
    }

    public function get_numbers_primary(): array
    {
        $results = $this->get_primary_json_results();

        $drawIndex = -1;
        for ($index = 0; $index < count($results); $index++) {
            if ($results[$index]['data_extragere'] === $this->providerNextDrawDate->format('Y-m-d') && $results[$index]['ora_extragere'] === $this->providerNextDrawDate->format('H:i')) {
                $drawIndex = $index;
                break;
            }
        }

        if ($drawIndex === -1) {
            throw new Exception($this->lottery_slug . ' - primary source unable to find draw');
        }

        return explode(',', $results[$drawIndex]['numere']);
    }

    public function get_numbers_secondary(): array
    {
        $results = $this->get_secondary_json_results();

        $drawIndex = -1;
        for ($index = 0; $index < count($results); $index++) {
            if ($results[$index][0] === $this->providerNextDrawDate->format('Y-m-d') && $results[$index][1] === $this->providerNextDrawDate->format('H:i')) {
                $drawIndex = $index;
                break;
            }
        }

        if ($drawIndex === -1) {
            $this->drawDateFix($this->providerNextDrawDate);
            throw new Exception($this->lottery_slug . ' - secondary source unable to find draw');
        }

        return explode(', ', $results[$drawIndex][2]);
    }
}
