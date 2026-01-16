<?php

namespace Feature\Generic\Shared;

use DateTimeImmutable;
use Fuel\Core\Fuel;
use Models\Whitelabel;
use Services\Shared\System;
use Test_Feature;

class SystemTest extends Test_Feature
{
    private System $system;

    public function setUp(): void
    {
        parent::setUp();
        $this->system = $this->container->get(System::class);
    }

    /** @test */
    public function env__returns_fuels_env_value(): void
    {
        // Given
        $expected = Fuel::$env;

        // When
        $actual = $this->system->env();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function whitelabel__exists__returns_instance(): void
    {
        if (Whitelabel::query()->count() === 0) {
            $this->skip('Missing WL to continue test. Add factory call on setUp.');
        }

        // Given
        $expected = Whitelabel::class;

        // When
        $actual = get_class($this->system->whitelabel());

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function baseFullUrl_withWww_returnsCorrectUrl(): void
    {
        $system = new System('PROD', new DateTimeImmutable(), ['host' => 'lottohoy.com'], null);
        $this->assertEquals('http://www.lottohoy.com/', $system->baseFullUrl());
    }

    /**
     * baseFullUrl from casino has to return url based on whitelabel.domain
     * It should never contain casino subdomain
     * @test
     */
    public function baseFullUrl_withWww_returnsCorrectUrlForCasino(): void
    {
        $_SERVER['HTTP_HOST'] = 'casino.lottohoy.com'; // Simulate action taken from casino
        $system = new System('PROD', new DateTimeImmutable(), ['host' => 'lottohoy.com'], null);
        $this->assertEquals('http://www.lottohoy.com/', $system->baseFullUrl());
        $_SERVER['HTTP_HOST'] = 'lottopark.loc';
    }
}
