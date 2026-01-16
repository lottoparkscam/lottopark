<?php

namespace Helpers;

use Helpers_Math;

final class NumberHelper
{
    /**
     * Prefix number with zeros.
     * E.g. ('10', '0000') = '0010'
     *
     * @param string $number
     * @param string $format
     * @return string
     */
    public static function prefix_with_zeros(string $number, string $format = '00'): string
    {
        $number_length = strlen($number);
        if ($number_length > strlen($format)) {
            return $number; // exit if number is outside of format range
        }

        return substr($format, 0, 0 - $number_length) . $number;
    }

    public static function round_currency(float $value): string
    {
        return self::round_string($value);
    }

    public static function round_string(float $value, int $precision = 2): string
    {
        return sprintf("%.{$precision}f", $value);
    }

    /**
     * E.g round_currencies(1, 15.3) => $val1 = '1.00', $val2 = '15.30'
     * @param float ...$values
     */
    public static function round_currencies(float &...$values): void
    {
        foreach ($values as &$value) {
            $value = self::round_currency($value);
        }
    }

    /**
     * @param int|float|string $number
     * @return boolean
     */
    public static function is_decimal($number): bool
    {
        $number_int = (int) $number;
        return ($number - $number_int) != 0;
    }

    /**
     * @param int|float|string $number
     * @return boolean
     */
    public static function is_not_decimal($number): bool
    {
        return !self::is_decimal($number);
    }

    public static function addZeroBeforeIfLowerThanTen(int $number): string
    {
        return $number < 10 ? '0' . $number : $number;
    }

    public static function isFloatNumberNegative(float $number): bool
    {
        return $number < 0;
    }

    /**
     * examples:
     * 5.421 -> 5.43 (when precision is 2)
     * -5.2412 -> -5.24 (when precision is 2)
     * 5.42131 -> 5.4214 (when precision is 4)
     * 5.42130 -> 5.4213 (when precision is 4)
     */
    public static function roundUpWhenNumberAfterPrecisionIsBiggerThenZero(float $number, int $precision = 2): float
    {
        /** We multiply by $valueToMultiplyToGetNumbersToRound to get the fractions we want to round. */
        $valueToMultiplyToGetNumbersToRound = 10 ** $precision;
        $numberToCeil = $valueToMultiplyToGetNumbersToRound * $number;
        /** Ceil round fractions up. */
        $numberAfterCeil = ceil($numberToCeil);
        /** We divide by $valueToMultiplyToGetNumbersToRound to change value to current fractions. */
        return $numberAfterCeil / $valueToMultiplyToGetNumbersToRound;
    }
}
