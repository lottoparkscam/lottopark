<?php

namespace Helpers;

use Fuel\Core\Arr;
use Exception;
use stdClass;

final class ArrayHelper
{

    /**
     * Loose conversion from array to string - non arrays will be returned unprocessed.
     *
     * @param mixed  $value     should be array, other values will be returned untouched.
     * @param string $separator separator between array items.
     *
     * @return mixed
     */
    public static function implode_loosely($value, string $separator = ',')
    {
        if (is_array($value)) {
            return implode($separator, $value);
        }

        return $value;
    }

    public static function reverse_with_keys(array $array): array
    {
        return array_combine(array_reverse(array_keys($array)), array_reverse($array));
    }

    private static function implode_item(&$item, array &$array, string ...$glues): void
    {
        if (is_array($item)) {
            $item = self::implode_recursive($item, ...$glues);
        }
        $key = key($array);
        if (is_string($key)) {
            $item = $key . '-' . $item;
        } // TODO: {Vordis 2020-05-04 17:48:26} key separator could be extracted
    }

    public static function implode_recursive(array $array, string ...$glues): string
    {
        $glue = array_shift($glues);
        if (empty($glues)) {
            $glues = [$glue]; // reuse glue if not specified for every level
        }
        $string = current($array);
        self::implode_item($string, $array, ...$glues);
        while (($next = next($array)) !== false) {
            self::implode_item($next, $array, ...$glues);
            $string .= $glue . $next;
        }

        return $string;
    }

    /**
     * Next which retrieves current item and move pointer to next one.
     *
     * @param array $array
     *
     * @return mixed|null next item.
     */
    public static function next(array $array)
    {
        $item = current($array);
        next($array);

        return $item ?: null;
    }

    /**
     * Get last item of an array
     *
     * @param array $array
     *
     * @return mixed|null
     */
    public static function last(array $array)
    {
        $item = end($array);
        reset($array);

        return $item ?: null;
    }

    /**
     * Get first item of an array
     *
     * @param array $array
     *
     * @return mixed|null
     */
    public static function first(array $array)
    {
        return current($array);
    }

    /**
     * If we have collection of arrays, we can search it by value from item
     * e.g. if we have users array, we can find single array [user] by property name
     *
     * @param array $array
     * @param string $key
     * @param $value
     * @return int
     * @throws Exception
     */
    public static function getKeyOfItemFromArrayByItemValue(array $array, string $key, $value): int
    {
        $foundItemKey = -1;

        foreach ($array as $itemKey => $item) {
            if (!is_array($item)) {
                throw new Exception('Item is not an array');
            }

            if (isset($item[$key]) && $item[$key] === $value) {
                $foundItemKey = $itemKey;
            }
        }

        return $foundItemKey;
    }

    public static function createSingleArrayFromValue(array $array, string $value): array
    {
        $response = [];

        foreach ($array as $item) {
            $response[] = $item[$value];
        }

        return $response;
    }

    /**
     * Can be slower on bigger arrays.
     * Ensures that all keys from array and array of arrays are removed.
     * Example:
     * - Input: ['key1' => 'value1', 'key2' => 'value2'] AND key to remove "key1"
     * - Output: ['key2' => 'value2']
     * @see ArrayTest::removeArrayItemsByKeys_RemovesKeysAnywhereInArray_KeysAndSubKeysAreRemoved()
     * @param string[] $array
     * @param string[] $keysToRemove
     */
    public static function removeArrayAndArraySubItemsByKeys(array &$array, array $keysToRemove): void
    {
        foreach ($keysToRemove as $keyToRemove) {
            self::arrayRecursiveUnset($array, $keyToRemove);
        }
    }

    private static function arrayRecursiveUnset(array &$array, string $keyToRemove)
    {
        unset($array[$keyToRemove]);
        foreach ($array as &$value) {
            if (is_array($value)) {
                self::arrayRecursiveUnset($value, $keyToRemove);
            }
        }
    }

    /**
     * @param string[] $array
     * @return string[]
     */
    public static function arrayValuesToLowerCase(array $array): array
    {
        return array_map('strtolower', $array);
    }

    public static function countNumericValues(array $array): int
    {
        $i=0;
        foreach ($array as $number) {
            if (is_numeric($number)) {
                $i++;
            }
        }
        return $i;
    }

    public static function countUniqueNumericValues(array $array): int
    {
        return self::countNumericValues(array_unique($array));
    }

    /** @return ?array null when is not assoc array */
    public static function deleteValuesLikeForAssocArray(array $array, array $values, ?string $column = null): ?array
    {
        if (Arr::is_assoc($array)) {
            foreach ($array as $arrayKey => $arrayValue) {
                if ($column !== null) {
                    if ($arrayKey === $column && $arrayValue !== str_ireplace($values, 'wordFound', $arrayValue)) {
                        Arr::delete($array, $arrayKey);
                    }
                    continue;
                }

                if ($arrayValue !== str_ireplace($values, 'wordFound', $arrayValue)) {
                    Arr::delete($array, $arrayKey);
                }
            }

            return $array;
        }

        return null;
    }

    /** @return ?array null when is not multi dimensional array */
    public static function deleteValuesForMultiDimensionalArray(
        array $array,
        array $values,
        bool $resetKeys = true,
        bool $removeArrayFromMulti = false,
        ?string $column = null
    ): ?array {
        if (Arr::is_multi($array)) {
            foreach ($array as $arrayKey => $arrayValue) {
                if (is_array($arrayValue) && Arr::is_assoc($arrayValue)) {
                    $array[$arrayKey] = self::deleteValuesLikeForAssocArray($arrayValue, $values, $column);
                    if ($removeArrayFromMulti && !array_key_exists($column, $array[$arrayKey])) {
                        Arr::delete($array, $arrayKey);
                    }
                }
            }

            return $resetKeys ? array_values($array) : $array;
        }

        return null;
    }

    /** php array_splice resets keys. This function doesn't. */
    public static function arraySpliceWithoutKeys(array $array, int $index): array
    {
        $values = array_map(function (int $key, mixed $value) use ($index) {
            if ($key >= $index) {
                return $value;
            }
        }, array_keys($array), $array);

        return array_filter($values);
    }
}
