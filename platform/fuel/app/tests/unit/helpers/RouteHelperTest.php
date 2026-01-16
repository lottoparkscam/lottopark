<?php

namespace Tests\Unit\Helpers;

use Helpers\RouteHelper;
use phpmock\phpunit\PHPMock;
use Test_Unit;

final class RouteHelperTest extends Test_Unit
{
    use PHPMock;

    /**
     * DO NOT USE HERE AUTOLOADED CLASS BECAUSE WE USE THIS FUNCTION IN WORDPRESS_IN_FUEL.PHP
     * We use global here because this function is also used in wordpress_in_fuel.
     * There isn't autoloader, DB, config and other things, so we import casinoConfig by require_once
     */
    public function setUp(): void
    {
        parent::setUp();

        global $casinoConfig;

        $casinoConfig['slugMap'] = [
            'lottopark.loc' => [
                'casino-play' => 'instant-games-play',
            ],
        ];

        $permalinkBySlugMock1 = $this->getFunctionMock(
            'Helpers',
            'lotto_platform_get_permalink_by_slug'
        );

        $permalinkBySlugMock1->expects($this->any())->willReturnCallback(function ($parameter) {

            $baseCasinoUrl = 'https://casino.lottopark.loc/';
            $existingSites = [
                'instant-games-play',
            ];

            $siteIndex = array_search($parameter, $existingSites);

            if ($siteIndex !== false) {
                return $baseCasinoUrl . $existingSites[$siteIndex] . '/';
            }

            return $baseCasinoUrl . $parameter . '/';
        });
    }

    /**
     * @test
     */
    public function getPermalinkBySlug_NoDomainSet_ShouldNotReplaceWithValidCasinoLink(): void
    {
        $slug = 'casino-play';
        $domain = null;

        $actual = RouteHelper::getPermalinkBySlug($slug, $domain);
        $expected = 'https://casino.lottopark.loc/casino-play/';

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function getPermalinkBySlug_DomainSet_ShouldReplaceWithValidCasinoLinkForGivenDomain(): void
    {
        $slug = 'casino-play';
        $domain = 'lottopark.loc';

        $actual = RouteHelper::getPermalinkBySlug($slug, $domain);
        $expected = 'https://casino.lottopark.loc/instant-games-play/';

        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     */
    public function getPermalinkBySlug_DomainSet_shouldNotReplaceWithValidCasinoLinkForGivenDomain(): void
    {
        $slug = 'general-terms-and-conditions';
        $domain = 'lottopark.loc';

        $actual = RouteHelper::getPermalinkBySlug($slug, $domain);
        $expected = 'https://casino.lottopark.loc/general-terms-and-conditions/';

        $this->assertSame($expected, $actual);
    }
}
