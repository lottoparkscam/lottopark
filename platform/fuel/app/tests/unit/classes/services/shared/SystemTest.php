<?php

namespace Tests\Unit\Services\Shared;

use DateTimeImmutable;
use Models\Whitelabel;
use Services\Shared\System;
use Test_Unit;

final class SystemTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider baseUrlWlExistsDataProvider
     * @param string $domain
     */
    public function baseUrl_WlExists_ReturnsWlDomain(string $domain): void
    {
        // Given
        $expected = 'https://somedomain.com/';

        $wl = new Whitelabel();
        $wl->domain = $domain;

        // When
        $system = new System('prod', new DateTimeImmutable(), parse_url('https://www.lottopark.com'), $wl);

        $actual = $system->baseFullUrl();

        // Then
        $this->assertSame($expected, $actual);
    }

    /** @test */
    public function baseUrl_WlNotExists_ReturnsFuelsBaseUrlConfigValue(): void
    {
        // Given
        $expected = 'https://www.someurl.com/';

        // When
        $system = new System('prod', new DateTimeImmutable(), parse_url('https://www.someurl.com'));

        $actual = $system->baseFullUrl();

        // Then
        $this->assertSame($expected, $actual);
    }

    public function baseUrlWlExistsDataProvider(): array
    {
        return [
            'domain without slash at the end' => ['https://somedomain.com'],
            'domain with slash at the end' => ['https://somedomain.com/'],
            'domain without the protocol' => ['somedomain.com/'],
        ];
    }
}
