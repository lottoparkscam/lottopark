<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_CzechKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 0.05; // 5 000 * 10 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 12;
    public const LOTTERY_NUMBERS_RANGE = [1, 60];
    public const PROVIDER_TIMEZONE = 'Europe/Prague';

    protected string $lottery_slug = Lottery::CZECH_KENO_SLUG;
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
    }

    public function get_primary_json_ids(): array
    {
        $drawIdsScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.sazka.cz/api/draw-info/past-draws/keno');
        return $drawIdsScraper->getJsonStructure();
    }

    public function get_primary_json_results(int $drawId): array
    {
        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://www.sazka.cz/api/draw-info/draws/universal/keno/{$drawId}");
        return $drawScraper->getJsonStructure();
    }

    public function get_secondary_json_results(int $page): array
    {
        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructureWithParameters('https://www.sazka.cz/api/draw-info/draws/keno/results', [
            'day' => $this->providerNextDrawDate->format('Y-m-d\TH:i:s'),
            'page' => $page,
        ]);
        $drawJson = $drawScraper->getJsonStructure();

        if (!isset($drawJson['results'])) {
            throw new Exception($this->lottery_slug . ' - secondary source unable to fetch results');
        }

        return $drawJson['results'];
    }

    public function get_numbers_primary(): array
    {
        // ID Scraper
        $drawIdsJson = $this->get_primary_json_ids();

        $drawId = -1;
        for ($index = 0; $index < count($drawIdsJson); $index++) {
            $areDatesEqual = preg_replace('/[^0-9]/', '', $drawIdsJson[$index]['name']) === $this->providerNextDrawDate->format('jnYGi');
            if ($areDatesEqual) {
                $drawId = $drawIdsJson[$index]['id'];
                break;
            }
        }

        if ($drawId === -1) {
            throw new Exception($this->lottery_slug . ' - primary source unable to find draw');
        }

        // Draw Scraper
        $drawJson = $this->get_primary_json_results($drawId);
        
        return $drawJson['draws'][0] ?? [];
    }

    public function get_numbers_secondary(): array
    {
        for ($i = 1; $i <= 29; $i++) {
            $drawJson = $this->get_secondary_json_results($i);

            foreach ($drawJson as $drawJsonDatum) {
                $areDatesEqual = $drawJsonDatum['drawDate'] === $this->providerNextDrawDate->format('j. n. Y') && $drawJsonDatum['drawTime'] === $this->providerNextDrawDate->format('G:i');
                if ($areDatesEqual) {
                    $numbers = $drawJsonDatum['results'];
                    break 2;
                }
            }
        }

        if (empty($numbers)) {
            $this->drawDateFix($this->providerNextDrawDate);
            throw new Exception($this->lottery_slug . ' - secondary source unable to find draw');
        }

        return $numbers ?? [];
    }
}
