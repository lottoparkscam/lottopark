<?php

/**
 * Use this trait to check if client should be blocked, based on country name.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
trait Traits_Checks_Block_Country
{
    /**
     * Check if specified country should be blocked.
     * @param string|null $iso_code Country code.
     * @param string|int|null $whitelabel_id loosely binded id of the whitelabel, null is safe.
     * @return true if location (based on country) is blocked.
     */
    private function is_country_blocked($iso_code, $whitelabel_id): bool
    {
        // check if iso code is valid
        if (empty($iso_code)) {
            return false; // do not block null or empty
        }
        
        // check if country is in blocked countries
        return in_array($iso_code, array_column(Model_Whitelabel_Blocked_Country::by_whitelabel_sort_code($whitelabel_id) ?: [], 'code'), true); // NOTE: 1. change null to empty array, if unable to fetch records.
                                                                                                                                           // NOTE: 2. array_column works fine with empty array - it will just return empty array
    }
}
