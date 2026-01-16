<?php

namespace Tests\Browser\Crm;

use Test\Selenium\Login\SeleniumLoginService;
use Test\Selenium;

final class CRMLoginTest extends Selenium
{
    private SeleniumLoginService $loginService;

    public function setUp(): void
    {
        parent::setUp();
        $this->loginService = new SeleniumLoginService($this->driver);
    }

    /** @test */
    public function crmLoginUser_Success(): void
    {
        $this->loginService->loginCrmSuperadmin();
        $urlAfterSuccessfulLoginRedirect = 'https://admin.whitelotto.loc/';

        $this->assertStringContainsString(
            $urlAfterSuccessfulLoginRedirect,
            $this->driver->getCurrentURL()
        );
    }
}
