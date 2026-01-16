<?php

namespace Tests\Unit\Classes\Helpers;

use Fuel\Core\Session;
use Helpers\UserHelper;
use Test_Unit;

final class UserHelperTest extends Test_Unit
{
    public function tearDown(): void
    {
        parent::tearDown();
        Session::delete('is_user');
    }

    /** @test */
    public function isUserLogged_userIsNotLogged(): void
    {
        $this->assertFalse(UserHelper::isUserLogged());
    }

    /** @test */
    public function isUserLogged(): void
    {
        Session::set('is_user', true);
        $this->assertTrue(UserHelper::isUserLogged());
    }

    /** @test */
    public function isUserNotLogged(): void
    {
        $this->assertTrue(UserHelper::isUserNotLogged());
    }

    /** @test */
    public function isUserNotLogged_userIsLogged(): void
    {
        Session::set('is_user', true);
        $this->assertFalse(UserHelper::isUserNotLogged());
    }
}
