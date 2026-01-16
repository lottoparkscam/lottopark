<?php

namespace Test\Selenium\Traits;

use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\Remote\RemoteWebElement;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Exception\NoSuchElementException;

/** Make easier selenium functions */
trait SeleniumWrappersTrait
{
    protected RemoteWebDriver $driver;

    /** @throws NoSuchElementException: */
    protected function findById(string $id): RemoteWebElement
    {
        return $this->driver->findElement(WebDriverBy::id($id));
    }

    protected function setInputValue(RemoteWebElement $element, string|int|bool $value): void
    {
        $element->sendKeys($value);
    }

    protected function findByCssSelector(string $cssSelector): RemoteWebElement
    {
        return $this->driver->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    /** @param string $classes - dot separated values */
    protected function findByClasses(string $htmlTag, string $classes): RemoteWebElement
    {
        return $this->driver->findElement(WebDriverBy::cssSelector("$htmlTag.$classes"));
    }

    protected function findByType(string $htmlTag, string $value): RemoteWebElement
    {
        return $this->driver->findElement(WebDriverBy::cssSelector(
            $this->getCssSelector($htmlTag, 'type', $value)
        ));
    }

    protected function findChildByHtmlTag(RemoteWebElement $parent, string $htmlTag): RemoteWebElement
    {
        return $parent->findElement(WebDriverBy::tagName($htmlTag));
    }

    /** @return RemoteWebElement[] */
    protected function findChildren(RemoteWebElement $parent): array
    {
        return $parent->findElements(WebDriverBy::cssSelector('*'));
    }

    /** 
     * html: <div class="platform-alert platform-alert-info">
     * eg. css selector = div platform-alert.platform-alert-info
     */
    protected function noExistsInBody(string $cssSelector): void
    {
        sleep(1);
        $elements = $this->driver->findElements(WebDriverBy::cssSelector("body $cssSelector"));

        $this->assertEmpty($elements, "Element body $cssSelector exists in HTML DOM!");
    }

    protected function getHref(RemoteWebElement $element): string
    {
        return $element->getAttribute('href');
    }

    protected function getFlashMessage(): string
    {
        $this->driver->wait()->until(
            function () {
                $elements = $this->driver->findElements(WebDriverBy::cssSelector('div.platform-alert'));

                return count($elements) >= 1;
            },
            'Cannot find flash message.'
        );
        return $this->driver->findElement(WebDriverBy::cssSelector('div.platform-alert'))->getText();
    }

    protected function getFlashMessages(): array
    {
        $flashMessagesArray = [];

        $this->driver->wait()->until(
            function () {
                $elements = $this->driver->findElements(WebDriverBy::cssSelector("div.platform-alert"));

                return count($elements) >= 1;
            },
            'Cannot find any flash message. '
        );

        $flashMessages = $this->driver->findElements(WebDriverBy::cssSelector('div.platform-alert'));
        foreach ($flashMessages as $flashMessage) {
            $classes = explode(' ', $flashMessage->getAttribute('class'));
            $type = end($classes);
            $flashMessagesArray[] = [
                'message' => $flashMessage->getText(),
                'type' => $type
            ];
        }

        return $flashMessagesArray;
    }

    /**
     * @todo Add new cases when they will be needed
     * During task I've added only required for me
     */
    protected function getCssSelector(string $htmlTag, string $type, string $value): string
    {
        switch ($type) {
            case 'type':
                return $htmlTag . "[type='$value']";
            default:
                return '';
        };
    }

    protected function refresh(): void
    {
        $this->driver->navigate()->refresh();
    }
}
