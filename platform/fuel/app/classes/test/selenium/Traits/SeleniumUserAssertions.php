<?php

namespace Test\Selenium\Traits;

use Models\WhitelabelUser;

trait SeleniumUserAssertions
{
    use SeleniumWrappersTrait;
    protected WhitelabelUser $whitelabelUser;
    private string $resendLink;

    private function assertValidResendLink(string $actualResendLink): void
    {
        // we need to refresh whitelabelUser to get resend_hash
        $this->whitelabelUser = $this->whitelabelUser->find($this->whitelabelUser->id);

        $expected = <<<EXPECTED
        https://lottopark.loc/resend/{$this->whitelabelUser->token}/{$this->whitelabelUser->resend_hash}
        EXPECTED;
        $this->assertSame($expected, $actualResendLink);
        $this->resendLink = $actualResendLink;
    }

    private function assertUserIsLoggedIn(): void
    {
        $isUserLoggedIn = $this->findById('btn-login')->isDisplayed();
        $this->assertFalse($isUserLoggedIn);
    }

    private function assertUserIsNotLoggedIn(): void
    {
        $isUserNotLoggedIn = $this->findById('btn-login')->isDisplayed();
        $this->assertTrue($isUserNotLoggedIn);
    }

    private function assertFlashMessage(string $expected): void
    {
        $this->assertSame($expected, $this->getFlashMessage());
    }

    /** 
     * @param string[] $expected eg. 
     * [
     *  'type' = 'error', 'message' = 'mess'
     * ] */
    private function assertFlashMessages(array $expected): void
    {
        $this->assertSame($expected, $this->getFlashMessages());
    }

    private function assertRedirect(string $expected): void
    {
        $this->assertSame(
            $expected,
            $this->driver->getCurrentURL()
        );
    }

    /** IMPORTANT: You have to set $this->resendLink */
    private function assertResendSuccess(): void
    {
        $this->driver->get($this->resendLink);
        $this->assertRedirect('https://lottopark.loc/');

        $this->assertFlashMessage('The activation e-mail has been resent to you.');
    }

    /** IMPORTANT: You have to set $this->resendLink */
    private function assertResendFailWrongLink(): void
    {
        $this->driver->get($this->resendLink);
        $this->assertRedirect('https://lottopark.loc/');

        $this->assertFlashMessage('Incorrect resend link. Please contact us for manual activation.');
    }

    private function assertResendFailOncePer24Hours(): void
    {
        $this->driver->get($this->resendLink);
        $this->assertRedirect('https://lottopark.loc/');

        $this->assertFlashMessage('You can resend the activation link once per 24 hours. Please try again later or contact us.');
    }
}
