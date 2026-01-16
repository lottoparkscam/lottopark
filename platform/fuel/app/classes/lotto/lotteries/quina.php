<?php

use Carbon\Carbon;
use Helpers\ScraperHelper;
use Carbon\Exceptions\InvalidFormatException;

class Lotto_Lotteries_Quina extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    protected string $lottery_slug = 'quina';
    protected Carbon $nextDrawDate;

    public function get_results(): void
    {
        $this->nextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        if ($this->nextDrawDate->isFuture()) {
            echo "[{$this->lottery_slug}] Next draw date is in the future." . PHP_EOL;
            return;
        }

        try {
            $data = $this->fetchLotteryData();
            $this->processResults($data);
        } catch (Throwable $e) {
            echo $e->__toString();
        }
    }

    private function fetchLotteryData(): array
    {
        try {
            return $this->getDataPrimary();
        } catch (Exception $exception) {
            try {
                return $this->getDataSecondary();
            } catch (Exception $exception) {
                $this->sendDelayedErrorLog(
                    slug: $this->lottery_slug,
                    errorMessage: 'All Sources',
                    exception: $exception,
                );
            }
        }
        return [];
    }

    private function processResults(array $data): void
    {
        try {
            [$numbersArray, $prizes, $drawDateTime, $jackpot] = $data;

            if (!is_array($numbersArray) || !array_key_exists(0, $numbersArray) || !array_key_exists(1, $numbersArray)) {
                throw new Exception('Value Error: $numbersArray may not contain numbers and bonus numbers.');
            }

            $this->validateResults([$jackpot, $drawDateTime, $numbersArray[0], $numbersArray[1], $prizes], 5, 0, [1, 80], [], 4);
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
            $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbersArray[0], $numbersArray[1], $prizes, $this->overwrite_jackpot, false);
            return;
        }
        echo 'Jackpot update or draw insertion trigger condition not met.';
    }

    public function getDrawNumber(): int
    {
        $nextDrawDateFormatted = $this->nextDrawDate->format('F j, Y');

        $drawNumberScraper = Lotto_Scraperhtml::build("https://www.lotoloto.com.br/en/results/quina")
            ->setInitialBoundaries("<div class=\"sorteios multiplo\">", 'Access the Ticket Checker for Quina')
            ->setNumbersBoundaries($nextDrawDateFormatted, '</h3>');

        $drawNumber = $drawNumberScraper->getNumbersHTML();
        $drawNumber = strip_tags($drawNumber);
        $drawNumber = str_replace([$nextDrawDateFormatted, 'Draw '], '', $drawNumber);
        $drawNumber = trim($drawNumber);

        return (int)$drawNumber;
    }

    public function getDataPrimary(): array
    {
        $drawNumber = $this->getDrawNumber();

        $scraper = Lotto_Scraperhtml::build("https://www.lotoloto.com.br/en/results/quina/$drawNumber")
            ->setInitialBoundaries('<div class="item ordem1">', '<div class="barraIndividual')
            ->setDrawDateBoundaries('<div class="titulo fcCor">', '</div>')
            ->setNumbersBoundaries('<div class="numeros">', '<div class="resultado fcCor">')
            ->setPrizesBoundaries('Prizes</div>', '<div class="barraIndividual')
            ->setJackpotBoundaries('<span class="fcCor estimativa">', '</span>');

        $drawDate = $scraper->extractDrawDate();
        if (!strtotime($drawDate)) {
            throw new Exception("[{$this->lottery_slug}] [First source] Invalid date format from scraper");
        }
    
        $drawDateTime = $this->prepareDrawDatetime($drawDate);

        if ($drawDateTime->format('dmY') !== $this->nextDrawDate->format('dmY')) {
            throw new Exception("[{$this->lottery_slug}] [First source] Incorrect draw number or date");
        }

        $jackpot = ScraperHelper::getNumberOfMillions($scraper->jackpotHTML, $scraper->numberLocaleFlag);
        $numbersArray = $scraper->extractNumbers(5, 0);

        // Prizes
        $prizesData = $scraper->getPrizesHTML();
        $prizesData = str_replace(['winners <br> <span class="valor">(R$ ', '<div class="numero fcCor">'], '%%%', $prizesData);
        $prizesData = str_replace(['winner <br> <span class="valor">(R$ ', '<div class="numero fcCor">'], '%%%', $prizesData);
        $prizesData = str_replace('No winners', '0%%%0', $prizesData);
        $prizesData = str_replace('No winner', '0%%%0', $prizesData);
        $prizesData = str_replace(['Prizes', '5 matches', '4 matches', '3 matches', '2 matches', ' each)', ')', '(', ' ', "\n", "\r", "\t"], '', $prizesData);
        $prizesData = strip_tags($prizesData);
        $prizesData = str_replace(',', '', $prizesData);
        $prizesData = trim($prizesData, '%');
        $prizesData = explode('%%%', $prizesData);

        if (count($prizesData) !== 8) {
            throw new Exception("[{$this->lottery_slug}] [First source] Prizes parse error.");
        }

        /** If someone win, result page should add winner location at the end of prizes */
        $prizesData[7] = preg_replace('/[^0-9.]+/', '', $prizesData[7]);
        $prizes = [
            [$prizesData[0], $prizesData[1]],
            [$prizesData[2], $prizesData[3]],
            [$prizesData[4], $prizesData[5]],
            [$prizesData[6], $prizesData[7]],
        ];

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    public function getDataSecondary(): array
    {
        $scraper = Lotto_Scraperhtml::build('https://noticias.uol.com.br/service/?loadComponent=lottery-wrapper&data={"mode":"completo","selected":"quina"}', ScraperHelper::$NUMBER_LOCALE_DOT_COMMA)
            ->setInitialBoundaries('<div class="lottery-info">', '</table>')
            ->setDrawDateBoundaries(' | ', '</span>')
            ->setNumbersBoundaries('<div class="lt-result">', '<div class="winners">')
            ->setPrizesBoundaries('<tbody>', '</tbody>');

        // DATE
        $drawDate = $scraper->extractDrawDate();
        $drawDate = preg_replace('/[^0-9.]+/', '', $drawDate);
        $drawDate = str_replace('.', '/', $drawDate);

        try {
            $drawDateCarbon = Carbon::createFromFormat('d/m/y', $drawDate);
        } catch (InvalidFormatException $e) {
            throw new Exception("[{$this->lottery_slug}] [Second source] Invalid date format from scraper");
        }

        $drawDateFormatted = $drawDateCarbon->format('Y-m-d');

        if ($drawDateFormatted !== $this->nextDrawDate->format('Y-m-d')) {
            throw new Exception("[{$this->lottery_slug}] [Second source] Incorrect draw number or date");
        }

        $drawDateTime = $this->prepareDrawDatetime($drawDateFormatted);

        // NUMBERS
        $numbersArray = $scraper->extractNumbers(5, 0);

        // PRIZES
        $prizes = $scraper->extractPrizesUsingDOM(1, 2);
        $prizes = array_values($prizes);

        // JACKPOT
        $jackpotScraper = Lotto_Scraperhtml::build('https://www.estadao.com.br/loterias/quina/', ScraperHelper::$NUMBER_LOCALE_DOT_COMMA)
            ->setInitialBoundaries('<div class="estimativa">', '</div>')
            ->setDrawDateBoundaries('Estimativa de prêmio do próximo concurso: <!-- -->', '</span>')
            ->setJackpotBoundaries('</span><span>R$', '</span></div>');

        $jackpotDate = $jackpotScraper->extractDrawDate();
        try {
            $jackpotDateCarbon = Carbon::createFromFormat('d/m/Y', $jackpotDate);
        } catch (InvalidFormatException $e) {
            throw new Exception("[{$this->lottery_slug}] [Second source] Invalid date format from jackpot scraper");
        }

        $areDatesEqual = $jackpotDateCarbon->format('dmY') !== $this->nextDrawDate->addDay()->format('dmY');
        if ($areDatesEqual) {
            throw new Exception("[{$this->lottery_slug}] [Second source] Incorrect date in jackpot scraper");
        }

        $jackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag);

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }

    public function getDataTertiary(): array
    {
        $drawDate = $this->nextDrawDate->format('Y-m-d');

        $scraper = Lotto_Scraperhtml::build("https://www.lottoster.com/br/quina/results/$drawDate/", ScraperHelper::$NUMBER_LOCALE_DOT_COMMA)
            ->setInitialBoundaries('<div id="sorteo" data-gametype="quina">', '<footer')
            ->setDrawDateBoundaries('<h2>Prizes Quina ', '  </h2>')
            ->setJackpotBoundaries('<p class="jackpot sk_fontSize34 text-center"><strong class="sk_strongest">', '</p>')
            ->setNumbersBoundaries('<ul class="numbers sexy" data-gametype="quina" aria-label="Numbers">', '</ul>')
            ->setPrizesBoundaries('<table class="escrutinioTable">', '</table>');

        
           
        try {
            $extractedDrawDate = Carbon::createFromFormat('d F Y', $scraper->extractDrawDate(), $this->lottery['timezone']);
        } catch (InvalidFormatException $e) {
            throw new Exception("[{$this->lottery_slug}] [Third source] Failed to parse draw date.");
        }

        $extractedDrawDateFormatted = $extractedDrawDate->format('Y-m-d');

        if ($drawDate !== $extractedDrawDateFormatted) {
            throw new Exception("[{$this->lottery_slug}] [Third source] Incorrect draw date in scraper.");
        }

        $jackpotScraper = Lotto_Scraperhtml::build("https://www.lotoloto.com.br/en/quina/", ScraperHelper::$NUMBER_LOCALE_COMMA_DOT)
            ->setInitialBoundaries('<div class="item recente">', '<div class="estatisticas')
            ->setDrawDateBoundaries('<div class="titulo fcCor">', '</div>')
            ->setJackpotBoundaries('Next prize', '</span>');

        $jackpotDrawDateFormatted = Carbon::parse($jackpotScraper->extractDrawDate(), $this->lottery['timezone'])->format('Y-m-d');
        if ($jackpotDrawDateFormatted !== $drawDate) {
            throw new Exception($this->lottery_slug . ' - jackpot date is not correct');
        }

        $jackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag);

        $numbersArray = $scraper->extractNumbers(5, 0);
        $prizes = $scraper->extractPrizesUsingDOM(1, 2);
        $drawDateTime = $this->prepareDrawDatetime($extractedDrawDateFormatted);

        return [
            $numbersArray,
            $prizes,
            $drawDateTime,
            $jackpot,
        ];
    }
}
