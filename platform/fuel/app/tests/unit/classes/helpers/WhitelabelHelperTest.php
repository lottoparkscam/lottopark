<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers\WhitelabelHelper;
use Models\Whitelabel;
use Fuel\Core\Cache;
use Test_Unit;

final class WhitelabelHelperTest extends Test_Unit
{
    private Whitelabel $whitelabel;

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * We use global here because this function is also used in wordpress_in_fuel.
     * There isn't autoloader, DB, config and other things, so we import casinoConfig by require_once
     */
    public function setUp(): void
    {
        Cache::delete_all();
        parent::setUp();
        $this->whitelabel = $this->container->get('whitelabel');
        global $casinoConfig;

        $casinoConfig['titleMap'] = [
            'aff.lottopark.com' => [
                'TitleXYZ' => 'TitleABC',
            ],
            'instant.faireum.win' => [
                'Title123' => 'Title456',
            ],
            'premierloto.cm' => [
                'Casino' => 'Instant Games',
            ]
        ];
    }

    /** @test */
    public function getLoginFieldShouldReturnLogin(): void
    {
        $this->whitelabel->useLoginsForUsers = 1;
        $actual = WhitelabelHelper::getLoginField();

        $this->assertSame('login', $actual);
    }

    /** @test */
    public function getLoginFieldShouldReturnEmail(): void
    {
        $this->whitelabel->useLoginsForUsers = 0;
        $actual = WhitelabelHelper::getLoginField();

        $this->assertSame('email', $actual);
    }

    /** @test */
    public function isActivationRequiredShouldReturnTrue(): void
    {
        $this->whitelabel->userActivationType = Whitelabel::ACTIVATION_TYPE_REQUIRED;
        $this->assertTrue(WhitelabelHelper::isActivationRequired());
    }

    /** @test */
    public function isActivationRequiredIsOptionalShouldReturnFalse(): void
    {
        $this->whitelabel->userActivationType = Whitelabel::ACTIVATION_TYPE_OPTIONAL;

        $this->assertFalse(WhitelabelHelper::isActivationRequired());
    }

    /** @test */
    public function isActivationRequiredIsNoneShouldReturnFalse(): void
    {
        $this->whitelabel->userActivationType = Whitelabel::ACTIVATION_TYPE_NONE;

        $this->assertFalse(WhitelabelHelper::isActivationRequired());
    }

    /**
     * @test
     * @dataProvider getCasinoTitleMapProvider
     */
    public function getCorrectCasinoTitle(string $domain, string $actual, string $expected): void
    {
        $actual = WhitelabelHelper::convertTitle($actual, $domain);

        $this->assertSame($expected, $actual);
    }

    public function getCasinoTitleMapProvider(): array
    {
        return [
            ['aff.lottopark.com', 'TitleXYZ', 'TitleABC'],
            ['instant.faireum.win', 'Title123', 'Title456'],
            ['premierloto.cm', 'Casino', 'Instant Games']
        ];
    }
}
