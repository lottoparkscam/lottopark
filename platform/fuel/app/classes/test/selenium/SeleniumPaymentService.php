<?php

namespace Test\Selenium;

use Helpers_Currency;
use Test\Selenium\Abstracts\AbstractSelenium;
use Facebook\WebDriver\WebDriverBy;

class SeleniumPaymentService extends AbstractSelenium
{
    public function makeDeposit(int $amount, string $depositUrl): void
    {
        $this->driver->get($depositUrl);
        $this->driver->findElement(WebDriverBy::id('inputAmount'))
            ->clear()
            ->sendKeys($amount);
        $this->driver->findElement(WebDriverBy::id('paymentSubmit'))
            ->click();
    }

    public static function convertToUSD(string $amountToCOnvert, string $currencyCode): string
    {
        return Helpers_Currency::convert_to_USD($amountToCOnvert, $currencyCode);
    }
}
