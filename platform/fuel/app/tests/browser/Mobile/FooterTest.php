<?php

namespace Tests\Browser\Mobile;

use Test\Selenium\Interfaces\FooterTestInterface;
use Test\Selenium\Abstracts\AbstractSeleniumMobile;

final class FooterTest extends AbstractSeleniumMobile implements FooterTestInterface
{
    public function setUp(): void
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
        $this->elementIsDisplayed(self::FOOTER_IMAGE);
    }

    /** @test */
    public function checkChangeLanguage_FromEnglishToPolish_IsCorrect(): void
    {
        $this->checkChangeLanguageFromEnglishToPolish(
            'footer .mobile-language',
            'footer .mobile-language > option:nth-child(2)',
            'body > div.login-signup-buttons-main-div-mobile > div.login-signup-buttons-login-div.login-signup-buttons-front-page-small > div > a'
        );
    }
}
