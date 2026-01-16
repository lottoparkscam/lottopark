<?php

namespace Helpers;

class SanitizerHelper
{
    public static function sanitizeString(string $value): string
    {
        return htmlentities($value);
    }

    public static function sanitizeSlug(string $value): string
    {
        return preg_replace("/[^a-zA-Z0-9\-]+/", '', $value);
    }
}