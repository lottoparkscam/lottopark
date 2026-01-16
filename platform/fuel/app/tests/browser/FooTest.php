<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverBy;
use Test\Selenium;

final class FooTest extends Selenium
{
    /**
     * A basic browser test example.
     */
    public function testFoo(): void
    {
        $this->driver->get($this->appUrl());
        $this->driver->findElement(WebDriverBy::tagName('title'));
        $this->assertStringContainsString("LottoPark", $this->driver->getTitle());
    }
}
