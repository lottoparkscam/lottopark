<?php

namespace Tests\Browser\Mobile;

use Test\SeleniumMobile;
use Facebook\WebDriver\WebDriverBy;

final class FooTest extends SeleniumMobile
{
    public function testFoo(): void
    {
        $this->driver->get($this->appUrl());
        $this->driver->findElement(WebDriverBy::tagName('title'));
        $this->assertStringContainsString("LottoPark", $this->driver->getTitle());
    }
}
