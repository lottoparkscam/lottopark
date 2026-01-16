<?php

namespace Helpers;

/**
 * Here are located some constants with maximum values for specific mysql data types.
 * These should be used instead of magic numbers in validators etc.
 *
 * Note: migrations can have different constraints and should be handled differently.
 */
final class DatabaseHelper
{
    public const TINYINT_UNSIGNED_MAX_VALUE = 255;
    public const SMALLINT_UNSIGNED_MAX_VALUE = 65535;
    public const MEDIUMINT_UNSIGNED_MAX_VALUE = 16777215;
    public const INT_UNSIGNED_MAX_VALUE = 4294967295;
    public const DECIMAL_MAX_VALUE = 9999999999999.99;
}