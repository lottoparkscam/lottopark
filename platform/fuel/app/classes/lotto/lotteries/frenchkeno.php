<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_FrenchKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 2; // 200 000 * 10 (multiplier) / 1000000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Paris';

    const DAYS = [
        Carbon::MONDAY => 'lundi',
        Carbon::TUESDAY => 'mardi',
        Carbon::WEDNESDAY => 'mercredi',
        Carbon::THURSDAY => 'jeudi',
        Carbon::FRIDAY => 'vendredi',
        Carbon::SATURDAY => 'samedi',
        Carbon::SUNDAY => 'dimanche',
    ];

    const MONTHS = [
        Carbon::JANUARY => 'janvier',
        Carbon::FEBRUARY => 'février',
        Carbon::MARCH => 'mars',
        Carbon::APRIL => 'avril',
        Carbon::MAY => 'mai',
        Carbon::JUNE => 'juin',
        Carbon::JULY => 'juillet',
        Carbon::AUGUST => 'août',
        Carbon::SEPTEMBER => 'septembre',
        Carbon::OCTOBER => 'octobre',
        Carbon::NOVEMBER => 'novembre',
        Carbon::DECEMBER => 'décembre',
    ];


    protected string $lottery_slug = Lottery::FRENCH_KENO_SLUG;
    protected Carbon $providerNextDrawDate;
    protected array $providerNextDrawDateFragments;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->clone()->addHour()->isFuture()) {
            return;
        }

        try {
            $this->setProviderNextDrawDateFragments();
            $datetime = $this->removeFrenchAccents("{$this->providerNextDrawDateFragments['dayName']}-{$this->providerNextDrawDateFragments['dayNumber']}-{$this->providerNextDrawDateFragments['monthName']}-{$this->providerNextDrawDateFragments['year']}"); // dimanche-24-septembre-2023
            $scraper = Lotto_Scraperhtml::build("https://www.fdj.fr/jeux-de-tirage/keno/resultats/{$datetime}");
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

    public function setProviderNextDrawDateFragments(): void
    {
        $this->providerNextDrawDateFragments = [
            'dayName' => self::DAYS[$this->providerNextDrawDate->dayOfWeek],
            'dayNumber' => $this->providerNextDrawDate->format('d'),
            'dayNumberWithoutZeros' => $this->providerNextDrawDate->format('j'),
            'monthName' => self::MONTHS[$this->providerNextDrawDate->month],
            'year' => $this->providerNextDrawDate->year,
        ];
    }

    public function removeFrenchAccents(string $input): string
    {
        $accents = [
            'é' => 'e',
            'û' => 'u',
        ];

        return strtr($input, $accents);
    }

    public function getNextDrawTime(string $nextDrawTime): string
    {
        switch($nextDrawTime) {
            case '13:00':
                return 'Tirage du midi';
                break;
            case '20:00':
                return 'Tirage du soir';
                break;
            default:
                throw new Exception($this->lottery_slug . ' - unable to get next draw time');
        }
    }

    public function get_numbers_primary(Lotto_Scraperhtml $scraper): array
    {
        $nextDrawTime = $this->getNextDrawTime($this->providerNextDrawDate->format('H:i'));

        $dateScraper = $scraper
            ->setInitialBoundaries('id="datepicker"', '</div>')
            ->setDrawDateBoundaries('</svg>', '</button>')
            ->extractDrawDate();

        if (empty($dateScraper) || $dateScraper !== $this->providerNextDrawDate->format('d/m/Y')) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        $numbers = $scraper
            ->setInitialBoundaries("aria-label=\"$nextDrawTime", '</ul>')
            ->setDrawDateBoundaries('aria-label=\"', '</ul>')
            ->setNumbersBoundaries('<ul', '</ul>')
            ->getNumbersHTML();

        $date = $scraper->extractDrawDate();
        if (!str_contains($date, $nextDrawTime)) {
            throw new Exception($this->lottery_slug . ' - incorrect draw during the day');
        }

        $numbers = preg_replace('/<h5\b[^>]*>(.*?)<\/h5>/', '', $numbers);
        $numbers = str_replace('</h4>', ',', $numbers);
        $numbers = strip_tags($numbers);
        $numbers = explode(',', rtrim($numbers, ','));
        $numbers = array_map('intval', $numbers);
        sort($numbers);
        return $numbers;
    }
}
