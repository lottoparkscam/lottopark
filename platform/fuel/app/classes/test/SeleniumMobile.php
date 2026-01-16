<?php

namespace Test;

use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Fuel\Core\TestCase;
use Fuel\Core\Config;

//Android configuration
abstract class SeleniumMobile extends TestCase
{
    private const ARGUMENTS = [
        '--window-size=360,640',
        '--disable-extensions',
        '--disable-popup-blocking',
        '--test-type',
        '--no-sandbox',
        '--ignore-certificate-errors',
        '--ignore-ssl-errors=yes'
    ];

    /**
     * @var \Facebook\WebDriver\Remote\RemoteWebDriver
     */
    protected $driver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driver = $this->driver();
        Config::load("test", true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->driver->quit();
    }

    protected static function driver(): RemoteWebDriver
    {
        $options = (new ChromeOptions())->addArguments(self::ARGUMENTS);
        $desiredCapabilities = DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            $options
        );
        $desiredCapabilities->android();
        $desiredCapabilities->setCapability('acceptSslCerts', true);
        return RemoteWebDriver::create('http://chrome:4444', $desiredCapabilities);
    }

    protected function appUrl(string $path = null): string
    {
        return Config::get("test.selenium.app_url") . $path;
    }
}
