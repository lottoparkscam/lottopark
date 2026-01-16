<?php

namespace Helpers;

use Exception;

class TypeHelper
{
    public const STRING = 'string';
    public const BOOLEAN = 'boolean';
    public const INTEGER = 'integer';
    public const FLOAT = 'float';

    private static $types = [
      self::STRING,
      self::BOOLEAN,
      self::INTEGER,
      self::FLOAT
    ];

    private static function isCorrectType(string $typeName): bool
    {
        return in_array($typeName, self::$types);
    }

    public static function cast(string $value, string $type)
    {
        $type = strtolower($type);
        $isNotCorrectType = !self::isCorrectType($type);

        if ($isNotCorrectType) {
            throw new Exception("Type $type is not correct.");
        }

        switch ($type) {
            case 'boolean':
                return self::castBoolean($value);
            case 'integer':
                return intval($value);
            case 'float':
                return floatval($value);
            default:
                return $value;
        }
    }

    private static function castBoolean(string $property): bool
    {
        return filter_var($property, FILTER_VALIDATE_BOOLEAN);
    }
}
