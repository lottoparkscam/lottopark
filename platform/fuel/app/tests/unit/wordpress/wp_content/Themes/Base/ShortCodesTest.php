<?php

namespace Tests\Unit\Wordpress\Wp_content\Themes\Base;

use Models\Whitelabel;
use PHPUnit\Framework\MockObject\MockObject;
use Services\Api\Internal\SeoWidgetsService;
use Test_Unit;

final class ShortCodesTest extends Test_Unit
{
    private Whitelabel $whitelabel;
    private SeoWidgetsService|MockObject $seoWidgetsServiceMock;
    private const LOTTERY_SLUG = 'powerball';
    private const WIDGET_TYPE = 'pickNumbers';

    public function setUp(): void
    {
        parent::setUp();
        if (!defined('WPINC')) {
            define('WPINC', '');
        }

        if (!function_exists('add_shortcode')) {
            eval("
                function add_shortcode(){
                    return '';
                }
            ");
        }

        require_once($_ENV['WP_PATH'] . '/wp-content/themes/base/ShortCodes.php');

        $this->whitelabel = $this->container->get('whitelabel');
        $this->seoWidgetsServiceMock = $this->createMock(SeoWidgetsService::class);
        $this->container->set(SeoWidgetsService::class, $this->seoWidgetsServiceMock);
    }

    /** @test */
    public function whitelabelLicenceShouldReturnValid(): void
    {
        $this->whitelabel->licence = '1234/ASD';
        $this->assertSame($this->whitelabel->licence, whitelabelLicence());
    }

    /** @test */
    public function whitelabelLicence_LicenceDoesNotExist_ShouldReturnEmptyString(): void
    {
        $this->whitelabel->licence = null;
        $this->assertSame('', whitelabelLicence());
    }

    /** @test */
    public function whitelabelDomain(): void
    {
        $this->assertSame($this->whitelabel->domain, whitelabelDomain());
    }

    /** @test */
    public function getWhitelabelProperty_PropertyDoesNotExist_ShouldReturnEmpty()
    {
        $actual = getWhitelabelProperty('asd');
        $this->assertSame('', $actual);
    }

    /** @test */
    public function getWhitelabelProperty_PropertyExists_ShouldReturnValid()
    {
        $actual = getWhitelabelProperty('domain');
        $this->assertSame($this->whitelabel->domain, $actual);
    }

    /** @test */
    public function whitelabelCasinoDomain(): void
    {
        $expected = 'casino.' . $this->whitelabel->domain;
        $this->assertSame($expected, whitelabelCasinoDomain());
    }

    /** @test */
    public function whitelabelSupportEmail(): void
    {
        $this->whitelabel->domain = 'asd.cd';
        $expected = 'support@asd.cd';
        $this->assertSame($expected, whitelabelSupportEmail());
    }

    /** @test */
    public function whitelabelSupportEmail_DomainWithWWW_ShouldRemoveWWW(): void
    {
        $this->whitelabel->domain = 'www.asd.cd';
        $expected = 'support@asd.cd';
        $this->assertSame($expected, whitelabelSupportEmail());
    }

    /** @test */
    public function whitelabelCompany(): void
    {
        $this->whitelabel->companyDetails =
            'White Lotto B.V.,
        Fransche Bloemweg 4, Willemstad, Curacao';

        $this->assertSame($this->whitelabel->companyDetails, whitelabelCompany());
    }

    /** @test */
    public function whitelabelCompanyField_GetAddress(): void
    {
        $expected = 'Fransche Bloemweg 4, Willemstad, Curacao';

        $this->whitelabel->companyDetails =
            'White Lotto B.V.,
        Fransche Bloemweg 4, Willemstad, Curacao';

        $this->assertSame($expected, whitelabelCompanyField('address'));
    }

    /** @test */
    public function whitelabelCompanyField_GetName(): void
    {
        $expected = 'White Lotto B.V.,';

        $this->whitelabel->companyDetails =
            'White Lotto B.V.,
        Fransche Bloemweg 4, Willemstad, Curacao';

        $this->assertSame($expected, whitelabelCompanyField('name'));
    }


    /** @test */
    public function seoWidget_withoutData(): void
    {
        // When
        $htmlResponse = seoWidget([]);

        // Then
        $this->assertSame('', $htmlResponse);
    }

    /** @test */
    public function seoWidget_withBasicData(): void
    {
        // Expects
        $this->seoWidgetsServiceMock
            ->expects($this->once())
            ->method('generateIframe')
            ->with(self::LOTTERY_SLUG, self::WIDGET_TYPE);

        // When
        seoWidget([
            'lottery_slug' => self::LOTTERY_SLUG,
            'widget_type' => self::WIDGET_TYPE,
        ]);
    }

    /** @test */
    public function seoWidget_withFullData(): void
    {
        // Expects
        $this->seoWidgetsServiceMock
            ->expects($this->once())
            ->method('generateIframe')
            ->with(self::LOTTERY_SLUG, self::WIDGET_TYPE, 102, 103);

        // When
        seoWidget([
            'lottery_slug' => self::LOTTERY_SLUG,
            'widget_type' => self::WIDGET_TYPE,
            'width' => 102,
            'height' => 103
        ]);
    }
}
