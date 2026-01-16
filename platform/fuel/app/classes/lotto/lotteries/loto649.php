<?php
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Helpers\ScraperHelper;
use Models\Lottery;
use GuzzleHttp\Client;

class Lotto_Lotteries_LoteriaRomana extends Lotto_Lotteries_Lottery
{
    use Lotto_Hasscraping;

    const LOTTERY_CURRENCY_CODE = 'RON';
    protected string $lottery_slug = Lottery::LOTO_6_49_SLUG;
    protected Carbon $providerNextDrawDate;

    public function get_results(): void
    {
        $this->providerNextDrawDate = Carbon::parse($this->lottery['next_date_local'], $this->lottery['timezone']);

        // Results are delayed at the source
        if ($this->providerNextDrawDate->clone()->addHours(3)->isFuture()) {
            return;
        }

        try {
            $data = $this->fetchLotteryData();
            $this->processResults($data);
        } catch (Throwable $e) {
            echo $e->__toString();
        }
    }

    /**
     * @return array
     * @throws Exception
     */
    private function fetchLotteryData(): array
    {
        try {
            $client = new Client();
            $response = $client->get('https://www.loto.ro/loto-new/newLotoSiteNexioFinalVersion/web/app2.php/jocuri/649_si_noroc/rezultate_extragere.html');
            $html = $response->getBody();
            $resultTable = $this->getResultsContentForDate($html, $this->providerNextDrawDate->format('d.m.Y'));
            $scraper = Lotto_Scraperhtml::build(null, 0, null, $resultTable);

            return $this->getDataPrimary($scraper);
        } catch (Exception $e) {
            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'All Sources',
                exception: $e,
                nextDrawDateFormatted: $this->providerNextDrawDate->format('YmdHi'),
            );

            return [];
        }
    }

    private function processResults(array $data): void
    {
        if (empty($data)) {
            throw new Exception($this->lottery_slug . ' - no data to process');
        }

        try {
            [$numbers, $prizes, $drawDateTime, $jackpot] = $data;
            $this->validateResults([$jackpot, $drawDateTime, $numbers, [], $prizes], 6, 0, [1, 49], [], 4);
        } catch (Throwable $exception) {
            if (empty($jackpot)) {
                ScraperHelper::sendEmptyJackpotErrorAfterSixHours($jackpot, $this->lottery_slug);
                return;
            }

            $this->sendDelayedErrorLog(
                slug: $this->lottery_slug,
                errorMessage: 'Validation',
                exception: $exception,
            );
            return;
        }

        if ($this->shouldUpdateLottery($drawDateTime, $jackpot)) {
            $this->set_lottery_with_data($this->lottery, $jackpot, $drawDateTime, $drawDateTime->clone()->setTimezone('UTC'), $numbers, [], $prizes, $this->overwrite_jackpot, false);
            return;
        }
        echo 'Jackpot update or draw insertion trigger condition not met.';
    }

    public function getDataPrimary(Lotto_Scraperhtml $scraper): array
    {
        $scraper = $scraper
            ->setInitialBoundaries('<div class="rezultate-extrageri-content resultDiv ">', '</tbody></table>')
            ->setNumbersBoundaries('numere-extrase">', '</div>')
            ->setDrawDateBoundaries('<p>Detalii castiguri  la 6/49 din <span>', '</span></p>')
            ->setPrizesBoundaries('<tbody>', '<tr class="total">')
            ->setJackpotBoundaries('Fond total de castiguri: <strong>', '</strong></td>');

        $numbers = $scraper->getNumbersHTML();

        $numbers = str_replace(
            [
                'numere-extrase">',
                '<img src="/loto-new/newLotoSiteNexioFinalVersion/web/bundles/lotofrontend/images/bile/',
                ' ',
                "\n",
            ],
            [
                '',
            ],
            $numbers
        );

        $numbers = explode('.png"onerror="this.style.display=\'none\'"/>', $numbers, -1);
        sort($numbers);

        $drawDate = $scraper->extractDrawDate();
        if (empty($drawDate)) {
            throw new Exception("[{$this->lottery_slug}] [First source] Empty drawDate");
        }

        if (!preg_match('/^\d{1,2}\.\d{1,2}\.\d{4}$/', $drawDate)) {
            throw new Exception("[{$this->lottery_slug}] [First source] Date has incorrect format");
        }

        try {
            $drawDateCarbon =  Carbon::createFromFormat('d.m.Y', $drawDate);
        } catch (InvalidFormatException $e) {
            throw new Exception("[{$this->lottery_slug}] [First source] Invalid date format from scraper");
        }

        $drawDateTime = $this->prepareDrawDatetime($drawDateCarbon->format('Y-m-d'));

        if (!$drawDateTime) {
            throw new Exception("[{$this->lottery_slug}] [First source] Invalid date, can't format date ($drawDate) from scraper");
        }

        if ($drawDateCarbon->format('dmY') !== $this->providerNextDrawDate->format('dmY')) {
            throw new Exception("[{$this->lottery_slug}] [First source] Incorrect draw date");
        }

        $prizes = $this->getPrizesFromHTMLTable($scraper->getPrizesHTML());

        $jackpotHTML = $scraper->getJackpotHTML();
        $jackpot = str_replace(["Fond total de castiguri: <strong>", '.'], [''], $jackpotHTML);
        $jackpot = ScraperHelper::parseNumberToSystemLocale($jackpot, $scraper->numberLocaleFlag);
        $jackpot = Helpers_Currency::convert_to_EUR($jackpot, static::LOTTERY_CURRENCY_CODE);
        $jackpot = ScraperHelper::getNumberOfMillions($jackpot, $scraper->numberLocaleFlag);

        return [
            $numbers,
            $prizes,
            $drawDateTime,
            $jackpot
        ];
    }

    function getResultsContentForDate(string $html, string $date): string 
    {
        // Simply match what's between <div class="rezultate-extrageri-content resultDiv ">
        // and <div class="rezultate-extrageri-content resultDiv floatright">
        // and then loop matches to find a string with the correct date and data.
        $pattern = '/<div\s+class=["\']rezultate-extrageri-content\s+resultDiv\s*["\'][^>]*>([\s\S]*?)<div\s+class=["\']rezultate-extrageri-content\s+resultDiv\s+floatright["\'][^>]*>/i';
        preg_match_all($pattern, $html, $matches);
        $finalMatch = '';

        foreach ($matches[0] as $match) {
            if (str_contains($match, "<p>Detalii castiguri  la 6/49 din <span>$date</span></p>")) {
                $finalMatch = $match;
            }
        }

        $finalMatch = str_replace('<div class="rezultate-extrageri-content resultDiv floatright">', '', $finalMatch);

        return $finalMatch;
    }

    function getPrizesFromHTMLTable(string $htmlString): array
    {
        $rows = explode('</tr>', $htmlString, 4);
        $result = [];
    
        foreach ($rows as $row) {
            $cells = explode('</td>', $row);
            $count = trim(strip_tags($cells[1]));
            $count = str_replace(['REPORT', '.'], ['0', ''], $count);
            $prize = trim(strip_tags($cells[2]));
            $prize = str_replace(['.', ','], ['', '.'], $prize);
            $prize = floatval($prize);
            $prize = Helpers_Currency::convert_to_EUR($prize, static::LOTTERY_CURRENCY_CODE);
        
            $result[] = [$count, $prize];
        }

        return $result;
    }
}
