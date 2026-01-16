<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Models\Lottery;

class Lotto_Lotteries_MegaMillions extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = Lottery::MEGA_MILLIONS_SLUG;
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
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'All Sources',
                exception: $exception,
            );
        }
    }

    protected function processResults($data): void
    {
        try {
            [$numbersArray, $prizes, $drawDateTime, $jackpot] = $data;
            $prizes = array_values($prizes);

            if (!$this->validateAndLogResults($jackpot, $drawDateTime, $numbersArray, $prizes)) {
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
                $this->lottery, 
                $jackpot,
                $drawDateTime,
                $drawDateTime->clone()->setTimezone('UTC'),
                $numbersArray[0],
                $numbersArray[1],
                $prizes,
                $this->overwrite_jackpot
            );
            return;
        }

        echo 'jackpot update or draw insertion trigger condition not met.';
    }

    private function validateAndLogResults($jackpot, $drawDateTime, $numbersArray, $prizes): bool
    {
        try {
            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 5, 1, [1, 70], [1, 25], 9);
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

    public function getDataPrimary(): array
    {
        $scraper = Lotto_Scraperapi::build()
            ->fetchXmlDataStructureWithParametersAsJson('https://router.gginternational.work/getResults.php?lottery=megamillions');

        $drawDate = $scraper->extractDrawDate(['Drawing', 'PlayDate']);
        $drawDateTime = $this->prepareDrawDatetime($drawDate);

        if ($drawDateTime->format('dmY') !== $this->nextDrawDate->format('dmY')) {
            throw new Exception($this->lottery_slug . ' - unable to find draw');
        }

        $primaryNumbersArray = $scraper->extractNumbersFromMultipleColumns(
            ['Drawing', ['N1', 'N2', 'N3', 'N4', 'N5']],
            5,
        );

        $bonusNumbersArray = $scraper->extractNumbersFromMultipleColumns(
            ['Drawing', ['MBall']],
            1,
        );

        $jackpot = $scraper->extractJackpot(['Jackpot', 'NextPrizePool']);
        $jackpot = ScraperHelper::getNumberOfMillions($jackpot);
        
        $prizes = $this->getPrizesFromJson($scraper->getJsonStructure());

        return [
            [$primaryNumbersArray, $bonusNumbersArray],
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    private function getPrizesFromJson(array $jsonStructure): array
    {
        $winnersArray = [];
        foreach ($jsonStructure['PrizeTiers'] as $tier) {
            $tierValue = $tier['Tier'];
            $winners = $tier['Winners'];
            $prizeAmount = $jsonStructure['PrizeMatrix']['PrizeTiers'][$tierValue]['PrizeAmount'];

            if (!isset($winnersArray[$tierValue])) {
                $winnersArray[$tierValue] = [
                    $winners,
                    $prizeAmount
                ];
            } else {
                $winnersArray[$tierValue][0] += $winners;
            }
        }

        return $winnersArray;
    }
}
