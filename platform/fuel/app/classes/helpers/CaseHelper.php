<?php

namespace Helpers;

class CaseHelper
{
    public static function snakeToCamel(string $value): string
    {
        return lcfirst(self::snakeToPascal($value));
    }

    public static function camelToSnake(string $value): string
    {
        return mb_strtolower(preg_replace('/(?<!^|_)[A-Z0-9]/', '_$0', $value));
    }

    public static function pascalToSnake(string $value): string
    {
        return self::camelToSnake($value);
    }

    public static function snakeToPascal(string $value): string
    {
        return str_replace(' ', '', mb_convert_case(str_replace('_', ' ', $value), MB_CASE_TITLE));
    }

    public static function kebabToPascal(string $value): string
    {
        return str_replace(' ', '', mb_convert_case(str_replace('-', ' ', $value), MB_CASE_TITLE));
    }

    public static function kebabToSnake(string $value): string
    {
        return mb_strtolower(str_replace('-', '_', $value));
    }

    public static function snakeToTitle(string $value): string
    {
        return str_replace(' ', ' ', mb_convert_case(str_replace('_', ' ', $value), MB_CASE_TITLE));
    }
}
