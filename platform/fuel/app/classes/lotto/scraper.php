<?php

use Carbon\Carbon;
use Helpers\ArrayHelper;

class Lotto_Scraper
{

    public Validation $validation;
    private array $lottery;
    private int $numbersCount;
    private int $bonusNumbersCount;
    private int $prizesCount;
    private string $winnersKey;
    private string $prizeKey;
    private array $drawDateKeys;
    private array $prizesKeys;
    private array $jackpotKeys;
    private array $numbersKeys;
    private array $bonusNumbersKeys;


    /**
     * counts are for validation purposes. should be provided from lottery rules.
     * Keys are for prizes validation and extraction.
     */
    public function __construct(array $lottery, int $numbersCount, int $bonusNumbersCount, int $prizesCount, string $winnersKey, string $prizeKey, array $drawDateKeys, array $prizesKeys, array $jackpotKeys, array $numbersKeys, array $bonusNumbersKeys)
    { // TODO: we should automatically fetch counts for validation.
        $this->lottery = $lottery;
        $this->numbersCount = $numbersCount;
        $this->bonusNumbersCount = $bonusNumbersCount;
        $this->prizesCount = $prizesCount;
        $this->winnersKey = $winnersKey;
        $this->prizeKey = $prizeKey;
        $this->drawDateKeys = $drawDateKeys;
        $this->prizesKeys = $prizesKeys;
        $this->jackpotKeys = $jackpotKeys;
        $this->numbersKeys = $numbersKeys;
        $this->bonusNumbersKeys = $bonusNumbersKeys;
    }

    /**
     * @return float[]|array[]|string[] variables: $jackpot, $drawDatetime, $dateUTC, $numbers, $bonusNumbers, $prizes
     *
     * @throws Exception if unable to fetch for any reason. Mostly I/O error or invalid keys.
     */
    protected function fetchResultsFromJSON(string $resultsRaw): array
    {
        $resultsArray = json_decode($resultsRaw, true);
        if (empty($resultsArray) || !is_array($resultsArray)) {
            throw new Exception("Invalid json results data: " . var_export(['raw' => $resultsRaw], true));
        }

        $drawDate = $this->extractValue($resultsArray, $this->drawDateKeys);
        $prizes = $this->extractValue($resultsArray, $this->prizesKeys);
        $jackpot = $this->extractValue($resultsArray, $this->jackpotKeys);
        $numbers = $this->extractValue($resultsArray, $this->numbersKeys);
        $bonusNumbers = $this->extractValue($resultsArray, $this->bonusNumbersKeys);

        // extraction of numbers if they are not in array form; for more advanced extraction extend this function
        if (is_string($numbers)) {
            $matches = [];
            preg_match_all('/[0-9]+/', $numbers, $matches);
            $numbers = array_slice($matches[0], 0, $this->numbersCount);
            $bonusNumbers = array_slice($matches[0], $this->numbersCount, $this->bonusNumbersCount);
        }

        return [$jackpot, $drawDate, $numbers, $bonusNumbers, $prizes];
    }


    private function extractValue(array $array, array $keys): mixed
    {
        foreach ($keys as $key) {
            $array = $array[$key];
        }

        return $array;
    }

    public function parseResults(array $fetchedResultsArray): array
    {
        [$jackpot, $drawDate, $numbers, $bonusNumbers, $prizes] = $fetchedResultsArray;
        $jackpot /= 1000000;

        foreach ($prizes as &$prize) {
            $prize[$this->prizeKey] = str_replace(',', '.', $prize[$this->prizeKey]); // parse locale
            $prize = [(int)$prize[$this->winnersKey], (float)$prize[$this->prizeKey]];
        }

        $drawDatetime = Carbon::parse($drawDate, $this->lottery['timezone']);
        foreach (json_decode($this->lottery['draw_dates'], true) as $draw_date) { // NOTE: I omit checks with premeditation let false and null explode
            $isTheSameAsDrawDay = strpos($draw_date, $drawDatetime->shortDayName) !== false;
            if ($isTheSameAsDrawDay) {
                $drawTime = explode(' ', $draw_date)[1];
                break;
            }
        }
        if (!isset($drawTime)) {
            throw new \InvalidArgumentException('unable to find draw time');
        }
        $drawDatetime->setTimeFromTimeString($drawTime);
        $drawDatetimeUTC = $drawDatetime->clone()->setTimezone('UTC');

        return [$jackpot, $drawDatetime, $drawDatetimeUTC, $numbers, $bonusNumbers, $prizes];
    }

    /**
     * Returns validated and parsed results - ready to use.
     * @return float|Carbon|int[]|array[] variables: $jackpot, $drawDatetime, $datetimeUTC, $numbers, $bonusNumbers, $prizes
     *
     * @throws Exception if unable to fetch for any reason. From I/O to missing fields and parsing errors.
     */
    public function serveResults(string $url): array
    {
        $resultsRaw = Services_Curl::getJsonAsBrowser($url); // note: 25s default timeout
        return $this->fetchResultsFromJSON($resultsRaw);
    }
}
