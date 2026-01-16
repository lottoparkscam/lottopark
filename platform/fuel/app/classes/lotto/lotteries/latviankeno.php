<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_LatvianKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 0.8; // 80 000 * 10 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 62];
    public const PROVIDER_TIMEZONE = 'Europe/Riga';

    protected string $lottery_slug = Lottery::LATVIAN_KENO_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->isFuture()) {
            return;
        }

        try {
            $scraper = Lotto_Scraperhtml::build("https://www.latloto.lv/lv/arhivs/keno");
            $numbers = $this->get_numbers_primary($scraper);
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
            echo $e->__toString();
        }
    }

    public function getNextDrawTime(string $nextDrawTime): string
    {
        switch($nextDrawTime) {
            case '11:30':
                return 'Rīta';
                break;
            case '15:30':
                return 'Dienas';
                break;
            case '19:30':
                return 'Vakara';
                break;
            default:
                throw new Exception($this->lottery_slug . ' - unable to get next draw time');
        }
    }

    public function get_numbers_primary(Lotto_Scraperhtml $scraper): array
    {
        $nextDrawDay = $this->providerNextDrawDate->format('Y.m.d');
        $nextDrawTime = $this->getNextDrawTime($this->providerNextDrawDate->format('H:i'));

        $scraper
            ->setInitialBoundaries("<td>{$nextDrawDay}</td>", '</tr>')
            ->setNumbersBoundaries("<span class=\"kenoTime lv\">{$nextDrawTime}:</span>", '</div>');

        $isDateCorrect = str_contains($scraper->getNumbersHTML(), $this->getNextDrawTime($this->providerNextDrawDate->format('H:i')));
        if (!$isDateCorrect) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        return $scraper->extractNumbers(20, 0)[0];
    }
}
