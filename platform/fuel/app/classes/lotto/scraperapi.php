<?php

use Helpers\ScraperHelper;

/**
 * 
 * Date: 2023-07-04
 * Time: 11:15:15
 */
class Lotto_Scraperapi
{

    // tood: test for every lottery 
    public array $jsonStructure = []; // todo: other types of structure
    private int $numberLocaleFlag;

    public function __construct(int $numberLocaleFlag = 0)
    {
        $this->numberLocaleFlag = $numberLocaleFlag;
    }
    /**
     * Build and prepare for scrapping.
     * 
     * @throws Exception networking exception
     */
    public static function build(int $numberLocaleFlag = 0): static
    {
        $scrapperHtml = new Lotto_Scraperapi($numberLocaleFlag);

        return $scrapperHtml;
    }

    public function fetchJsonDataStructure(string $url): static
    {
        $this->jsonStructure = json_decode(Services_Curl::getJsonAsBrowser($url), true); // todo: 25s default timeout

        return $this;
    }

    public function fetchJsonDataStructureAsHtml(string $url): static
    {
        $this->jsonStructure = json_decode(Services_Curl::getHTMLAsBrowser($url), true); // todo: 25s default timeout

        return $this;
    }

    public function fetchJsonDataStructureWithParameters(string $url, array $parameters = []): static
    {
        $this->jsonStructure = json_decode(Services_Curl::postJsonAsBrowser($url, $parameters), true); // todo: 25s default timeout

        return $this;
    }

    public function fetchXmlDataStructureWithParametersAsJson(string $url, array $parameters = []): static
    {
        $response = Services_Curl::getXmlAsBrowser($url, $parameters);
        $xml = simplexml_load_string($response);
        $json = json_decode($xml, true);

        if ($json) {
            $this->jsonStructure = $json;
        }

        return $this;
    }

    private function extractValue(array $array, array $keys): mixed
    {
        foreach ($keys as $key) {
            if (!array_key_exists($key, $array)) {
                throw new InvalidArgumentException("Key '{$key}' does not exist in the array.");
            }
            $array = $array[$key];
        }

        return $array;
    }

    private function extractValues(array $array, array $indices): array
    {
        $values = [];
        $mainIndex = $indices[0];
        $keys = $indices[1];

        foreach ($keys as $key) {
            $values[] = $array[$mainIndex][$key];
        }

        return $values;
    }

    public function extractDrawDate(array $indices): string
    {
        return $this->extractValue($this->jsonStructure, $indices);
    }

    /**
     * Use this method only if you need take whole number.
     * Remember if you need to set jackpot in database use ScraperHelper::getNumberOfMillions($jackpot).
     */
    public function extractJackpot(array $indices): float
    {
        return $this->extractValue($this->jsonStructure, $indices); // NOTE: in most cases jackpot is displayed as full number - need to divide.
    }

    /**
     * Use this method only if you need take whole number.
     * Remember if you need to set jackpot in database use ScraperHelper::getNumberOfMillions($jackpot).
     */
    public function extractJackpotFromText(array $indices): float
    {
        $jackpotText = $this->extractValue($this->jsonStructure, $indices);

        preg_match('/\d[\d,\. ]{0,}/', $jackpotText, $matches);

        return (float)ScraperHelper::parseNumberToSystemLocale($matches[0] ?? '0', $this->numberLocaleFlag);
    }

    public function extractNumbers(array $numbersIndices, array $bonusNumbersIndices, int $numbersCount, int $bonusNumbersCount): array // todo: validation of numbers, jackpot etc format. it should double check that it at least look like value for our lottery. scrapper api already doing this so need to abstract and improve
    {
        $numbers = $this->extractValue($this->jsonStructure, $numbersIndices);
        $bonusNumbers = $this->extractValue($this->jsonStructure, $bonusNumbersIndices);

        // extraction of numbers if they are not in array form; for more advanced extraction extend this function
        if (is_string($numbers)) {  // todo: this is incomplete and not elastic enough; genesis in la primitiva api
            $matches = [];
            preg_match_all('/[0-9]+/', $numbers, $matches);
            $numbers = array_slice($matches[0], 0, $numbersCount);
            $bonusNumbers = array_slice($matches[0], $numbersCount, $bonusNumbersCount);
        }

        return [$numbers, $bonusNumbers];
    }

    public function extractNumbersFromMultipleColumns(array $numbersIndices, int $numbersCount): array
    {
        $primaryNumbers = $this->extractValues($this->jsonStructure, $numbersIndices);

        if (is_string($primaryNumbers)) {
            $matches = [];
            preg_match_all('/[0-9]+/', $primaryNumbers, $matches);
            return array_slice($matches[0], 0, $numbersCount);
        }

        return $primaryNumbers;
    }

    public function extractPrizes(array $indices, string $prizeKey, string $winnersKey): array
    {
        $prizes = $this->extractValue($this->jsonStructure, $indices);

        foreach ($prizes as &$prizeEntry) { // todo: locale handling for apis that have display values instead of numeric
            $prizeEntry = [(int)$prizeEntry[$winnersKey], (float)$prizeEntry[$prizeKey]];
        }

        return $prizes;
    }

    public function getJsonStructure(): array
    {
        return $this->jsonStructure;
    }
}
