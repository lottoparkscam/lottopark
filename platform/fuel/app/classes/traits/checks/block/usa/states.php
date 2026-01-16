<?php

/**
 * Use this trait to check if client US State should be blocked.
 * Trait Traits_Checks_Block_Usa_States
 */
trait Traits_Checks_Block_Usa_States
{

    /**
     * Check if specified US STATE should be blocked.
     * @param $whitelabel
     * @param null $ip
     * @return bool
     */
    private function is_us_state_blocked($whitelabel, $ip = null): bool
    {
        if(!$whitelabel['us_state_active']) {
            return false;
        }

        // Get user IP
        $ip_address = $ip ?: Lotto_Security::get_IP();

        // Get user geo city localization
        $geo_ip = Lotto_Helper::get_geo_IP_city_record($ip_address);

        // Check if geo was loaded successfully
        if (empty($geo_ip->country->isoCode) || empty($geo_ip->mostSpecificSubdivision->isoCode)) {
            return false;
        }

        // Check if user is from US
        if ($geo_ip->country->isoCode != "US") {
            return false;
        }

        $enabled_us_states = [];
        if (!empty($whitelabel['enabled_us_states'])) {
            $enabled_us_states = unserialize($whitelabel['enabled_us_states']);
        }

        if (!is_array($enabled_us_states)) {
            return false;
        }

        // Check if user's US State is whitelisted
        if (!in_array($geo_ip->mostSpecificSubdivision->isoCode, $enabled_us_states)) {
            return true;
        }

        return false;
    }
}
