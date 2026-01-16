<?php

declare(strict_types=1);

namespace Helpers;

/**
 * Full token is whitelabel prefix like LP +
 * type transaction(T)/deposit(D)/purchase(P)/user(U)/withdrawal(W) +
 * whitelabel_user.token or other
 * For example -> LPP12334567
 */
final class FullTokenHelper
{
    public const TYPES = ['T', 'D', 'P', 'U', 'W',];
    public static function getWhitelabelPrefix(string $fullToken): string
    {
        if (self::isNotValid($fullToken)) {
            return '';
        }

        return mb_substr($fullToken, 0, 2);
    }

    public static function getToken(string $fullToken): string
    {
        if (self::isNotValid($fullToken)) {
            return '';
        }

        return mb_substr($fullToken, 3);
    }

    public static function isNotValid(string $fullToken): bool
    {
        $tokenType = mb_substr($fullToken, 0, 3);
        $isIncorrectType = in_array($tokenType, self::TYPES);
        $firstThreeCharAreNotLetters = !ctype_alpha(mb_substr($fullToken, 0, 3));
        $afterThreeCharNumberNotExists = !is_numeric(mb_substr($fullToken, 3, 4));
        return empty($fullToken) ||
            $firstThreeCharAreNotLetters ||
            $isIncorrectType ||
            $afterThreeCharNumberNotExists;
    }
}
