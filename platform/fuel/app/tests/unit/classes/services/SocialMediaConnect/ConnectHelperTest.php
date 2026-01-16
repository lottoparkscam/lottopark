<?php

namespace Tests\Unit\Classes\Services\SocialMediaConnect;

use Fuel\Core\Session;
use Helpers\SocialMediaConnect\ConnectHelper;
use Test_Unit;

class ConnectHelperTest extends Test_Unit
{
    public function tearDown(): void
    {
        parent::tearDown();
        Session::delete(ConnectHelper::SOCIAL_CONNECT_KEY);
    }

    /** @test */
    public function markRegisterAsSocialConnection(): void
    {
        $_SERVER['REQUEST_URI'] = 'auth/signup/last-steps';
        ConnectHelper::markRegisterAsSocialConnection();
        $this->assertTrue(Session::get(ConnectHelper::SOCIAL_CONNECT_KEY));
    }

    /** @test */
    public function markRegisterAsSocialConnection_isNotSocialRegister(): void
    {
        $_SERVER['REQUEST_URI'] = 'auth/signup/last';
        ConnectHelper::markRegisterAsSocialConnection();
        $this->assertFalse(Session::get(ConnectHelper::SOCIAL_CONNECT_KEY));
    }

    /** @test */
    public function isSocialConnection(): void
    {
        Session::set(ConnectHelper::SOCIAL_CONNECT_KEY, true);

        $this->assertTrue(ConnectHelper::isSocialConnection());
    }

    /** @test */
    public function isNotSocialConnection(): void
    {
        $this->assertFalse(ConnectHelper::isSocialConnection());
    }
}
