<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\CountryHelper;
use Test_Unit;

final class CountryTest extends Test_Unit
{
    /** @test */
    public function getCountryCodesByCountryName_shouldReturnTheSameCountryCode()
    {
        $countryCode = 'PL';
        $countryName = CountryHelper::COUNTRY_CODES[$countryCode];
        $countryCodeFromFunction = CountryHelper::getCountryCodesByCountryName($countryName);

        $this->assertSame([$countryCode], $countryCodeFromFunction);

        $countryCode = 'GB';
        $countryName = CountryHelper::COUNTRY_CODES[$countryCode];
        $countryCodeFromFunction = CountryHelper::getCountryCodesByCountryName($countryName);

        $this->assertSame([$countryCode], $countryCodeFromFunction);

        $countryCode = 'GB';
        $countryName = 'UnITEd KingDOm Of Great BRITain';
        $countryCodeFromFunction = CountryHelper::getCountryCodesByCountryName($countryName);
        $this->assertSame([$countryCode], $countryCodeFromFunction);
    }

    /** @test */
    public function getCountryCodesByCountryName_shouldReturnTheSameCountryCode_ifValueIsCorrectButNotFullName()
    {
        $countryName = 'United';
        $countryCodeFromFunction = CountryHelper::getCountryCodesByCountryName($countryName);
        $this->assertTrue(in_array('GB', $countryCodeFromFunction));
        $this->assertTrue(in_array('US', $countryCodeFromFunction));
    }

    /** @test */
    public function getCountryCodesByCountryName_ifValueIsCorrectButFewCountryNameContainsTheSameName()
    {
        $countryCode = 'PL';
        $countryName = 'Polan';
        $countryCodeFromFunction = CountryHelper::getCountryCodesByCountryName($countryName);

        $this->assertSame([$countryCode], $countryCodeFromFunction);

        $countryCode = 'GB';
        $countryName = 'United Kingdom of Great Brita';
        $countryCodeFromFunction = CountryHelper::getCountryCodesByCountryName($countryName);

        $this->assertSame([$countryCode], $countryCodeFromFunction);
    }

    /** @test */
    public function getCountryCodesByCountryName_shouldReturnEmptyString()
    {
        $countryName = 'asxasdasxads';
        $countryCodeFromFunction = CountryHelper::getCountryCodesByCountryName($countryName);

        $this->assertEmpty($countryCodeFromFunction);
    }
}
