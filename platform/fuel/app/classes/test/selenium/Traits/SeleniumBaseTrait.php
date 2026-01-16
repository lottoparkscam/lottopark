<?php

namespace Test\Selenium\Traits;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

trait SeleniumBaseTrait
{
    protected function assertCssValue(
        string $cssSelector,
        string $cssElement,
        string $value
    ): void {
        $this->assertStringContainsString(
            $value,
            $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))->getCssValue($cssElement),
            'CSS value is not displayed'
        );
    }

    protected function elementIsDisplayed(string $cssSelector): void
    {
        $this->assertTrue(
            $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))
                ->isDisplayed(),
            'Element is not displayed'
        );
    }

    protected function elementIsNotDisplayed(string $cssSelector): void
    {
        $this->assertFalse(
            $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))
                ->isDisplayed(),
            'Element is displayed'
        );
    }

    protected function elementIsEnabled(string $cssSelector): void
    {
        $this->assertTrue(
            $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))
                ->isEnabled(),
            'Element is not enabled'
        );
    }

    protected function elementIsNotEnabled(string $cssSelector): void
    {
        $this->assertFalse(
            $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))
                ->isEnabled(),
            'Element is enabled'
        );
    }

    protected function elementHasText(string $cssSelector, string $text): void
    {
        $this->assertSame(
            $text,
            $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))
                ->getText(),
            'Text is not the same'
        );
    }

    protected function elementHasFormattedNumbers(string $cssSelector, string $number): void
    {
        $results = $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))
            ->getText();
        $results = str_replace([',', '€', '$'], '', $results);
        $this->assertSame(
            $number,
            $results,
            'Bad numbers format'
        );
    }

    protected function waitForData(string $xPath): void
    {
        $this->driver->wait()->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(
                WebDriverBy::xpath($xPath)
            )
        );
    }

    protected function assertUrlIsCorrect(string $url): void
    {
        $this->assertSame(
            $url,
            $this->driver->getCurrentURL(),
            'URL is not correct'
        );
    }

    protected function checkBackgroundColor(string $colourRGB, string $cssSelector): void
    {
        $this->assertCssValue(
            $cssSelector,
            'background-color',
            $colourRGB
        );
    }
}
