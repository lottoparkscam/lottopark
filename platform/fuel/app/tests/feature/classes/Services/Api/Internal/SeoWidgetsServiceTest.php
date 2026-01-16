<?php

namespace Feature\Services\Api\Internal;

use Services\Api\Internal\SeoWidgetsService;
use Symfony\Component\DomCrawler\Crawler;
use Test_Feature;

/**
 * generateIframe is used in shordcodes so it sets locale inside
 *
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
final class SeoWidgetsServiceTest extends Test_Feature
{
    private SeoWidgetsService $seoWidgetsServiceUnderTest;

    public function setUp(): void
    {
        parent::setUp();

        $this->seoWidgetsServiceUnderTest = $this->container->get(SeoWidgetsService::class);

        if (!defined('WP_PLUGIN_DIR')) {
            define('WP_PLUGIN_DIR', '');
        }

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
    }

    /** @test */
    public function generateIframe_withSize(): void
    {
        // Given
        $width = 200;
        $height = 500;
        $domain = $this->container->get('domain');
        $apiDomain = 'api.' . $domain;

        // When
        $htmlResponse = $this->seoWidgetsServiceUnderTest->generateIframe(
            'powerball',
            'pickNumbers',
            $width,
            $height,
        );

        // Then
        $crawler = new Crawler($htmlResponse);
        $iframe = $crawler->filter('iframe');

        $this->assertStringContainsString(
            "https://$apiDomain/api/internal/seoWidgets/?lotterySlug=powerball&widgetType=pickNumbers",
            $iframe->attr('src')
        );

        $this->assertEquals($height, $iframe->attr('height'));
        $this->assertEquals('100%', $iframe->attr('width'));
        $this->assertStringContainsString("max-width: {$width}px", $iframe->attr('style'));
    }

    /** @test */
    public function generateIframe_withDefaultSize(): void
    {
        // Given
        $domain = $this->container->get('domain');
        $apiDomain = 'api.' . $domain;

        // When
        $htmlResponse = $this->seoWidgetsServiceUnderTest->generateIframe(
            'powerball',
            'pickNumbers',
        );

        // Then
        $crawler = new Crawler($htmlResponse);
        $iframe = $crawler->filter('iframe');

        $this->assertStringContainsString(
            "https://$apiDomain/api/internal/seoWidgets/?lotterySlug=powerball&widgetType=pickNumbers",
            $iframe->attr('src')
        );

        $this->assertEquals(700, $iframe->attr('height'));
        $this->assertEquals('100%', $iframe->attr('width'));
        $this->assertStringContainsString("max-width: 400px", $iframe->attr('style'));
    }

    /** @test */
    public function generateIframe_languageIsSet(): void
    {
        // When
        $htmlResponse = $this->seoWidgetsServiceUnderTest->generateIframe(
            'powerball',
            'pickNumbers',
        );

        // Then
        $crawler = new Crawler($htmlResponse);
        $iframe = $crawler->filter('iframe');

        $this->assertStringContainsString(
            "language=en_GB.utf8",
            $iframe->attr('src')
        );
    }

    /** @test */
    public function generateIframe_currencyCodeIsSet(): void
    {
        // When
        $htmlResponse = $this->seoWidgetsServiceUnderTest->generateIframe(
            'powerball',
            'pickNumbers',
        );

        // Then
        $crawler = new Crawler($htmlResponse);
        $iframe = $crawler->filter('iframe');

        $this->assertStringContainsString(
            "currencyCode=EUR",
            $iframe->attr('src')
        );
    }
}
