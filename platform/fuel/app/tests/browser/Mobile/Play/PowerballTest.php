<?php

namespace Tests\Browser\Mobile\Play;

use Container;
use Facebook\WebDriver\WebDriverBy;
use Test\Selenium\SeleniumUserService;
use Test\Selenium\SeleniumGlobalService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Interfaces\PowerballInterface;
use Test\Selenium\Abstracts\AbstractSeleniumMobile;

final class PowerballTest extends AbstractSeleniumMobile implements PowerballInterface, UserInterface
{
    public const CONTINUE_PICK_BUTTON = '#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-mobile-button > a';
    public const CONTINUE_BUTTON = '#play-continue';
    public const QUICKPICK_3 = 'body > div.content-area > div.widget.widget_lotto_platform_widget_ticket > div > div > div > div.small-purchase-section > a:nth-child(1)';
    private SeleniumGlobalService $globalService;
    private SeleniumLoginService $loginService;
    private SeleniumUserService $userService;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixtureUser = Container::get(WhitelabelUserFixture::class);
        $this->fixtureUser->addUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->globalService = new SeleniumGlobalService($this->driver);
        $this->loginService = new SeleniumLoginService($this->driver);
        $this->userService = new SeleniumUserService($this->driver);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userService->deleteUser(self::TEST_USER_EMAIL);
    }

    private function buyQuickPick(): void
    {
        $this->driver->get(self::PLAY_URL);
        $this->globalService->clickElement(self::QUICKPICK_3, $this->driver);
        $this->globalService->clickElement(self::CONTINUE_BUTTON, $this->driver);
    }

    /** @test */
    public function powerballPlayPage_mobile_showElements(): void
    {
        $this->driver->get(self::PLAY_URL);

        $this->elementIsNotEnabled(self::CONTINUE_BUTTON);
        $this->elementIsEnabled('#widget-ticket-form > div > div:nth-child(27) > a.btn.btn-tertiary.btn-xs.widget-ticket-entity-mobile-quickpick');
        $this->elementIsDisplayed('body > div.content-area > div.widget.widget_lotto_platform_widget_ticket > div > div > div > div.widget-ticket-header-wrapper > div.widget-ticket-image > img');
        $this->elementIsDisplayed(
            self::QUICKPICK_3,
            '3 Quick-Pick lines'
        );
        $this->elementIsDisplayed(
            'body > div.content-area > div.widget.widget_lotto_platform_widget_ticket > div > div > div > div.small-purchase-section > a:nth-child(2)',
            '6 Quick-Pick lines'
        );

        $selector = [1 => 'Play Powerball', 2 => 'Powerball Results', 3 => 'Powerball Information'];
        foreach ($selector as $key => $value) {
            $this->elementHasText("body > div.content-area > div.main-width.relative > div > select > option:nth-child($key)", $value);
        }
    }

    /** @test */
    public function cantBuyTicket_withoutLogin(): void
    {
        $this->buyQuickPick();
        $this->globalService->clickElement(
            'body > div.content-area > div > div > div.order-buttons > div.pull-right > a',
            $this->driver
        );
        $this->assertUrlIsCorrect('https://lottopark.loc/auth/login/');
    }

    /** @test */
    public function cantAddNewLine_Manually_withoutCorrectNumbers(): void
    {
        $confirmUnlockButton = '#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-mobile-button > a';
        $confirmButton = '#widget-ticket-form > div > div.widget-ticket-entity.relative.checked > div.widget-ticket-entity-content > div.widget-ticket-mobile-button > a';
        $this->driver->get(self::PLAY_URL);

        $this->globalService->clickElement(
            '#widget-ticket-form > div > div:nth-child(27) > a.widget-ticket-entity-newline',
            $this->driver
        );

        //Quick Pick
        $this->elementIsEnabled('#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-buttons > button.btn.btn-xs.btn-tertiary.widget-ticket-button-quickpick');

        for ($i = 1; $i < self::POWERBALL_NUMBERS; $i++) {
            $this->elementIsEnabled("#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-numbers > a:nth-child($i)");
        }

        for ($i = 1; $i < self::POWERBALL_BNUMBERS; $i++) {
            $this->elementIsEnabled("#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-numbers > a:nth-child($i)");
        }

        for ($i = 1; $i < 6; $i++) {
            $this->globalService->clickElement(
                "#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-numbers > a:nth-child($i)",
                $this->driver
            );
        }
        $this->elementIsDisplayed($confirmUnlockButton);

        $bnumber = rand(1, self::POWERBALL_BNUMBERS);
        $this->globalService->clickElement(
            "#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-bnumbers > a:nth-child($bnumber)",
            $this->driver
        );
        $this->elementIsDisplayed($confirmUnlockButton);

        $number = rand(5, self::POWERBALL_BNUMBERS);
        $this->globalService->clickElement(
            "#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-numbers > a:nth-child($number)",
            $this->driver
        );

        //Widget ticket icon OK
        $this->elementIsDisplayed('#widget-ticket-form > div > div.widget-ticket-entity.relative.checked > div.widget-ticket-entity-content > div.widget-ticket-icon-ok > span');

        //Unclick number
        $this->globalService->clickElement(
            "#widget-ticket-form > div > div:nth-child(2) > div.widget-ticket-entity-content > div.widget-ticket-numbers > a:nth-child($number)",
            $this->driver
        );

        $this->globalService->clickElement($confirmButton, $this->driver);
        $this->globalService->clickElement(self::CONTINUE_BUTTON, $this->driver);
        $this->elementIsEnabled('#dialog-button-close > button');
        $this->elementIsEnabled('#dialog-button-quickpick > button');
    }

    /** @test */
    public function canBuyTicket_withLoggedUser(): void
    {
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->buyQuickPick();
        $this->assertUrlIsCorrect('https://lottopark.loc/order/');
        $this->elementIsEnabled('body > div.content-area > div:nth-child(1) > div > div.order-buttons > div.pull-left > a');
        $this->elementIsEnabled('#paymentSubmit');
        $this->elementHasText(
            '#order-count',
            '1'
        );
        $this->globalService->clickElement('#paymentSubmit', $this->driver);
        $this->assertUrlIsCorrect('https://lottopark.loc/order/success/');
    }

    /** @test */
    public function canDeleteTicket_withLoggedUser(): void
    {
        $this->loginService->loginUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->buyQuickPick();
        $this->driver->get($this->appUrl());
        $this->elementHasText(
            '#order-count',
            '1'
        );
        $this->globalService->clickElement('#order-count', $this->driver);
        $this->globalService->clickElement(
            'body > div.content-area > div:nth-child(1) > div > table > tbody > tr:nth-child(1) > td:nth-child(4) > a > span',
            $this->driver
        );
        $this->elementHasText(
            'body > div.content-area > div > div > div.platform-alert.platform-alert-info.order-alert',
            'You don\'t have any tickets. Play now to add tickets to your order.'
        );
    }

    /** @test */
    public function cannotBuyTicket_withoutUserBalance_withBalance(): void
    {
        $email = 'zeroBalance@email.com';
        $password = 'qwerty';
        $this->fixtureUser->addUser($email, $password, 0, 0);
        $this->loginService->loginUser($email, $password);
        $this->buyQuickPick();
        $text = 'Test payment';
        $cssSelector = '#paymentTypeMobile > option:nth-child(1)';

        $this->assertSame(
            $text,
            $this->driver->findElement(WebDriverBy::cssSelector($cssSelector))
                ->getText()
        );

        $this->userService->deleteUser($email);
    }
}
