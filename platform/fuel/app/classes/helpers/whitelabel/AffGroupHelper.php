<?php

namespace Helpers;

use Generator;

class AffGroupHelper
{
    public static function prepareCasinoGroups(array $casinoGroups): Generator
    {
        foreach ($casinoGroups as $casinoGroup) {
            yield $casinoGroup['id'] => $casinoGroup;
        }
    }
}
