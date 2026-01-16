<?php

namespace Test\Selenium\Interfaces;

interface FooterTestInterface
{
    public const HOME_LINK = '#menu-item-138';
    public const TERMS_AND_CONDITIONS_LINK = '#menu-item-137';
    public const PRIVACY_POLICY_LINK = '#menu-item-136';
    public const FOOTER_IMAGE = 'body > footer > div.footer-logotypes > img';

    public function clickHome_Redirect_HomePage(): void;

    public function clickTermsAndConditions_Redirect_TermsAndConditionsPage(): void;

    public function clickPrivacyPolicy_Redirect_PrivacyPolicyPage(): void;

    public function checkFooterText_IsCorrect(): void;

    public function checkFooterImage_IsCorrect(): void;

    public function checkChangeLanguage_FromEnglishToPolish_IsCorrect(): void;
}
