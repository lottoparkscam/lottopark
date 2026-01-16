<?php

use Carbon\Carbon;
use Models\Lottery;

class Lotto_Lotteries_BrazilianKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 0.5; // 500 000 * 10 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 80];
    public const PROVIDER_TIMEZONE = 'America/Sao_Paulo';

    protected string $lottery_slug = Lottery::BRAZILIAN_KENO_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone'])->setTimezone(self::PROVIDER_TIMEZONE);

        if ($this->providerNextDrawDate->isFuture()) {
            return;
        }

        try {
            // nonce
            $nonceScraper = Lotto_Scraperhtml::build('https://keno.com.br/resultados/');
            $nonce = $this->getNonce($nonceScraper);

            // numbers
            $numbers = $this->getNumbersPrimary($nonce);
            $this->insert_draw_numbers($numbers, $this->providerNextDrawDate);
            return;
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

    public function getNonce(Lotto_Scraperhtml $scraper): string
    {
        $scraper = $scraper->setCustomValueBoundaries('id="busca-nonce" value="', '">');
        return $scraper->getCustomValueHTML();
    }

    public function getPrimaryResultsRaw(string $nonce): string
    {
        $date = $this->providerNextDrawDate->format('d/m/Y');

        $drawScraper = Lotto_Scraperapi::build()->fetchJsonDataStructureAsHtml("https://keno.com.br/wp-admin/admin-ajax.php?action=search_sorteio&jogo=kenominas&data={$date}&sorteio=&nonce={$nonce}");
        $results = $drawScraper->getJsonStructure();

        if (!array_key_exists('data', $results) || empty($results['data'])) {
            throw new Exception('No data in API response');
        }

        return $results['data']['html'] ?? [];
    }

    public function getNumbersPrimary(string $nonce): array
    {
        $date = $this->providerNextDrawDate->format('d/m/Y');
        $time = $this->providerNextDrawDate->format('H:i:s');

        $result = $this->getPrimaryResultsRaw($nonce);

        $result = preg_replace('/\s+/', '', $result);
        $result = substr($result, strpos($result, "{$date}</strong></div><divclass=\"col-4\">Horário<strongclass=\"info\">{$time}"));
        $result = substr($result, 0, strpos($result, 'KenoMinas'));

        $areDatesCorrect = strlen($result) < 1000 && str_contains($result, $date) && str_contains($result, $time);
        if (!$areDatesCorrect) {
            $this->drawDateFix($this->providerNextDrawDate);
            throw new Exception($this->lottery_slug . ' - primary source unable to find draw');
        }

        $result = str_replace([$date, $time, 'Horário', '<span><strong>'], ['', '', '', ','], $result);
        $result = strip_tags($result);
        $result = array_filter(explode(',', $result));

        return $result;
    }
}
