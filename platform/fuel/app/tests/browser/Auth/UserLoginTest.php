<?php

namespace Tests\Browser\Auth;

use Container;
use Test\Selenium;
use Test\Selenium\SeleniumUserService;
use Tests\Fixtures\WhitelabelUserFixture;
use Test\Selenium\Interfaces\UserInterface;
use Test\Selenium\Login\SeleniumLoginService;

final class UserLoginTest extends Selenium implements UserInterface
{
    private SeleniumLoginService $loginService;
    private SeleniumUserService $userService;
    private WhitelabelUserFixture $fixtureUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureUser = Container::get(WhitelabelUserFixture::class);
        $this->userService = new SeleniumUserService($this->driver);
        $this->fixtureUser->addUser(self::TEST_USER_EMAIL, self::TEST_USER_PASSWORD);
        $this->driver->get(self::LOGIN_URL);
        $this->loginService = new SeleniumLoginService($this->driver);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->userService->deleteUser(self::TEST_USER_EMAIL);
    }

    /** @test */
    public function loginUser_Success(): void
    {
        $this->loginService->loginUser();
        $this->assertStringContainsString(
            self::HOMEPAGE,
            $this->driver->getCurrentURL()
        );
    }

    /** @test */
    public function loginUser_WithWrongPassword_Failure(): void
    {
        $this->loginService->loginUserWithWrongPassword();
        $this->assertStringContainsString(
            self::LOGIN_URL,
            $this->driver->getCurrentURL()
        );
    }

    /** @test */
    public function loginUser_WithWrongEmail_Failure(): void
    {
        $this->loginService->loginUserWithWrongEmail();
        $this->assertStringContainsString(
            self::LOGIN_URL,
            $this->driver->getCurrentURL()
        );
    }
}
