<?php

/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2020-05-07
 * Time: 13:19:57
 */
final class Helper_Raffle
{
    public static function tier_matches_to_winners(array $matches): int
    {
        if (is_array($matches[0])) { // e.g. [[2,25]] translates into 24 winners
            return $matches[0][1] - $matches[0][0] + 1; // NOTE: +1 due to inclusive range (from 2 inclusive to 25 inclusive gives 24)
        }

        return $matches[0]; // e.g. [1] translates into 1 winner
    }
}
