<?php

namespace Helpers;

final class AffMenuHelper
{
    private const MANAGER_URL_AFF_SLUG = 'affs';

    public static function isActiveTab(string $action, string $firstParamValue, string $firstParamExpectedValue): bool
    {
        return $action === self::MANAGER_URL_AFF_SLUG && $firstParamValue  === $firstParamExpectedValue;
    }
}
