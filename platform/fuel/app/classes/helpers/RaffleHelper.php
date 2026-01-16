<?php

namespace Helpers;

final class RaffleHelper
{

    /**
     * Convert prize in kind slug to corresponding lottery slug
     *
     * @param string $slug Prize in kind slug
     *
     * @return string Lottery slug
     * 
     * !!! PROVISIONAL CODE - This switch should not be considered during "The Merge" !!!
     * Prize raffle was not scalable because of faireum raffle slugs and its conflict with WL lottery names.
     * Faireum raffle slugs in database should be fixed. Slugs should be raffle specific like "10x-mega-millions-monks"
     * or generic like "10x-mega-millions" and used by multiple raffles.
     */
    public static function prizeInKindSlugToLotterySlug(string $slug): string
    {
        switch ($slug) {
            case '100x-euromillions':
                return 'euromillions';
                break;
            case '10x-mega-millions':
                return 'mega-millions';
                break;
            case '5x-gg-world-x':
                return 'gg-world-x';
                break;
            case '1x-gg-world-million':
                return 'gg-world-million';
                break;
            default:
                return $slug;
        }
    }
    
}
