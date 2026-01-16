<?php

namespace Tests\Browser\Lottery\Play;

use Container;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\SeleniumUserService;
use Test\Selenium\SeleniumGlobalService;
use Test\Selenium\SeleniumTicketService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Login\SeleniumLoginService;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Test\Selenium\Interfaces\EurojackpotInterface;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;

final class EurojackpotTest extends AbstractSeleniumPageBase implements UserInterface, EurojackpotInterface
{
    public const LINE_PRICE = 3.20; // euro
    public const PLAY_CONTINUE = '#play-continue';
    private SeleniumLoginService $loginService;
    private SeleniumGlobalService $globalService;
    private SeleniumTicketService $ticketService;
    private SeleniumUserService $userService;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginService = new SeleniumLoginService($this->driver);
        $this->globalService = new SeleniumGlobalService($this->driver);
        $this->ticketService = new SeleniumTicketService($this->driver);
        $this->userService = new SeleniumUserService($this->driver);
        $fixtureUser = Container::get(WhitelabelUserFixture::class);
        $fixtureUser->addUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userService->deleteUser(self::TEST_USER_EMAIL);
    }

    /** @test */
    public function playLottery_WithoutLoggedUser_Failure(): void
    {
        $this->ticketService->chooseTicket(self::EUROJACKPOT_PLAY_URL);
        $this->driver->wait()->until(WebDriverExpectedCondition::elementToBeClickable(WebDriverBy::cssSelector("a.btn.btn-primary.btn-order.popup-order")))->click();
        $this->assertUrlIsCorrect($this->globalService::getLotteryUrl() . 'auth/login/');
    }

    /** @test */
    public function playLottery_LoggedUserDidNotPickNumbers_NotBuyTicket(): void
    {
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->driver->get(self::EUROJACKPOT_PLAY_URL);
        $this->elementIsNotEnabled(self::PLAY_CONTINUE);
        $this->assertUrlIsCorrect(self::EUROJACKPOT_PLAY_URL);
    }

    /** @test */
    public function playLottery_LoggedUserWithoutBalance_PayTestPayment(): void
    {
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->globalService->updateBalanceByEmail(self::TEST_USER_EMAIL, 0);
        $this->ticketService->chooseTicket(self::EUROJACKPOT_PLAY_URL);
        $this->elementHasText('p.payment-info', 'Pay using Test payment.');
    }

    /** @test */
    public function checkNumbers_NumbersAreSelected_PressingClearAllShouldWorkProperly(): void
    {
        $this->driver->get(self::EUROJACKPOT_PLAY_URL);
        // Quick Pick 5 lines
        $this->driver->findElement(WebDriverBy::className("widget-ticket-quickpick-all"))->click();
        $this->driver->findElement(WebDriverBy::className("widget-ticket-clear-all"))->click();
        $this->elementIsNotEnabled(self::PLAY_CONTINUE);
    }

    /** @test */
    public function playLottery_LoggedUserWithoutCheckAllNumbers_CannotBuyTicket(): void
    {
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->driver->get(self::EUROJACKPOT_PLAY_URL);
        $bNumber = rand(1, 12);
        for ($i = 1; $i <= 5; $i++) {
            $number = rand(1, 50);
            $this->globalService->clickElement("div.widget-ticket-numbers > a:nth-child($number)");
        }
        $this->globalService->clickElement("div.widget-ticket-bnumbers > a:nth-child($bNumber)");
        $this->elementHasText(
            'span.widget-ticket-summary-content-total-value',
            '€0.00'
        );
        $this->elementIsNotEnabled(self::PLAY_CONTINUE);
    }

    /** @test */
    public function playLottery_LoggedUser_Success(): void
    {
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);

        // Get current user balance
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $this->ticketService->buyQuickPickTicket(self::EUROJACKPOT_PLAY_URL);
        $balanceBeforePlay = $user['balance'];
        $buyCost = number_format((5 * self::LINE_PRICE), 2);
        $this->driver->wait(10, 1000)->until(
            WebDriverExpectedCondition::urlIs($this->globalService::getLotteryUrl() . 'order/success/')
        );

        // Get frontend user balance
        // Refresh user model and get actual balance
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $balanceAfterPlay = $user['balance'];

        // Assert user balance properly changed by play cost
        $this->assertEquals(($balanceBeforePlay - $buyCost), $balanceAfterPlay);
        $balanceAfterPlayFormatted = "€" . number_format($balanceAfterPlay, 2);

        $frontUserBalanceAfterPlay = $this->driver->findElement(WebDriverBy::cssSelector('#user-balance-amount'))->getText();

        // Assert frontend user balance properly changed by play cost
        $this->assertEquals($frontUserBalanceAfterPlay, $balanceAfterPlayFormatted);
    }
}
