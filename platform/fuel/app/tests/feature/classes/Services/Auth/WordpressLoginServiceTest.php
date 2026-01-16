<?php

namespace Tests\Feature\Classes\Services\Auth;

use Fuel\Core\DB;
use Fuel\Core\Session;
use Helpers\UserHelper;
use Models\WhitelabelUser;
use Tests\Feature\AbstractTests\AbstractUserTest;
use Services\Auth\WordpressLoginService;

/**
 * @runTestsInSeparateProcesses to ensure database entries are cleared between each test.
 * @preserveGlobalState disabled
*/
final class WordpressLoginServiceTest extends AbstractUserTest
{
    protected WhitelabelUser $whitelabelUser;
    private WordpressLoginService $wordpressLoginService;

    public function setUp(): void
    {
        parent::setUp();
        $this->wordpressLoginService = $this->container->get(WordpressLoginService::class);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        DB::query('TRUNCATE ip_login_try;')->execute();
        $this->whitelabelUser->delete();
    }

    /** @test */
    public function loginByLoginWithValidCredentialsShouldReturnOkAndSaveUserSession(): void
    {
        $this->prepareUserVariables('login');
        $result = $this->wordpressLoginService->login(false);

        $this->assertFalse(UserHelper::isUserSessionIncorrect());
        $this->assertSame($result, true);
    }

    /** @test */
    public function loginByEmailWithValidCredentialsShouldReturnOkAndSaveUserSession(): void
    {
        $this->prepareUserVariables();

        $result = $this->wordpressLoginService->login(false);

        $this->assertFalse(UserHelper::isUserSessionIncorrect());
        $this->assertSame($result, true);
    }

    /** @test */
    public function loginCheckSecurityisHoneypotIncorrectShouldReturnError(): void
    {
        $this->prepareUserVariables();

        $this->setInput('POST', ['currency_a' => 'USD']);
        $result = $this->wordpressLoginService->login(false);

        $this->assertSame($result, false);
        $this->assertSame(['error', WordpressLoginService::MESSAGES['securityError']], Session::get_flash('message'));
    }

    /** @test */
    public function loginCheckSecurityInvalidCsrfTokenShouldReturnError(): void
    {
        $this->prepareUserVariables();
        $this->setInput('POST', ['lotto_csrf_token_2' => '']);
        $result = $this->wordpressLoginService->login(false);

        $this->assertSame($result, false);
        $this->assertSame(['error', WordpressLoginService::MESSAGES['securityError']], Session::get_flash('message'));
    }

    /** @test */
    public function loginCheckSecurityInvalidCaptchaShouldReturnError(): void
    {
        $this->resetInput();
        $this->prepareUserVariables();

        $this->setInput('POST', ['g-recaptcha-response' => '']);
        $result = $this->wordpressLoginService->login();

        $this->assertSame($result, false);
        $this->assertSame(['error', WordpressLoginService::MESSAGES['wrongCaptcha']], Session::get_flash('message'));
    }

    /** @test */
    public function loginCheckSecurityFailedLoginLimitReachedShouldReturnError(): void
    {
        $this->resetInput();
        $this->prepareUserVariables();
        $this->setAuthInput('email', 'asdqwe');

        $result = $this->wordpressLoginService->login(false);

        // We should check it only if we receive wrong credentials
        $this->assertSame($result, false);
        $this->assertSame(['error', WordpressLoginService::MESSAGES['wrongLoginCredentials']], Session::get_flash('message'));

        $result = $this->wordpressLoginService->login(false);
        $result = $this->wordpressLoginService->login(false);
        $result = $this->wordpressLoginService->login(false);
        $result = $this->wordpressLoginService->login(false);
        $result = $this->wordpressLoginService->login(false);

        // check after x fail logins we thrown login attempts limit reached
        $this->assertSame($result, false);
        $this->assertSame(['error', WordpressLoginService::MESSAGES['loginAttemptsReached']], Session::get_flash('message'));
    }
}
