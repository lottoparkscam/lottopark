<?php

namespace Test;

use Container;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Fuel\Core\TestCase;
use Wrappers\Decorators\ConfigContract;
use Fuel\Core\Cache;
use Test\Selenium\Traits\SeleniumWrappersTrait;

/**
 * 1. We cannot use transactions here.
 * 2. We cannot use dd(), die() or other commands thats stop code. It won't trigger $this->driver->quit()
 * 3. Remember to revert db updates or the test will success only once. We should have dedicated db for tests to avoid it.
 */
abstract class Selenium extends TestCase
{
    use SeleniumWrappersTrait;

    protected static bool $wasLoaded = false;

    protected static RemoteWebDriver $staticDriver;
    protected RemoteWebDriver $driver;
    protected \DI\Container $container;
    protected ConfigContract $configContract;

    /** Some tricky way to speed up tests, create driver only once per class. */
    public static function setUpBeforeClass(): void
    {
        if (!self::$wasLoaded) {
            self::$staticDriver = self::driver();
            self::$wasLoaded = true;
        }
    }

    /**
     * Prepare for Selenium test execution.
     */
    protected function setUp(): void
    {
        Cache::delete_all();
        parent::setUp();
        $this->container = Container::forge(false);
        $this->configContract = $this->container->get(ConfigContract::class);

        $appUrl = $this->configContract->get('test.selenium.app_url');
        $host = rtrim(str_replace('https://', '', $appUrl), '/');

        $_SERVER['SITE_URL'] = $appUrl;
        $_SERVER['HTTP_HOST'] = $host;

        $this->driver = self::$staticDriver;
    }

    /**
     * Important: Remember to delete inserted data in DB.
     * We cannot use transactions here.
     * If you don't delete for eg. new user the test will pass only once
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->driver->manage()->deleteAllCookies();
    }

    public static function tearDownAfterClass(): void
    {
        self::$wasLoaded = false;
        self::$staticDriver->quit();
    }

    // Create the RemoteWebDriver instance.
    protected static function driver(): RemoteWebDriver
    {
        /** @var ConfigContract $configContract */
        $configContract = Container::get(ConfigContract::class);

        $arguments = [
            '--window-size=1920,1080',
            '--ignore-ssl-errors=yes',
            '--ignore-certificate-errors',
        ];

        if ($configContract->get('test.selenium.is_local')) {
            $arguments[] = '--disable-gpu';
            $arguments[] = '--no-sandbox';
        }

        $options = (new ChromeOptions())->addArguments($arguments);

        $desiredCapabilities = DesiredCapabilities::chrome()->setCapability(
            ChromeOptions::CAPABILITY,
            $options
        );

        $desiredCapabilities->setCapability('acceptSslCerts', true);

        return RemoteWebDriver::create('http://chrome:4444', $desiredCapabilities);
    }

    //Get base url with trailing slash.
    protected function appUrl(string $path = null): string
    {
        return $this->configContract->get('test.selenium.app_url') . $path;
    }
}
