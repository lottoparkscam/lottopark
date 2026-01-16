<?php

namespace Tests\Browser\Payment;

use Container;
use Fuel\Core\Cache;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\SeleniumUserService;
use Test\Selenium\SeleniumPaymentService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Login\SeleniumLoginService;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;

final class DepositTest extends AbstractSeleniumPageBase implements UserInterface
{
    private SeleniumPaymentService $paymentService;
    private SeleniumLoginService $loginService;
    private WhitelabelUserFixture $fixtureUser;
    private SeleniumUserService $userService;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete_all();
        $this->paymentService = new SeleniumPaymentService($this->driver);
        $this->userService = new SeleniumUserService($this->driver);
        $this->fixtureUser = Container::get(WhitelabelUserFixture::class);
        $this->fixtureUser->addUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD, 0, 0);
        $this->loginService = new SeleniumLoginService($this->driver);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userService->deleteUser();
    }

    /** @test */
    public function changeBalance_AfterDeposit_Success(): void
    {
        $deposit = 100;

        $this->loginService->loginUser();
        $this->paymentService->makeDeposit($deposit, self::DEPOSIT_URL);

        $urlAfterSuccessfulLoginRedirect = 'https://lottopark.loc/order/success';
        $this->assertStringContainsString(
            $urlAfterSuccessfulLoginRedirect,
            $this->driver->getCurrentURL()
        );

        $deposit = '€' . $deposit . '.00';

        $this->driver->get($this->appUrl() . self::USER_TRANSACTIONS_URL);
        $balanceAfterDeposit = $this->driver->findElement(WebDriverBy::cssSelector('#user-balance-amount'))
            ->getText();
        $this->assertStringContainsString(
            $balanceAfterDeposit,
            $deposit
        );

        // Get current user balance
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $balanceBeforeDeposit = $user['balance'];

        // Go to deposit page
        $this->driver->get(self::DEPOSIT_URL);
        $this->driver->wait(20, 1000)->until(
            WebDriverExpectedCondition::visibilityOf(
                $this->driver->findElement(WebDriverBy::id("your-deposit"))
            )
        );

        // Make deposit of 20 euro
        $this->driver->findElement(WebDriverBy::className('deposit-amount-value'))->click();

        // Submit payment
        $this->driver->findElement(WebDriverBy::id('paymentSubmit'))->click();

        // Wait until Thank You page appears
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::visibilityOf($this->driver->findElement(WebDriverBy::className("success-content")))
        );

        // Refresh model and get actual user's balance
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $balanceAfterDeposit = $user['balance'];

        // Get frontend balance value
        $frontBalanceValue = $this->driver->findElement(WebDriverBy::xpath('//*[@id="user-balance-amount"]'))->getText();

        // Format database balance value to match one from website
        $balanceAfterDepositFormatted = "€" . number_format($balanceAfterDeposit, 2);

        $this->driver->wait(1000, 5000);

        // Check if user's balance changed by deposit value
        $this->assertEquals(($balanceBeforeDeposit + 20), $balanceAfterDeposit);

        $this->driver->wait(1000, 5000);

        // Check if user's frontend balance changed by deposit value
        $this->assertEquals($balanceAfterDepositFormatted, $frontBalanceValue);
    }

    /** @test */
    public function deposit_WithTooHighAmounth_IsFailure(): void
    {
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $balanceBeforeDeposit = $user['balance'];

        $deposit = 10000;
        $this->loginService->loginUser();
        $this->paymentService->makeDeposit($deposit, self::DEPOSIT_URL);
        $this->assertStringContainsString(
            self::DEPOSIT_URL,
            $this->driver->getCurrentURL()
        );

        $balanceAfterDeposit = $user['balance'];

        $this->elementHasText(
            'body > div.content-area > div.main-width.error-area > div',
            'The maximum deposit is €1,000.00.'
        );

        $this->assertEquals($balanceAfterDeposit, $balanceBeforeDeposit);
    }

        /** @test */
        public function deposit_WithTooLowAmounth_IsFailure(): void
        {
            $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
            $balanceBeforeDeposit = $user['balance'];

            $deposit = 0.5;
            $this->loginService->loginUser();
            $this->paymentService->makeDeposit($deposit, self::DEPOSIT_URL);
            $this->assertStringContainsString(
                self::DEPOSIT_URL,
                $this->driver->getCurrentURL()
            );

            $balanceAfterDeposit = $user['balance'];

            $this->elementHasText(
                '#payment > div.payment-content > form > div.payment-type-item > div',
                'The minimum order for this payment type is €1.00.'
            );

            $this->assertEquals($balanceAfterDeposit, $balanceBeforeDeposit);
            $this->elementIsNotEnabled('#paymentSubmit');
        }

        /** @test */
        public function deposit_WithoutLoggedUser_RedirectToLoginSite(): void
        {
            $this->driver->get(self::DEPOSIT_URL);
            $this->assertStringContainsString(
                self::LOGIN_URL,
                $this->driver->getCurrentURL()
            );
        }
}
