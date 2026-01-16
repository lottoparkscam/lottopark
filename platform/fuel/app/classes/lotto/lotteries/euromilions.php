<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Models\Lottery;

class Lotto_Lotteries_Euromilions extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = Lottery::EUROMILLIONS_SLUG;
    protected string $ltech_slug = "euromillions-at";
    protected Carbon $nextDrawDate;

    public function get_results(): void
    {
        $this->nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        if ($this->nextDrawDate->isFuture()) {
            echo "{$this->lottery_slug} - next draw date is in the future" . PHP_EOL;
            return;
        }

        try {
            $data = $this->fetchLotteryData();
            $this->processResults($data);
        } catch (Throwable $e) {
            echo $e->__toString();
        }
    }

    protected function fetchLotteryData(): array
    {
        try {
            return $this->getDataSecondary();
        } catch (Exception $exception) {
            try {
                return $this->getDataPrimary();
            } catch (Exception $exception) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'All Sources',
                    exception: $exception,
                );
            }
        }
    }

    public function getDataPrimary(): array
    {
        $nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);
        $nextDrawDateFormatted = $nextDrawDate->format('Ymd');

        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructure("https://www.loteriasyapuestas.es/servicios/buscadorSorteos?game_id=EMIL&celebrados=true&fechaInicioInclusiva={$nextDrawDateFormatted}&fechaFinInclusiva={$nextDrawDateFormatted}");
        $data = $scraper->getJsonStructure()[0];

        $drawDateTime = Carbon::parse($data['fecha_sorteo'], $this->lottery['timezone']);
        $drawDateTimeFormatted = $drawDateTime->format('Ymd');

        if ($drawDateTimeFormatted !== $nextDrawDateFormatted) {
            throw new Exception($this->lottery_slug . ' unable to find draw');
        }

        $numbers = explode(' - ', trim($data['combinacion']));
        $numbers = array_map('intval', $numbers);

        $numbersArray[0] = array_slice($numbers, 0, 5);
        $numbersArray[1] = array_slice($numbers, -2);

        $prizes = $scraper->extractPrizes([0, 'escrutinio'], 'premio', 'ganadores_eu');

        $jackpotScrapper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.fdj.fr/api/service-draws/v1/games/euromillions/announces');
        $jackpotData = $jackpotScrapper->getJsonStructure();

        $jackpot = 0;
        foreach ($jackpotData as $draw) {
            if ($draw['type'] === 'normal') {
                if (Carbon::parse($draw['drawn_at'])->format('Ymd') !== $nextDrawDateFormatted) {
                    $jackpot = $draw['amount'] / 100000000;
                }
                break;
            }
        }

        if ($jackpot <= 1) {
            throw new Exception($this->lottery_slug . ' - primary source incorrect jackpot value');
        }

        return [
            $numbersArray,
            $prizes,
            $nextDrawDate,
            $jackpot,
        ];
    }

    public function getDataSecondary(): array
    {
        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.win2day.at/jam/drawgame/v1/public/drawResultInfo/euromillionen?payoutReleased=true&limit=1&sortBy=DATE_DESC');
        $data = $scraper->getJsonStructure();
        $data = $data['drawResults'];

        if (empty($data)) {
            throw new Exception($this->lottery_slug . ' - secondary source unable to fetch results');
        }

        $data = $data[0];

        // Draw date
        // API returns date as "2024-11-12" and time must be adjusted manually to 20:30:00 to match our database
        $drawDateTime = Carbon::parse($data['drawDate'], $this->lottery['timezone'])->setHour(20)->setMinute(30)->setSecond(0);

        $areDatesEqual = $this->nextDrawDate->format('Ymd') !== $drawDateTime->format('Ymd');
        if ($areDatesEqual) {
            throw new Exception($this->lottery_slug . ' - secondary source unable to find draw');
        }

        // Numbers
        $numbersArray = [
            $data['values'][0]['numbers'],
            $data['values'][1]['numbers'],
        ];

        // Prizes
        $prizes = $scraper->extractPrizes(['drawResults', 0, 'ranks'], 'amountPerWinner', 'numberOfWinners');
        $prizes = array_map(function ($innerArray) {
            return [$innerArray[0], $innerArray[1] / 100];
        }, $prizes);

        // Jackpot
        $jackpotScrapper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.fdj.fr/api/service-draws/v1/games/euromillions/announces');
        $jackpotData = $jackpotScrapper->getJsonStructure();

        $jackpot = 0;
        foreach ($jackpotData as $draw) {
            if ($draw['type'] === 'normal') {
                if (Carbon::parse($draw['drawn_at'])->format('Ymd') !== $this->nextDrawDate->format('Ymd')) {
                    $jackpot = $draw['amount'] / 100000000;
                }
                break;
            }
        }

        if ($jackpot <= 1) {
            throw new Exception($this->lottery_slug . ' - secondary source incorrect jackpot value');
        }

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    protected function processResults(array $data): bool
    {
        try {
            [$numbersArray, $prizes, $drawDateTime, $jackpot] = $data;
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 5, 2, [1, 50], [1, 12], 13);
        } catch (Throwable $exception) {
            if (empty($jackpot)) {
                ScraperHelper::sendEmptyJackpotErrorAfterSixHours($jackpot, $this->lottery_slug);
                return false;
            }

            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Validation',
                exception: $exception,
            );
            return false;
        }

        if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) { // TODO: {Vordis 2021-11-17 11:38:30} should be separate (jackpot update and draw insertion logic), but for that we need to rebuilt set_lottery_with_data
            $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbersArray[0], $numbersArray[1], $prizes, $this->overwrite_jackpot, false);
            return true;
        }
        echo "jackpot update or draw insertion trigger condition not met.";
        return false;
    }
}
