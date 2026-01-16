<?php

namespace Test\Selenium\Traits;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

trait FooterTrait
{
    public function checkRedirectHomePage(string $homeCssLink): void
    {
        $this->elementHasText($homeCssLink, 'Home');
        $this->driver->findElement(WebDriverBy::cssSelector($homeCssLink))->click();
        $this->assertUrlIsCorrect($this->appUrl());
    }

    public function checkTermsAndConditionsRedirect(string $termsAndConditionsLink): void
    {
        $this->elementHasText($termsAndConditionsLink, 'Terms and Conditions');
        $this->driver->findElement(WebDriverBy::cssSelector($termsAndConditionsLink))->click();
        $this->assertUrlIsCorrect($this->appUrl() . 'terms/');
    }

    public function checkPrivacyPolicyRedirect(string $privacyPolicyLink): void
    {
        $this->elementHasText($privacyPolicyLink, 'Privacy Policy');
        $this->driver->findElement(WebDriverBy::cssSelector($privacyPolicyLink))->click();
        $this->assertUrlIsCorrect($this->appUrl() . 'privacy/');
    }

    public function checkFooterText(): void
    {
        $this->elementHasText(
            'body > footer > div:nth-child(2) > p:nth-child(1)',
            'LottoPark is an independent service website offering online sale of lotto tickets and is not connected nor supervised by National Lottery,' .
                ' MUSL Camelot Plc, or any other provider of the products available on this website.' .
                ' EuroMillions is a Services aux Loteries en Europe brand. National Lottery and Lotto are Camelot Group Plc. Brands.'
        );
        $this->elementHasText(
            'body > footer > div:nth-child(2) > p:nth-child(2)',
            'All lottopark.loc products are operated by White Lotto B.V., Fransche Bloemweg 4, Willemstad, Curacao.' .
                ' A company licensed and regulated by the law of Curacao under the Master License Holder Curacao eGaming with license number 8048/JAZ.' .
                ' White Lto Limited (CY) (reg.number HE 413497) with a registered office located at Voukourestiou, 25 Neptun House, 1st floor, Flat/Office 11,' .
                ' Zakaki,3045, Limassol, Cyprus, is acting as an Agent on behalf of the license-holding entity White Lotto B.V.'
        );
    }

    public function checkXCMImage(string $imageCss, string $height = '42', string $width = '42'): void
    {
        $this->assertCssValue($imageCss, 'height', $height);
        $this->assertCssValue($imageCss, 'width', $width);
    }

    public function checkChangeLanguageFromEnglishToPolish(string $englishSelectorCss, string $polishSelectorCss, string $loginUserButtonCss): void
    {
        $this->driver->findElement(WebDriverBy::cssSelector($englishSelectorCss))->click();
        $this->driver->findElement(WebDriverBy::cssSelector($polishSelectorCss))->click();
        $this->elementHasText($loginUserButtonCss, 'Logowanie');
        $this->assertUrlIsCorrect($this->appUrl() . 'pl/');
    }

    public function checkAntillephoneImage(string $imageCss, string $height = '42', string $width = '42'): void
    {
        $this->assertCssValue($imageCss, 'height', $height);
        $this->assertCssValue($imageCss, 'width', $width);
    }

    public function checkAntillephoneLink(string $imageCss): void
    {
        $this->driver->findElement(WebDriverBy::cssSelector($imageCss))->click();
        $this->driver->switchTo()->window($this->driver->getWindowHandles()[1]);
        $this->driver->wait()->until(WebDriverExpectedCondition::titleIs('License Validation'));
        $this->assertTrue(
            preg_match(
                "https://validator.antillephone.com/validate?domain=lottopark.loc&seal_id=([a-z0-9]){96}&stamp=([a-z0-9]){32}",
                $this->driver->getCurrentURL()
            )
        );
    }
}
