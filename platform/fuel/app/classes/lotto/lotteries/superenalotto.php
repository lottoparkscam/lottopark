<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Models\Lottery;

class Lotto_Lotteries_SuperEnalotto extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = Lottery::SUPERENA_SLUG;
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
            return $this->getDataPrimary();
        } catch (Exception $exception) {
            try {
                $scraper = Lotto_Scraperhtml::build('https://www.superenalotto.com/risultati/estrazione-' . $this->nextDrawDate->format('d-m-Y'));
                return $this->getDataSecondary($scraper);
            } catch (Exception $exception) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'All Sources',
                    exception: $exception,
                );
            }
        }
    }

    public function getJsonPrimary(): array
    {
        $scraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.gntn-pgd.it/gntn-info-web/rest/gioco/superenalotto/estrazioni/ultimoconcorso?idPartner=GIOCHINUMERICI_INFO');
        return $scraper->getJsonStructure() ?? [];
    }

    public function getJackpotJsonPrimary(): array
    {
        $jackpotScraper = Lotto_Scraperapi::build()->fetchJsonDataStructure('https://www.gntn-pgd.it/gntn-info-web/rest/gioco/superenalotto/concorsoufficiale?idPartner=GIOCHINUMERICI_INFO');
        return $jackpotScraper->getJsonStructure() ?? [];
    }

    public function getDataPrimary(): array
    {
        $data = $this->getJsonPrimary();

        // NUMBERS
        $numbers = [
            $data['combinazioneVincente']['estratti'],
            [$data['combinazioneVincente']['numeroJolly']],
        ];

        // DATE
        $date = Carbon::createFromTimestampMs($data['dataEstrazione'], $this->lottery['timezone']);
        if ($date->format('YmdHis') !== $this->nextDrawDate->format('YmdHis')) {
            throw new Exception($this->lottery_slug . ' - [Primary source] unable to find draw');
        }

        // JACKPOT
        $dataJackpot = $this->getJackpotJsonPrimary();
        $jackpot = $dataJackpot['dettaglioConcorso']['jackpot'] / 100000000;

        // PRIZES
        $prizes = [];
        foreach($data['dettaglioVincite']['vincite'] as $key=> $datum) {
            $prizes[] = [
                $datum['numero'],
                $datum['quota']['importo'] / 100,
            ];

            if ($key === 5) {
                break;
            }
        }

        return [
            $numbers,
            $prizes,
            $date,
            $jackpot,
        ];
    }

    public function getDataSecondary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries('<time itemprop="startDate"', '<aside id="sidebarRight">')
            ->setDrawDateBoundaries('datetime="', '">Estrazione')
            ->setNumbersBoundaries('<table id="t2">', '</tr>')
            ->setPrizesBoundaries('<h2>Quote SuperEnalotto</h2>', '</tbody>')
            ->setJackpotBoundaries('jackpot del SuperEnalotto &egrave; di <span>', ' &euro;</span></td>');

        // DATE
        if (!strtotime($scraper->extractDrawDate())) {
            throw new Exception('[First source] Invalid date format from scraper');
        }
        $date = $this->prepareDrawDatetime($scraper->extractDrawDate());

        // NUMBERS
        $numbers = $scraper->extractNumbers(6, 1);

        // PRIZES
        $prizes = $scraper->extractPrizesUsingDOM(2, 1);
        array_walk($prizes, function (&$item) {
            $item[1] = preg_replace('/(\d+)\.(\d{3})\.(\d{2})/', '$1$2.$3', $item[1]);
            $item[1] = (float) $item[1];
            $item[0] = preg_replace('/\.(\d{3})$/', '$1', $item[0]);
        });

        // JACKPOT
        $jackpot = $scraper->getJackpotHTML();
        $jackpot = preg_replace('/\D/', '', $jackpot);
        $jackpot /= 1000000;

        return [
            $numbers,
            $prizes,
            $date,
            $jackpot,
        ];
    }

    protected function processResults($data): void
    {
        try {
            [$numbers, $prizes, $drawDateTime, $jackpot] = $data;
            $prizes = array_values($prizes);

            if (!$this->validateAndLogResults($jackpot, $drawDateTime, $numbers, $prizes)) {
                return;
            }

            $nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);
            if ($drawDateTime->format('dmY') !== $nextDrawDate->format('dmY')) {
                return;
            }

        } catch (Throwable $exception) {
            if (empty($jackpot)) {
                ScraperHelper::sendEmptyJackpotErrorAfterSixHours($jackpot, $this->lottery_slug);
                return;
            }

            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Validation',
                exception: $exception,
                delayInHours: 2,
            );
            return;
        }

        if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) {
            $this->set_lottery_with_data(
                $this->lottery, $jackpot,
                $drawDateTime,
                $drawDateTime->clone()->setTimezone('UTC'),
                $numbers[0],
                $numbers[1],
                $prizes,
                $this->overwrite_jackpot
            );
            return;
        }

        echo 'jackpot update or draw insertion trigger condition not met.';
    }

    private function validateAndLogResults($jackpot, $drawDateTime, $numbers, $prizes): bool
    {
        try {
            $this->validateResults([$jackpot, $drawDateTime, $numbers[0], $numbers[1], $prizes], 6, 1, [1, 90], [1, 90], 6);
            return true;
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
    }
}
