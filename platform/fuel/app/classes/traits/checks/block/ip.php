<?php

/**
 * Use this trait to check if client ip should be blocked, based on country.
 * NOTE: Auto include Traits_Checks_Block_Country.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
trait Traits_Checks_Block_Ip
{
    use Traits_Checks_Block_Country;

    /**
     * Check if specified ip should be blocked.
     * @param string|int|null $whitelabel_id loosely binded id of the whitelabel, null is safe.
     * @param string|null $ip optional ip address - if not provided current one will be fetched and used.
     * @return true if country (based on ip) is blocked.
     */
    private function is_ip_blocked($whitelabel_id, $ip = null): bool
    {
        // fetch client ip, if address was not provided
        $ip_address = $ip ?: Lotto_Security::get_IP();

        // create geoip record based on our ip
        $geoip = Lotto_Helper::get_geo_IP_record($ip_address);
        
        // check if geoip could find country for specified ip
        if (!$geoip) {
            return false; // let user go
        }
        
        // check if country in geoip record is in blocked countries
        return $this->is_country_blocked($geoip->country->isoCode, $whitelabel_id); // NOTE: iso_code may pass null.
    }
}
