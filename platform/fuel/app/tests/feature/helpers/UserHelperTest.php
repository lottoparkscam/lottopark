<?php

namespace Tests\Feature\Helpers;

use Fuel\Core\Session;
use Helpers\UserHelper;
use Models\WhitelabelUser;
use Tests\Feature\AbstractTests\AbstractUserTest;

final class UserHelperTest extends AbstractUserTest
{
    protected WhitelabelUser $whitelabelUser;

    public function tearDown(): void
    {
        parent::tearDown();
    }

    /** @test */
    public function getUserModelShouldReturnNull(): void
    {
        $this->assertNull(UserHelper::getUserModel());
    }

    /** @test */
    public function setShouldLogoutAfterBrowserCloseUserCheckedRememberMeShouldSetFalse(): void
    {
        $this->prepareUserVariables('email', true);
        $this->assertFalse(Session::get(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY));
    }

    /** @test */
    public function setShouldLogoutAfterBrowserCloseUserDidNotCheckRememberMeShouldSetTrue(): void
    {
        $this->prepareUserVariables('email', false);
        $this->assertTrue(Session::get(UserHelper::SHOULD_LOGOUT_AFTER_BROWSER_CLOSE_KEY));
    }
}
