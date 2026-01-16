<?php

namespace Test\Selenium\Abstracts;

use Facebook\WebDriver\Remote\RemoteWebDriver;

/** @deprecated - why we use it if we have it on setUp()? */
abstract class AbstractSelenium
{
    /** @var RemoteWebDriver $driver */
    protected $driver;

    public function __construct(RemoteWebDriver $driver)
    {
        $this->driver = $driver;
    }
}
