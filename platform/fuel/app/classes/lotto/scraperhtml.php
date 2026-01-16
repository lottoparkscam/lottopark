<?php

use Helpers\ScraperHelper;
use Services\Logs\FileLoggerService;

/**
 *  USAGE NOTES:
 * 1 TRY TO USE THE LEAST LIKELY TO CHANGE BOUNDARIES EG do not use css classes, use words instead like winners
 * Date: 2023-07-05
 * Time: 13:46:24
 */
class Lotto_Scraperhtml // todo: should have parent and contracts with scraper api
{
    // tood: test for every lottery 
    // todo: self recovery and fallbacks when unable to get from set boundaries (there may be more abstract ones)
    protected string $rawHTML;
    protected string $areaOfWorkHTML;
    public string $jackpotHTML;
    private string $prizesHTML;
    private string $numbersHTML;
    private string $drawDateHTML; // todo: mandatory for those that don't filter dates - we need to check that we have new draw.
    private string $customValueHTML;
    public int $numberLocaleFlag;

    public function __construct(int $numberLocaleFlag = 0)
    {
        $this->numberLocaleFlag = $numberLocaleFlag;
    }

    protected function fetchRawWebsite(string $url)
    {
        $this->rawHTML = Services_Curl::getHTMLAsBrowser($url); // todo: 25s default timeout
        $this->areaOfWorkHTML = $this->rawHTML;
    }

    protected function fetchRawWebsiteWithHeaders(string $url, array $parameters, array $headers = [])
    {
        $this->rawHTML = Services_Curl::postWithHeaders($url, $parameters, $headers);
        $this->areaOfWorkHTML = $this->rawHTML;
    }

    /**
     * Build and prepare for scrapping.
     *
     * @throws Exception networking exception
     */
    public static function build(?string $url, int $numberLocaleFlag = 0, Lotto_Scraperhtml $scraperInstance = null, string $rawHTML = ''): Lotto_Scraperhtml
    {
        $scrapperHtml = ($scraperInstance === null) ? new Lotto_Scraperhtml($numberLocaleFlag) : $scraperInstance;
        if ($url === null) {
            $scrapperHtml->rawHTML = $rawHTML;
        } else {
            $scrapperHtml->fetchRawWebsite($url);
        }

        return $scrapperHtml;
    }

    /**
     * Build and prepare for scrapping. You can add custom curl headers.
     *
     * @throws Exception networking exception
     */
    public static function buildWithParametersAndHeaders(string $url, array $parameters, array $headers = [], int $numberLocaleFlag = 0, Lotto_Scraperhtml $scraperInstance = null): Lotto_Scraperhtml
    {
        $scrapperHtml = ($scraperInstance === null) ? new Lotto_Scraperhtml($numberLocaleFlag) : $scraperInstance;
        $scrapperHtml->fetchRawWebsiteWithHeaders($url, $parameters, $headers);

        return $scrapperHtml;
    }

    public function setInitialBoundaries(string $uniquePhraseOfStart, string $uniquePhraseOfEnd): static // todo: optional to optimize large pages
    {
        $this->areaOfWorkHTML = substr($this->rawHTML, strpos($this->rawHTML, $uniquePhraseOfStart) ?? 0);  // NOTE: boundaries are inclusive
        $endPosition = strpos($this->areaOfWorkHTML, $uniquePhraseOfEnd) ?? null;
        $this->areaOfWorkHTML = substr($this->areaOfWorkHTML, 0,  $endPosition ? $endPosition + strlen($uniquePhraseOfEnd) : null);  // NOTE: boundaries are inclusive 
        // todo: boundaries should be flexibly exclusive or inclusive?

        return $this;
    }

    public function setJackpotBoundaries(string $uniquePhraseOfStart, string $uniquePhraseOfEnd): static
    { // todo: all of these functions can be done as one automagic?
        $this->jackpotHTML = substr($this->areaOfWorkHTML, strpos($this->areaOfWorkHTML, $uniquePhraseOfStart));
        $this->jackpotHTML = substr($this->jackpotHTML, 0, strpos($this->jackpotHTML, $uniquePhraseOfEnd));

        return $this;
    }

    public function setPrizesBoundaries(string $uniquePhraseOfStart, string $uniquePhraseOfEnd): static
    {
        $this->prizesHTML = substr($this->areaOfWorkHTML, strpos($this->areaOfWorkHTML, $uniquePhraseOfStart));
        $this->prizesHTML = substr($this->prizesHTML, 0, strpos($this->prizesHTML, $uniquePhraseOfEnd));

        return $this;
    }

    public function setNumbersBoundaries(string $uniquePhraseOfStart, string $uniquePhraseOfEnd): static
    {
        $this->numbersHTML = substr($this->areaOfWorkHTML, strpos($this->areaOfWorkHTML, $uniquePhraseOfStart));
        $this->numbersHTML = substr($this->numbersHTML, 0, strpos($this->numbersHTML, $uniquePhraseOfEnd));

        return $this;
    }

    public function setCustomValueBoundaries(string $uniquePhraseOfStart, string $uniquePhraseOfEnd): static
    {
        $this->customValueHTML = substr($this->areaOfWorkHTML, strpos($this->areaOfWorkHTML, $uniquePhraseOfStart) + strlen($uniquePhraseOfStart));
        $this->customValueHTML = substr($this->customValueHTML, 0, strpos($this->customValueHTML, $uniquePhraseOfEnd));

        return $this;
    }

    /**
     * IMPORTANT: try to get boundaries clean and containing exclusively date of draw. if not possible try to provide clean html container.
     * NOTE: boundaries here are exclusive
     */
    public function setDrawDateBoundaries(string $uniquePhraseOfStart, string $uniquePhraseOfEnd): static
    {
        $this->drawDateHTML = substr($this->areaOfWorkHTML, strpos($this->areaOfWorkHTML, $uniquePhraseOfStart) + strlen($uniquePhraseOfStart));
        $this->drawDateHTML = substr($this->drawDateHTML, 0, strpos($this->drawDateHTML, $uniquePhraseOfEnd));

        return $this;
    }

    public function extractNumbers(int $numbersCount, int $bonusNumbersCount, bool $stripTags = true): array // todo: validation of numbers, jackpot etc format. it should double check that it at least look like value for our lottery. scrapper api already doing this so need to abstract and improve
    {
        $numbers = $this->numbersHTML;
        if ($stripTags) {
            $numbers = strip_tags($numbers);
        }

        $matches = [];
        preg_match_all('/[0-9]+/', $numbers, $matches); // NOTE: we assume that these numbers will never have locale formatting applied
        $numbers = array_slice($matches[0], 0, $numbersCount);
        $numbers = array_map('intval', $numbers);
        $bonusNumbers = array_slice($matches[0], $numbersCount, $bonusNumbersCount);
        $bonusNumbers = array_map('intval', $bonusNumbers);
        return [$numbers, $bonusNumbers];
    }

    /**
     * IMPORTANT: if phrase includes tag like </td> it must be escaped eg <\/td> since phrases are directly used in regex
     * NOTE: try to not include more than one html element in boundaries. this algorithm will try to enter first element, but on the other hand it should be able to strip such elements, but it is not guaranteed
     * NOTE: boundaries should be as solid as possible - try to pick the most consistent words.
     *
     * @return array prizes in form [[winnersCount, prizeValue]...]
     */
    public function extractPrizes(string $uniquePhraseOfStartWinners, string $uniquePhraseOfEndWinners, string $uniquePhraseOfStartPrize, string $uniquePhraseOfEndPrize): array
    { // todo: validate prizes; count format etc
        // todo: this function can be as simple as possible and additional procesing could be done by lottery class or extracted.
        $winnersRaw = [];
        preg_match_all("/$uniquePhraseOfStartWinners.+?$uniquePhraseOfEndWinners/s", $this->prizesHTML, $winnersRaw); // /s to match new lines
        $prizesValueRaw = [];
        preg_match_all("/$uniquePhraseOfStartPrize.+?$uniquePhraseOfEndPrize/s", $this->prizesHTML, $prizesValueRaw); // /s to match new lines

        $prizes = [];
        $winnersRawCount = count($winnersRaw[0]);
        for ($i = 0; $i < $winnersRawCount; $i++) {
            $winnersEntry = $this->trimToBeginningOfElement($winnersRaw[0][$i]);
            $prizeValueEntry = $this->trimToBeginningOfElement($prizesValueRaw[0][$i]);

            $matches = [];
            preg_match("/\b(\d*[\d,\. ]*\d)(?![\d.,])/", $winnersEntry, $matches);
            $numberOfWinUsers = $prizes[$i][0] = ScraperHelper::parseNumberToSystemLocale($matches[0] ?? 0, $this->numberLocaleFlag);// todo: warning about not finding value? its for jackpot
            preg_match("/[0-9][0-9,\. ]+/", $prizeValueEntry, $matches);
            $prizes[$i][1] = ScraperHelper::parseNumberToSystemLocale($matches[0] ?? 0, $this->numberLocaleFlag);
            /** Set zero payout for tier without win */
            $noOneWonForCurrentTier = $numberOfWinUsers === '0';
            if ($noOneWonForCurrentTier) {
                $prizes[$i][1] = 0;
            }
        }

        return $prizes;
    }

    public function extractPrizesUsingDOM(int $winnersColumn, int $prizesColumn, int $limit = 0): array
    {
        $dom = new DOMDocument();
        $dom->loadHTML($this->prizesHTML);
        $rows = $dom->getElementsByTagName('tr');
        $prizes = [];
        $matches = [];
        for ($i = 0; $i < $rows->length - $limit; $i++) {
            $cells = $rows->item($i)->getElementsByTagName('td');
            for ($j = 0; $j < $cells->length; $j++) {
                if ($j === $winnersColumn) {
                    $winners = $cells->item($j)->nodeValue;
                    preg_match("/[0-9]+(?:[0-9,\. ]+)?/", $winners, $matches);
                    $prizes[$i - 1][0] = ScraperHelper::parseNumberToSystemLocale($matches[0] ?? 0, $this->numberLocaleFlag); // We need to subtract because of the header
                }
                if ($j === $prizesColumn) {
                    $prize = $cells->item($j)->nodeValue;
                    preg_match("/[0-9]+(?:[0-9,\. ]+)?/", $prize, $matches);
                    $prizes[$i - 1][1] = ScraperHelper::parseNumberToSystemLocale($matches[0] ?? 0, $this->numberLocaleFlag); // todo: warning and validations if it is not found
                }
            }
        }

        return $prizes;
    }

    public function extractDrawDate(): string
    {
        return trim(strip_tags($this->drawDateHTML));
    }

    protected function trimToBeginningOfElement(string $html): string
    {
        $html = ltrim(strip_tags($html)); // this may not work if there are broken tags, opened by boundaries eg drawresult"> we want to navigate to inside of element
        return substr($html, strpos($html, '>') + 1);
    }

    /** This method prepareJackpot for second source */
    public function prepareJackpot(float &$jackpot, Lotto_Scraperhtml $jackpotScraper, array &$fetchedResultsArray, string $slug): void
    {
        try {
            // todo: we are deliberately overwriting the variable. To be improved in the future.
            $nextDrawJackpot = ScraperHelper::getNumberOfMillions($jackpotScraper->jackpotHTML, $jackpotScraper->numberLocaleFlag); // jackpot value must be in millions
        } catch (Throwable) {
            $nextDrawJackpot = $jackpot;
            $fileLoggerService = Container::get(FileLoggerService::class);
            $fileLoggerService->error(
                "[{$slug}] The previous jackpot value has been set because the current one could not be retrieved."
            );
        }

        $jackpot = $nextDrawJackpot;
        $fetchedResultsArray[0] = $nextDrawJackpot;
    }

    public function getPrizesHTML(): string
    {
        return $this->prizesHTML;
    }

    public function getNumbersHTML(): string
    {
        return $this->numbersHTML;
    }

    public function getJackpotHTML(): string
    {
        return $this->jackpotHTML;
    }

    public function getCustomValueHTML(): string
    {
        return $this->customValueHTML;
    }
}
