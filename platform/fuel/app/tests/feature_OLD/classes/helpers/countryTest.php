<?php

namespace Feature\Helpers;

use Helpers\CountryHelper;
use Test_Feature;

final class CountryTest extends Test_Feature
{
    /** @test */
    public function iso_from_ip__valid_ip__returns_iso2_code(): void
    {
        $ip = '51.77.244.72';
        $isoCode = CountryHelper::isoFromIP($ip);
        $this->assertEquals('FR', $isoCode);
    }

    /** @test */
    public function iso_from_ip__invalid_ip__returns_null(): void
    {
        $ip = '51:77.244..72';
        $isoCode = CountryHelper::isoFromIP($ip);
        $this->assertNull($isoCode);
    }

    /** @test */
    public function is_ip_from_countries__valid_ip_and_countries_array__returns_true(): void
    {
        $ip = '51.77.244.72';
        $countries = ['CN', 'FR'];

        $is_from_FR_or_CN = CountryHelper::isIPFromCountries($ip, $countries);
        $this->assertTrue($is_from_FR_or_CN);
    }

    /** @test */
    public function is_ip_from_countries__invalid_ip__returns_false(): void
    {
        $ip = '51:77.244..72';
        $countries = ['CN', 'FR'];

        $is_from_FR_or_CN = CountryHelper::isIPFromCountries($ip, $countries);
        $this->assertFalse($is_from_FR_or_CN);
    }

    /** @test */
    public function is_ip_from_countries__invalid_countries_array__returns_false(): void
    {
        $ip = '51.77.244.72';
        $countries = ['PL'];

        $is_from_PL = CountryHelper::isIPFromCountries($ip, $countries);
        $this->assertFalse($is_from_PL);
    }
}
