<?php

namespace Tests\Browser\Pages;

use Test\Selenium\Interfaces\FooterTestInterface;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;

final class FooterTest extends AbstractSeleniumPageBase implements FooterTestInterface
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->driver->get($this->appUrl());
    }

    /** @test */
    public function clickHome_Redirect_HomePage(): void
    {
        $this->checkRedirectHomePage(self::HOME_LINK);
    }

    /** @test */
    public function clickTermsAndConditions_Redirect_TermsAndConditionsPage(): void
    {
        $this->checkTermsAndConditionsRedirect(self::TERMS_AND_CONDITIONS_LINK);
    }

    /** @test */
    public function clickPrivacyPolicy_Redirect_PrivacyPolicyPage(): void
    {
        $this->checkPrivacyPolicyRedirect(self::PRIVACY_POLICY_LINK);
    }

    /** @test */
    public function checkFooterText_IsCorrect(): void
    {
        $this->checkFooterText();
    }

    /** @test */
    public function checkFooterImage_IsCorrect(): void
    {
        $this->assertCssValue(self::FOOTER_IMAGE, 'height', '28');
        $this->assertCssValue(self::FOOTER_IMAGE, 'width', '1020');
    }

    /** @test */
    public function checkChangeLanguage_FromEnglishToPolish_IsCorrect(): void
    {
        $this->checkChangeLanguageFromEnglishToPolish(
            'body > footer > div:nth-child(2) > div > div.pull-right > div > div.menu-trigger > a',
            'body > footer > div:nth-child(2) > div > div.pull-right > div > div.menu-wrapper > ul > li > a',
            '#btn-login'
        );
    }
}
