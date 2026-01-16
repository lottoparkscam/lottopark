<?php

namespace Tests\Browser\Auth;

use Container;
use Fuel\Core\Cache;
use Test\Selenium\SeleniumUserService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium\Abstracts\AbstractSeleniumPageBase;

final class UserRegistrationTest extends AbstractSeleniumPageBase implements UserInterface
{
    public const ALTERT_ERROR_CSS = 'div.platform-alert.platform-alert-error';
    private SeleniumLoginService $loginService;
    private WhitelabelUserFixture $fixtureUser;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::delete_all();
        $this->driver->get(self::REGISTER_URL);
        $this->userService = new SeleniumUserService($this->driver);
        $this->fixtureUser = Container::get(WhitelabelUserFixture::class);
        $this->loginService = new SeleniumLoginService($this->driver);
    }

    /** @test */
    public function registration_WithCorrectDate_CanRegisterNewUser(): void
    {
        $this->loginService->registerUserToWhitelabel(true, true, true, true);
        $urlAfterSuccessRegisterRedirect = 'https://lottopark.loc/deposit/';
        $this->assertSame(
            $urlAfterSuccessRegisterRedirect,
            $this->driver->getCurrentURL()
        );
        $this->userService->deleteUser(self::TEST_USER_EMAIL);
    }

    /** @test */
    public function registration_WithoutEmail_CannotRegisterNewUser(): void
    {
        $this->loginService->registerUserToWhitelabel(false, true, true, true);
        $this->assertSame(
            self::REGISTER_URL,
            $this->driver->getCurrentURL()
        );
    }

    /** @test */
    public function registration_WithoutPassword_CannotRegisterNewUser(): void
    {
        $this->loginService->registerUserToWhitelabel(true, false, true, true);
        $this->assertUrlIsCorrect(self::REGISTER_URL);
    }

    /** @test */
    public function registration_WithoutRepeatPassword_CannotRegisterNewUser(): void
    {
        $this->loginService->registerUserToWhitelabel(true, true, false, true);
        $this->assertUrlIsCorrect(self::REGISTER_URL);
    }

    /** @test */
    public function registration_WithoutAcceptTermsAndPolicy_CannotRegisterNewUser(): void
    {
        $this->loginService->registerUserToWhitelabel(true, true, true, false);
        $this->assertUrlIsCorrect(self::REGISTER_URL);
    }

    /** @test */
    public function registration_WithExistingEmail_CannotRegisterNewUser(): void
    {
        $this->fixtureUser->addUser(SeleniumLoginService::TEST_USER_EMAIL, SeleniumLoginService::TEST_USER_PASSWORD);
        $this->loginService->registerUserToWhitelabel(true, true, true, true);
        $this->assertUrlIsCorrect(self::REGISTER_URL);
        $this->elementHasText(self::ALTERT_ERROR_CSS, 'This e-mail is already registered.');
        $this->checkBackgroundColor('rgba(244, 67, 54, 1)', self::ALTERT_ERROR_CSS);
        $this->userService->deleteUser(self::TEST_USER_EMAIL);
    }
}
