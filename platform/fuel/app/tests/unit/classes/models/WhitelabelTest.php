<?php

namespace Unit\Classes\Models;

use Helpers_General;
use Models\Whitelabel;
use Test_Unit;

class WhitelabelTest extends Test_Unit
{
    private Whitelabel $whitelabel;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabel = new Whitelabel();
    }

    /**
     * @dataProvider isThemeProvider
     * @test
     */
    public function isTheme(string $testingTheme, string $whitelabelTheme, bool $expectedResult): void
    {
        $this->whitelabel->theme = $whitelabelTheme;

        $result = $this->whitelabel->isTheme($testingTheme);
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @dataProvider isThemeProvider
     * @test
     */
    public function isNotTheme(string $testingTheme, string $whitelabelTheme, bool $expectedResult): void
    {
        $this->whitelabel->theme = $whitelabelTheme;

        $result = $this->whitelabel->isNotTheme($testingTheme);
        $this->assertEquals($expectedResult, !$result);
    }

    public static function isThemeProvider(): array
    {
        return [
            [Whitelabel::LOTTOPARK_THEME, Whitelabel::LOVCASINO_THEME, false],
            [Whitelabel::DOUBLEJACK_THEME, Whitelabel::DOUBLEJACK_THEME, true],
        ];
    }

    /**
     * @dataProvider hasCasinoBannerProvider
     * @test
     */
    public function hasCasinoBanner(string $theme, bool $expectedResult): void
    {
        $this->whitelabel->theme = $theme;
        $hasCasino = $this->whitelabel->hasCasinoBanner();
        $this->assertEquals($expectedResult, $hasCasino);
    }

    public static function hasCasinoBannerProvider(): array
    {
        return [
            [Whitelabel::FAIREUM_THEME, false], // Due to legal reasons, Faireum can not have banners with WhitelabelDomainHelper::LOTTOPARK_THEME, true],
            [Whitelabel::LOTTOMAT_THEME, true],
            [Whitelabel::LOTTOHOY_THEME, true],
            [Whitelabel::REDFOXLOTTO_THEME, false],
        ];
    }


    /**
     * @dataProvider registerRequiredFieldProvider
     * @test
     */
    public function isNameSurnameRequireDuringRegistration(int $registerFieldDisplay, bool $expectedResults): void
    {
        $this->whitelabel->registerNameSurname = $registerFieldDisplay;

        $this->assertSame($this->whitelabel->isNameSurnameRequiredDuringRegistration(), $expectedResults);
    }

    /**
     * @dataProvider registerRequiredFieldProvider
     * @test
     */
    public function isPhoneRequireDuringRegistration(int $registerFieldDisplay, bool $expectedResults): void
    {
        $this->whitelabel->registerPhone = $registerFieldDisplay;

        $this->assertSame($this->whitelabel->isPhoneRequiredDuringRegistration(), $expectedResults);
    }

    /**
     * @dataProvider registerRequiredFieldProvider
     * @test
     */
    public function isCompanyRequireDuringRegistration(int $registerFieldDisplay, bool $expectedResults): void
    {
        $this->whitelabel->useRegisterCompany = $registerFieldDisplay;

        $this->assertSame($this->whitelabel->isCompanyRequiredDuringRegistration(), $expectedResults);
    }

    public static function registerRequiredFieldProvider(): array
    {
        return [
            [Helpers_General::ACTIVATION_TYPE_NONE, false],
            [Helpers_General::ACTIVATION_TYPE_OPTIONAL, false],
            [Helpers_General::ACTIVATION_TYPE_REQUIRED, true]
        ];
    }

    /**
     * @dataProvider registerFieldProvider
     * @test
     */
    public function nameSurnameIsUsedDuringRegistration(int $registerFieldDisplay, bool $expectedResults): void
    {
        $this->whitelabel->registerNameSurname = $registerFieldDisplay;

        $this->assertSame($this->whitelabel->isNameAndSurnameUsedDuringRegistration(), $expectedResults);
    }

    /**
     * @dataProvider registerFieldProvider
     * @test
     */
    public function phoneIsUsedDuringRegistration(int $registerFieldDisplay, bool $expectedResults): void
    {
        $this->whitelabel->registerPhone = $registerFieldDisplay;

        $this->assertSame($this->whitelabel->isPhoneUsedDuringRegistration(), $expectedResults);
    }

    /**
     * @dataProvider registerFieldProvider
     * @test
     */
    public function companyIsUsedDuringRegistration(int $registerFieldDisplay, bool $expectedResults): void
    {
        $this->whitelabel->useRegisterCompany = $registerFieldDisplay;

        $this->assertSame($this->whitelabel->isCompanyUsedDuringRegistration(), $expectedResults);
    }

    public static function registerFieldProvider(): array
    {
        return [
            [Helpers_General::ACTIVATION_TYPE_NONE, false],
            [Helpers_General::ACTIVATION_TYPE_OPTIONAL, true],
            [Helpers_General::ACTIVATION_TYPE_REQUIRED, true],
        ];
    }
}
