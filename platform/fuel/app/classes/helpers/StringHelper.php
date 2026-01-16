<?php

namespace Helpers;

final class StringHelper
{

    /**
     * Create string from array of strings.
     * NOTE: it will only add glue if item is not empty.
     *
     * @param string $glue glue with which string will be joined.
     * @param string[] $string
     * @return string
     */
    public static function implode(string $glue, array $strings): string
    {
        // get first item
        $first_item_index = 0;
        foreach ($strings as $string) {
            $first_item = $string;
            if (!empty($first_item)) {
                break;
            }
            $first_item_index++;
        }

        // check if array holds at least one non empty item.
        if (empty($first_item)) {
            return ''; // empty array - return empty string
        }

        // otherwise initialize result to first item and add next items prefixed by glue
        $result = $first_item;
        for ($i = $first_item_index + 1; $i < count($strings); $i++) {
            if (!empty($strings[$i])) {
                $result .= $glue . $strings[$i];
            }
        }
        return $result;
    }

    /**
     * Generate a more truly "random" alpha-numeric string.
     * 'Borrowed' from Laravel.
     *
     * @param  int  $length
     * @return string
     */
    public static function random(int $length = 16): string
    {
        $string = '';

        while (($len = strlen($string)) < $length) {
            $size = $length - $len;

            $bytes = random_bytes($size);

            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }

        return $string;
    }

    public static function dot_notate(string ...$parts): string
    {
        return implode('.', $parts);
    }

    public static function slugify($title, $separator = '-', $language = 'en'): string
    {
        $title = $language ? static::ascii($title, $language) : $title;

        // Convert all dashes/underscores into separator
        $flip = $separator === '-' ? '_' : '-';

        $title = preg_replace('![' . preg_quote($flip) . ']+!u', $separator, $title);

        // Replace @ with the word 'at'
        $title = str_replace('@', $separator . 'at' . $separator, $title);

        // Remove all characters that are not the separator, letters, numbers, or whitespace.
        $title = preg_replace('![^' . preg_quote($separator) . '\pL\pN\s]+!u', '', mb_strtolower($title, 'UTF-8'));

        // Replace all separator characters and whitespace by a single separator
        $title = preg_replace('![' . preg_quote($separator) . '\s]+!u', $separator, $title);

        return trim($title, $separator);
    }

    /**
     * Transliterate a UTF-8 value to ASCII.
     *
     * @param  string  $value
     * @param  string  $language
     * @return string
     */
    public static function ascii($value, $language = 'en')
    {
        return preg_replace('/[^\x20-\x7E]/u', '', $value);
    }

    public static function removeLastChunkBySeparator(string $value, string $separator): string
    {
        return substr($value, 0, strrpos($value, $separator));
    }

    /**
     * This function checks if $mainString contains $subString
     * ##### NOTE: This function is case insensitive. To compare this function changes strings to lowerscase.
     */
    public static function strContainsLower(string $mainString, string $subString): bool
    {
        return str_contains(strtolower($mainString), strtolower($subString));
    }

    /**
     * Converts classname with namespace to classname only (aware if namespace exists or not)
     * Example:
     * Validators\Rules\StringHelper -> StringHelper
     * StringHelper -> StringHelper
     */
    public static function classnameMinusNamespace(string $input): string
    {
        return ltrim(substr($input, strrpos($input, '\\')), '\\');
    }

    /** @return string empty if doesn't have start or end string */
    public static function getStringBetween(string $string, string $start, string  $end): string
    {
        $stringHasNotStartOrEndWord = !str_contains($string, $start) || !str_contains($string, $end);
        if ($stringHasNotStartOrEndWord) {
            return '';
        }

        $results = preg_match('/' . preg_quote($start) . '(.*?)' . preg_quote($end) . '/', $string, $match);
        if ($results === 0) {
            return '';
        }

        return $match[1];
    }

    /** @return string empty string if $baseString doesn't have $subString */
    public static function getStringAfterSubString(string $baseString, string $subString, bool $withSubstring = true): string
    {
        if (!str_contains($baseString, $subString)) {
            return '';
        }

        $contentAfterIndex = strrpos($baseString, $subString);

        if (!$withSubstring) {
            $contentAfterIndex += strlen($subString);
        }

        return substr($baseString, $contentAfterIndex);
    }
    
}
