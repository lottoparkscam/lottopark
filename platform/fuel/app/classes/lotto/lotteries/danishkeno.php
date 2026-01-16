<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_DanishKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 3.08; // 154 000 * 20 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Copenhagen';
    public const DAYS = [
        Carbon::MONDAY => 'mandag',
        Carbon::TUESDAY => 'tirsdag',
        Carbon::WEDNESDAY => 'onsdag',
        Carbon::THURSDAY => 'torsdag',
        Carbon::FRIDAY => 'fredag',
        Carbon::SATURDAY => 'loerdag',
        Carbon::SUNDAY => 'soendag',
    ];
    public const MONTHS = [
        Carbon::JANUARY => 'januar',
        Carbon::FEBRUARY => 'februar',
        Carbon::MARCH => 'marts',
        Carbon::APRIL => 'april',
        Carbon::MAY => 'maj',
        Carbon::JUNE => 'juni',
        Carbon::JULY => 'juli',
        Carbon::AUGUST => 'august',
        Carbon::SEPTEMBER => 'september',
        Carbon::OCTOBER => 'oktober',
        Carbon::NOVEMBER => 'november',
        Carbon::DECEMBER => 'december',
    ];

    protected string $lottery_slug = Lottery::DANISH_KENO_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->isFuture()) {
            return;
        }
        
        try {
            $url = $this->getPrimarySourceUrl($this->providerNextDrawDate);
            $scraper = Lotto_Scraperhtml::build($url);
            $numbers = $this->getNumbersPrimary($scraper);
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
            
            echo 'Error in all sources.';
        }
    }

    public function getPrimarySourceUrl(Carbon $nextDrawDate): string
    {
        $weekday = self::DAYS[$nextDrawDate->dayOfWeek];
        $day = $nextDrawDate->day;
        $month = self::MONTHS[$nextDrawDate->month];
        $year = $nextDrawDate->year;
        
        return "https://www.lottotal.dk/keno/keno-{$weekday}-{$day}-{$month}-{$year}";
    }

    public function getNumbersPrimary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries('datetime=', '<div class="gamedivider"></div>')
            ->setNumbersBoundaries('<figure class="ball">', '</section>')
            ->setDrawDateBoundaries('datetime="', 'T');

        // DATE
        $date = Carbon::createFromFormat('Y-m-d', $scraper->extractDrawDate());
        if (!$date) {
            throw new Exception($this->lottery_slug . ' - [Primary source] Invalid date format from scraper');
        }

        $areDatesEqual = $date->format('dmY') === $this->providerNextDrawDate->format('dmY');
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - [Primary source] unable to find draw');
        }

        // NUMBERS
        $numbers = $scraper->extractNumbers(20, 0);
        return $numbers[0];
    }

    /**
     * This source is blocked by Cloudflare.
     */
    public function getPrimaryJsonResults__OLD(): array
    {
        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://danskespil.dk/dlo/scapi/danskespil/numbergames/keno/winningNumbers?drawId=undefined");
        $results = $scraper->getJsonStructure();
        return $results ?? [];
    }

    public function getNumbersPrimary__OLD(): array
    {
        $data = $this->getPrimaryJsonResults__OLD();

        if (empty($data['winningNumbers'])) {
            throw new Exception($this->lottery_slug . ' - [Primary source] results not found in API response');
        }

        $areDatesEqual = $data['date'] === $this->providerNextDrawDate->format('Y-m-d');
        if (!$areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - [Primary source] unable to find draw');
        }
        
        sort($data['winningNumbers']);
        return $data['winningNumbers'] ?? [];
    }
}
