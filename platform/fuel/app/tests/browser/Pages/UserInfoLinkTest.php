<?php

namespace Tests\Browser\Pages;

use Container;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Login\SeleniumLoginService;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Model_Whitelabel_User;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\SeleniumTicketService;
use Test\Selenium\SeleniumGlobalService;

final class UserInfoLinkTest extends AbstractSeleniumPageBase implements UserInterface
{
    public const PLAY_URL = '/play/superenalotto/';
    public const LINE_PRICE = 2.15;
    public const ORDER_INFO_ELEMENT = 'body > header > div > section > div.pull-left.order-info-area > div > a';
    public const USER_INFO_ELEMENT = 'body > header > div > section > div:nth-child(1) > div > div.user-info-area.menu-trigger > a';
    public const USER_NAME_AREA = '#user-info-user-name';
    private SeleniumGlobalService $globalService;
    private SeleniumTicketService $ticketService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->driver->get($this->appUrl());
        $this->globalService = new SeleniumGlobalService($this->driver);
        $this->ticketService = new SeleniumTicketService($this->driver);
    }

    public static function setUpBeforeClass(): void
    {
        $fixtureUser = Container::get(WhitelabelUserFixture::class);
        $fixtureUser->addUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
    }

    /** @test */
    public function checkInfoLink_BeforeLogIn(): void
    {
        $this->driver->wait(20, 2000)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::cssSelector(self::ORDER_INFO_ELEMENT),
                '0'
            )
        );
        $this->elementHasFormattedNumbers('#order-count', '0');
        $this->elementHasFormattedNumbers('#order-info-amount', '0.00');
    }

    /** @test */
    public function checkInfoLink_WithLoggedUser(): void
    {
        $this->loginHelper->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $balance = $user['balance'];
        $bonusBalance = $user['bonus_balance'];
        $this->driver->wait(20, 2000)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::cssSelector('#user-balance-amount'),
                '€10,000.00'
            )
        );
        $this->elementHasFormattedNumbers('#order-count', '0');
        $this->elementHasFormattedNumbers('#user-balance-amount', $balance);
        $this->elementHasFormattedNumbers('#user-bonus-balance-amount', 'bonus: ' . $bonusBalance);
    }

    /** @test */
    public function checkInfoLink_BeforeBuyTicket(): void
    {
        $this->loginHelper->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $balance = $user['balance'];
        $bonusBalance = $user['bonus_balance'];
        $this->ticketService->chooseTicket(self::PLAY_URL);
        $this->driver->get($this->appUrl() . self::HOMEPAGE);
        $this->driver->wait(20, 2000)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::cssSelector('#user-balance-amount'),
                '€10,000.00'
            )
        );
        $this->elementHasFormattedNumbers('#order-info-amount', (5 * self::LINE_PRICE));
        $this->elementHasFormattedNumbers('#order-count', 1);
        $this->elementHasFormattedNumbers('#user-balance-amount', $balance);
        $this->elementHasFormattedNumbers('#user-bonus-balance-amount', 'bonus: ' . $bonusBalance);
        $this->elementHasText(self::USER_NAME_AREA, $user['name']);
    }

    /** @test */
    public function checkInfoLink_AfterBuyTicket(): void
    {
        $this->loginHelper->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $user = SeleniumLoginService::getTestUserModel(self::TEST_USER_EMAIL);
        $balance = $user['balance'];
        $bonusBalance = $user['bonus_balance'];
        $this->ticketService->buyQuickPickTicket($this->appUrl() . self::PLAY_URL);
        $this->driver->get($this->appUrl() . self::HOMEPAGE);

        $balanceAfterBuy = $balance - (5 * self::LINE_PRICE);
        $this->driver->wait(20, 3000)->until(
            WebDriverExpectedCondition::elementTextContains(
                WebDriverBy::cssSelector('#order-count'),
                0
            )
        );
        $this->elementHasFormattedNumbers('#order-info-amount', '0.00');
        $this->elementHasFormattedNumbers('#user-balance-amount', round($balanceAfterBuy, 2));
        $this->elementHasFormattedNumbers('#user-bonus-balance-amount', 'bonus: ' . $bonusBalance);
        $this->elementHasText(self::USER_NAME_AREA, $user['name']);
    }

    public static function tearDownAfterClass(): void
    {
        $user = Model_Whitelabel_User::find_by(['email' => self::TEST_USER_EMAIL])[0];
        $user->delete();
    }
}
