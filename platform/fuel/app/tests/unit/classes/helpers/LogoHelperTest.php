<?php

namespace Tests\Unit\Classes\Helpers;

use Container;
use Fuel\Core\Config;
use Helpers\LogoHelper;
use Models\Whitelabel;
use Test_Unit;

class LogoHelperTest extends Test_Unit
{
    /** @test */
    public function isCurrentWhitelabelLogoExists(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->theme = 'lottopark';

        $this->assertTrue(LogoHelper::isCurrentWhitelabelLogoExists());
    }

    /** @test */
    public function isCurrentWhitelabelLogoExists_notExists(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->theme = 'asdadsaadadasdadasd';

        $this->assertFalse(LogoHelper::isCurrentWhitelabelLogoExists());
    }

    /** @test */
    public function generateCurrentWhitelabelLogoWordpressPath(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->theme = 'lottopark';

        $this->assertEquals(Config::get('wordpress.path') . '/wp-content/themes/lottopark/images/logo.png', LogoHelper::generateCurrentWhitelabelLogoWordpressPath());
    }

    /** @test */
    public function generateCurrentWhitelabelLogoUrl(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->domain = 'lottopark.loc';
        $whitelabel->theme = 'lottopark';

        $this->assertEquals('https://lottopark.loc/wp-content/themes/lottopark/images/logo.png', LogoHelper::generateCurrentWhitelabelLogoUrl());
    }


    /** @test */
    public function generateWhitelabelImgLogoSection(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->domain = 'lottopark.loc';
        $whitelabel->theme = 'lottopark';
        $urlToLogo = LogoHelper::generateCurrentWhitelabelLogoUrl();
        $expectedHtmlImg = <<<HTML
        <img src="$urlToLogo" alt="$whitelabel->name" title="$whitelabel->name">
        HTML;
        $this->assertEquals(
            $expectedHtmlImg,
            LogoHelper::generateWhitelabelImgLogoSection()
        );
    }

    /** @test */
    public function getWhitelabelImgLogoSection(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->domain = 'lottopark.loc';
        $whitelabel->theme = 'lottopark';
        $urlToLogo = LogoHelper::generateCurrentWhitelabelLogoUrl();
        $expectedHtmlImg = <<<HTML
        <img src="$urlToLogo" alt="$whitelabel->name" title="$whitelabel->name">
        HTML;
        $this->assertEquals(
            $expectedHtmlImg,
            LogoHelper::generateWhitelabelImgLogoSection()
        );
    }

    /** @test */
    public function getWhitelabelImgLogoSection_logoNotExists(): void
    {
        /** @var Whitelabel $whitelabel */
        $whitelabel = Container::get('whitelabel');
        $whitelabel->domain = 'asdadadadadasdasdsdsa';
        $this->assertEmpty(LogoHelper::getWhitelabelImgLogoSection());
    }

    /** @test */
    public function getWhitelabelWidgetLogoUrl_whenNotExists(): void
    {
        // When
        $widgetLogoUrl = LogoHelper::getWhitelabelWidgetLogoUrl();

        // Then
        $this->assertFalse($widgetLogoUrl);
    }

    /** @test */
    public function getWhitelabelWidgetLogoUrl_whenExists(): void
    {
        // Given
        $whitelabel = $this->container->get('whitelabel');
        $whitelabel->theme = 'lottopark';
        $whitelabel->domain = 'lottopark.loc';
        $this->container->set('domain', 'lottopark.loc');

        // When
        $widgetLogoUrl = LogoHelper::getWhitelabelWidgetLogoUrl();

        // Then
        $expectedUrl = 'https://lottopark.loc/wp-content/themes/lottopark/images/logo-widget.png';
        $this->assertSame($expectedUrl, $widgetLogoUrl);
    }
}
