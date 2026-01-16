<?php

namespace Tests\Browser\Crm;

use Container;
use Fuel\Core\Cache;
use Models\Whitelabel;
use Models\WhitelabelUser;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverKeys;
use Test\Selenium\SeleniumUserService;
use Test\Selenium\SeleniumGlobalService;
use Test\Selenium\SeleniumPaymentService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\CrmInterface;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Table\SeleniumTableService;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;
use Test\Selenium\Interfaces\UserInterface;

//TODO To fix
final class SuperadminManualDepositTest extends AbstractSeleniumPageBase implements CrmInterface, UserInterface
{
    public const CRM_TABLE_CONTENT = '//*[@id="main-wrapper"]/div/div[2]/div/div[2]/div/div/div/div/div[4]/div/table';
    public const CRM_USER_BALANCE = 1000;
    public const CRM_USER_BONUS_BALANCE = 1000;
    public const CRM_USER_BALANCE_XPATH = '//*[@id="main-wrapper"]/div/div[2]/div/div[2]/div/div/div/div/div[4]/div/table/tbody/tr[2]/td[4]/div[1]';

    private WhitelabelUserFixture $fixture;
    private SeleniumPaymentService $paymentHelper;
    private SeleniumUserService $userService;
    protected ?Whitelabel $contextWhitelabel;
    private SeleniumLoginService $loginService;
    private WhitelabelUser $whitelabelUser;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete_all();
        $this->tableService = new SeleniumTableService($this->driver);
        $this->paymentHelper = new SeleniumPaymentService($this->driver);
        $this->globalService = new SeleniumGlobalService($this->driver);
        $this->userService = new SeleniumUserService($this->driver);
        $this->fixture = Container::get(WhitelabelUserFixture::class);
        $this->loginService = new SeleniumLoginService($this->driver);
        $this->fixture->addRandomUser(self::CRM_USER_BALANCE, self::CRM_USER_BONUS_BALANCE);
        $this->whitelabelUser = $this->fixture->user;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (!empty($this->whitelabelUser)) {
            $this->whitelabelUser->delete();
        }
    }

    /** @test */
    public function isBalance_isCorrect(): void
    {
        $expectedBalance = $this->paymentHelper::convertToUSD(self::CRM_USER_BALANCE, 'EUR');
        $this->loginService->loginCrmSuperadmin();
        $this->driver->get(self::CRM_USERS_PATH);
        $this->waitForData(self::CRM_TABLE_CONTENT);
        $this->driver->wait(2000);

        $currentBalance = $this->driver->findElement(
            WebDriverBy::cssSelector('#main-wrapper > div > div.container-fluid > div > div:nth-child(2) > div > div > div > div > div.row.users-table > div > table > tbody > tr:nth-child(2) > td:nth-child(4) > div.m-b-0')
        )->getText();
        $currentBalance = str_replace(',', '', $currentBalance);
        $this->assertStringContainsString('US$' . $expectedBalance, $currentBalance);
    }

    /** @test */
    public function addManualBalance_isCorrect(): void
    {
        $convert = 2 * self::CRM_USER_BALANCE;
        $expectedBalance = $this->paymentHelper::convertToUSD((string)$convert, 'EUR');

        $this->loginService->loginCrmSuperadmin();

        $this->driver->get(self::CRM_USERS_PATH);
        $this->waitForData(self::CRM_TABLE_CONTENT);
        $this->driver->wait(2000);

        $this->globalService->clickElement('#main-wrapper > div > div.container-fluid > div > div:nth-child(2) > div > div > div > div > div.row.users-table > div > table > tbody > tr:nth-child(2) > td:nth-child(4) > div:nth-child(2) > div:nth-child(2) > a > i');
        $this->waitForData('//*[@id="main-wrapper"]/div/div[2]/div/div/div/form/div');
        $this->driver
            ->findElement(
                WebDriverBy::cssSelector('#main-wrapper > div > div.container-fluid > div > div > div > form > div > div:nth-child(4) > div.col-sm-9 > div')
            )
            ->findElement(
                WebDriverBy::cssSelector("option[value='1']")
            )
            ->click();

        $this->driver->findElement(WebDriverBy::id("single-input"))
            ->click()
            ->clear()
            ->sendKeys(WebDriverKeys::BACKSPACE)
            ->sendKeys(self::CRM_USER_BALANCE)
            ->sendKeys(WebDriverKeys::ENTER);

        $this->driver->get(self::CRM_USERS_PATH);
        $this->waitForData(self::CRM_USER_BALANCE_XPATH);

        $currentBalance = $this->driver->findElement(
            WebDriverBy::xpath(self::CRM_USER_BALANCE_XPATH)
        )->getText();

        $currentBalance = str_replace(',', '', $currentBalance);
        $this->assertStringContainsString('US$' . $expectedBalance, $currentBalance);
    }

    /** @test */
    public function changeManualBalance_isCorrect(): void
    {
        $this->loginService->loginCrmSuperadmin();
        $this->driver->get(self::CRM_USERS_PATH);
        $this->waitForData(self::CRM_TABLE_CONTENT);
        $this->driver->wait(2000);

        $expectedBalance = $this->paymentHelper::convertToUSD(2 * self::CRM_USER_BALANCE, 'EUR');

        $this->driver->findElement(
            WebDriverBy::xpath('//*[@id="main-wrapper"]/div/div[2]/div/div[2]/div/div/div/div/div[4]/div/table/tbody/tr[2]/td[4]/div[2]/div[1]')
        )->click();

        $this->waitForData('//*[@id="main-wrapper"]/div/div[2]/div/div/div/form/div/div[1]/p/button');
        $this->driver->findElement(WebDriverBy::id("single-input"))
            ->click()
            ->clear()
            ->sendKeys(2 * self::CRM_USER_BALANCE);

        $this->driver->findElement(
            WebDriverBy::cssSelector('#main-wrapper > div > div.container-fluid > div > div > div > form > div > div.form-group.m-b-0.text-right > button')
        )->click();

        $this->driver->get(self::CRM_USERS_PATH);
        $this->waitForData(self::CRM_USER_BALANCE_XPATH);

        $currentBalance = $this->driver->findElement(
            WebDriverBy::xpath(self::CRM_USER_BALANCE_XPATH)
        )->getText();

        $currentBalance = str_replace(',', '', $currentBalance);
        $this->assertStringContainsString('US$' . $expectedBalance, $currentBalance);
    }
}
