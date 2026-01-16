<?php

namespace Helpers;

use Carbon\Carbon;
use Container;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Services\CacheService;
use Services\Logs\FileLoggerService;

final class ScraperHelper
{
    public static int $NUMBER_LOCALE_COMMA_DOT = 0;
    public static int $NUMBER_LOCALE_SPACE_COMMA = 2;
    public static int $NUMBER_LOCALE_DOT_COMMA = 1;

    /**
     * Returned format examples
     * 1,000,000/1 000 000 -> 1.000.000
     * 1,000,000.23/1.000.000.34/1 000 000.34 -> 1.000.000.23
     */
    public static function parseNumberToSystemLocale(string $number, int $numberLocaleFlag): string
    {
        //  todo: most likely it is possible to centralize regex parsing of numbers here. the problem is trailing phrase and other possibilities in prizes
        $wholeNumber = trim($number, ' ,.'); // we assume that there may be some leftover characters from regex
        /**
         * Sometimes lotteries add decimal value in jackpot/prizes.
         * What caused bad value.
         * For example:
         * 1. External jackpot is 1.000.000,00
         * 2. For us this is 100.000.000 and it`s not correct.
         * For this you need to parse the value separately before/after dot.
         */
        $numberAfterDot = self::getDecimalPart($wholeNumber);
        $numberBeforeDot = self::removeDecimalPart($wholeNumber, $numberAfterDot);
        switch ($numberLocaleFlag) {
            case self::$NUMBER_LOCALE_COMMA_DOT:
                $numberBeforeDot = str_replace(',', '', $numberBeforeDot);
                break;
            case self::$NUMBER_LOCALE_DOT_COMMA:
                $numberBeforeDot = str_replace('.', '', $numberBeforeDot);
                break;
            case self::$NUMBER_LOCALE_SPACE_COMMA:
                $numberBeforeDot = str_replace(' ', '', $numberBeforeDot);
                break;
        }
        return self::getNumberFormatWithDot($numberBeforeDot . $numberAfterDot);
    }

    private static function getNumberFormatWithDot(string $number): string
    {
        return str_replace(',', '.', $number);
    }

    private static function getDecimalPart(string $number): string
    {
        $tenthsPartNumber = self::getTenthsPart($number);
        if (self::isTenthsPart($tenthsPartNumber)) {
            return $tenthsPartNumber;
        }

        $hundredthPartNumber = self::getHundredthPart($number);
        if (self::isHundredthPart($hundredthPartNumber)) {
            return $hundredthPartNumber;
        }

        return '';
    }

    private static function getTenthsPart(string $number): string
    {
        return mb_substr($number, -2);
    }

    private static function isTenthsPart(string $tenthsPart): bool
    {
        return str_contains(mb_substr($tenthsPart, 0), ',') ||
            str_contains(mb_substr($tenthsPart, 0), '.');
    }

    private static function removeDecimalPart(string $number, string $decimalPartNumber): string
    {
        return preg_replace("/($decimalPartNumber(?!.*$decimalPartNumber))/", '', $number);
    }

    private static function getHundredthPart(string $number): string
    {
        return mb_substr($number, -3);
    }

    private static function isHundredthPart(string $hundredthPart): bool
    {
        return str_contains(mb_substr($hundredthPart, 0), ',') ||
            str_contains(mb_substr($hundredthPart, 0), '.');
    }

    /** This method returns the number of million */
    public static function getNumberOfMillions(string $jackpot, int $numberLocaleFlag = 3): float // NOTE: jackpot retrieval should have its own process - it may not be found in results page
    {
        $jackpotHtml = strip_tags($jackpot);
        $matches = [];
        preg_match('/\d[\d,\. ]{0,}/', $jackpotHtml, $matches); // todo: extract into variable/const
        $firstMatchedJackpot = $matches[0] ?? '';

        /** Matches will be empty when the jackpot on the lottery page is not up-to-date */
        $jackpot = (float)ScraperHelper::parseNumberToSystemLocale($firstMatchedJackpot, $numberLocaleFlag);
        /**
         * Jackpot in many websites contains different format.
         * For example:
         * 1 Million
         * 1 M
         * 1,000,00
         * https://gginternational.slite.com/app/docs/HwZ_1kh9owG7D6#65c89251
         */
        if (self::jackpotIsMillion($jackpotHtml, $firstMatchedJackpot)) {
            return $jackpot;
        }

        if (self::jackpotIsThousand($jackpotHtml, $firstMatchedJackpot)) {
            return $jackpot / 1000 / 1000000;
        }

        if (self::jackpotIsBillion($jackpotHtml, $firstMatchedJackpot)) {
            return $jackpot * 1000;
        }

        return $jackpot / 1000000;
    }

    private static function jackpotIsMillion(string $jackpotHtml, string $jackpot): bool
    {
        $jackpotHtml = strtolower($jackpotHtml);
        $worldAfterJackpotToCheck = self::getWordAfterJackpot($jackpotHtml, $jackpot);
        return str_contains($worldAfterJackpotToCheck, 'million') ||
            $worldAfterJackpotToCheck === 'mio' || // Spanish
            $worldAfterJackpotToCheck === 'm'; // abbreviation of the word million
    }

    private static function jackpotIsThousand(string $jackpotHtml, string $jackpot): bool
    {
        $jackpotHtml = strtolower($jackpotHtml);
        $worldAfterJackpotToCheck = self::getWordAfterJackpot($jackpotHtml, $jackpot);
        return str_contains($worldAfterJackpotToCheck, 'thousand') ||
            $worldAfterJackpotToCheck === 'mil' || // Spanish
            $worldAfterJackpotToCheck === 'k'; // abbreviation of the word thousand
    }

    private static function jackpotIsBillion(string $jackpotHtml, string $jackpot): bool
    {
        $jackpotHtml = strtolower($jackpotHtml);
        $worldAfterJackpotToCheck = self::getWordAfterJackpot($jackpotHtml, $jackpot);
        return str_contains($worldAfterJackpotToCheck, 'billion') ||
            $worldAfterJackpotToCheck === 'mil millones' || // Spanish
            $worldAfterJackpotToCheck === 'b'; // abbreviation of the word billion
    }

    private static function getWordAfterJackpot(string $jackpotHtml, string $jackpot): string
    {
        $matches = [];
        preg_match('/(?<=' . $jackpot . ')[,. \n]*(\w+)/', $jackpotHtml, $matches);
        /**
         * If jackpot look like -> 1 Million.
         * Returned value is million.
         */
        return trim($matches[0] ?? '');
    }

    /**
     * Some lotteries don't set the jackpot immediately after the draw.
     * If the jackpot isn't set within 6 hours, it is done manually.
     */
    public static function sendEmptyJackpotErrorAfterSixHours(float $jackpot, string $lotterySlug): void
    {
        $jackpotNotExist = empty($jackpot);
        if ($jackpotNotExist) {
            $cacheKey = 'empty' . $lotterySlug . 'JackpotDate';
            $cacheService = Container::get(CacheService::class);
            try {
                $dateTimeWithoutJackpot = $cacheService->getGlobalCache($cacheKey);
                $jackpotNotExistAfterSixHoursPassed = Carbon::parse($dateTimeWithoutJackpot)->diffInHours() >= 6;
                if ($jackpotNotExistAfterSixHoursPassed) {
                    $fileLoggerService = Container::get(FileLoggerService::class);
                    $fileLoggerService->error("
                        Scraped jackpot for lottery with slug $lotterySlug not exists.
                         Lottery has not been updated. If the problem occur update lottery manually.
                         Add jackpot before validator in lottery results code and run `php oil r lottery:update_draw_data $lotterySlug`
                         Or update lottery in database.
                     ");
                    $cacheService->deleteGlobalCache($cacheKey);
                }
            } catch (CacheNotFoundException) {
                $dateTimeWithoutJackpot = Carbon::now()->format(Helpers_Time::DATETIME_FORMAT);
                $cacheService->setGlobalCache($cacheKey, $dateTimeWithoutJackpot, Helpers_Time::HALF_DAY_IN_SECONDS);
            }
        }
    }
}
