<?php

namespace Test\Selenium\Blocked;

use Test\Selenium;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Abstracts\AbstractSelenium;
use Facebook\WebDriver\WebDriverExpectedCondition;

class SeleniumBlockedCountryService extends AbstractSelenium
{
    public function addCountry(string $countryCode): void
    {
        // click "Add New" button
        $this->driver->findElement(WebDriverBy::cssSelector('body > div.container-fluid > div.col-md-10 > div.pull-right > a'))->click();

        // choose country
        $this->driver->findElement(WebDriverBy::cssSelector("#inputCode > option[value={$countryCode}]"))->click();

        // click "Submit" button
        $this->driver->findElement(WebDriverBy::cssSelector('body > div > div.col-md-10 > div > div > form > button'))->click();

        // if country exist in database e.g. in development environment redirect to default page
        $this->getBlockedCountryPage();
    }

    public function deleteAllCountries(): void
    {
        $buttonSelector = 'body > div.container-fluid > div.col-md-10 > div.container-fluid.container-admin > div > table > tbody > tr > td:nth-child(4) > button';

        while (count($this->driver->findElements(WebDriverBy::cssSelector($buttonSelector))) !== 0) {
            $button = $this->driver->findElement(WebDriverBy::cssSelector($buttonSelector));

            $button->click();

            // wait on modal
            $this->driver->wait()->until(
                WebDriverExpectedCondition::elementToBeClickable(
                    WebDriverBy::id('confirmOK')
                )
            );

            // click yes in modal
            $this->driver->findElement(WebDriverBy::id('confirmOK'))->click();
        }
    }

    public function blockAllCountries(): void
    {
        $blockButtonSelector  = 'body > div.container-fluid > div.col-md-10 > div.container-fluid.container-admin > div > table > tbody > tr > td:nth-child(3) > div > label';

        while (count($this->driver->findElements(WebDriverBy::cssSelector($blockButtonSelector))) !== 0) {
            $blockButton  = $this->driver->findElement(WebDriverBy::cssSelector($blockButtonSelector));

            $blockButton->click();
        }
    }

    public function getBlockedCountryPage(): void
    {
        $this->driver->get(SeleniumLoginService::MANAGER_LOGIN_URL . "/blocked_countries");
    }

    public function checkIfCountriesExist(array $countriesCodes, Selenium $selenium): void
    {
        foreach ($countriesCodes as $code) {
            $selenium->assertIsObject(
                WebDriverBy::cssSelector(
                    "body > div.container-fluid > div.col-md-10 > div.container-fluid.container-admin > div > table > tbody > tr > td[text={$code}]"
                )
            );
        }
    }

    public function checkIfCountriesNoExist(array $countriesCodes, Selenium $selenium): void
    {
        foreach ($countriesCodes as $code) {
            $selenium->assertTrue(
                count(
                    $this->driver->findElements(WebDriverBy::cssSelector(
                        "body > div.container-fluid > div.col-md-10 > div.container-fluid.container-admin > div > table > tbody > tr > td[text={$code}]"
                    ))
                ) === 0
            );
        }
    }

    public function addRandomCountries(array $countries): array
    {
        // create helper instance, you have to add driver once
        $this->getBlockedCountryPage();

        $randomCountries = array_rand($countries, 5);

        // add 5 random countries
        foreach ($randomCountries as $code) {
            $this->addCountry($code);
        }

        return $randomCountries;
    }
}
