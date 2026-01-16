<?php

namespace Tests\E2e\Controller\Api\Internal;

use Test_E2e_Controller_Api;

class FlashMessageTest extends Test_E2e_Controller_Api
{
    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function postAll_withActivationMessage(): void
    {
        $this->whitelabelUser->isConfirmed = false;
        $this->whitelabelUser->save();

        $response = $this->getResponse(
            'POST',
            '/api/internal/flashMessage/all',
            $this->prepareAndGetLoggedUserHeaders(),
            [
                'form_params' => [
                    'resendLink' => 'link/',
                    'activationText' => 'Example text'
                ]
            ]
        );

        $body = $response['body'];
        $this->assertArrayHasKey('flashMessages', $body);

        $flashMessages = <<<HTML
<div class="platform-alert platform-alert-front platform-alert-info">
            <div class="main-width">
                <span class="fa fa-exclamation-circle"></span> Example text
            </div>
        </div>
HTML;

        $this->assertSame($flashMessages, $body['flashMessages']);
    }

    /**
     * @test
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function postAll_withOwnMessage(): void
    {
        $this->whitelabelUser->isConfirmed = false;
        $this->whitelabelUser->save();

        $additionalSessionData = [
            'message' => ['error', 'My own message']
        ];

        $response = $this->getResponse(
            'POST',
            '/api/internal/flashMessage/all',
            $this->prepareAndGetLoggedUserHeaders($additionalSessionData),
            [
                'form_params' => [
                    'resendLink' => 'link/',
                    'activationText' => 'Example text'
                ]
            ]
        );

        $body = $response['body'];
        $this->assertArrayHasKey('flashMessages', $body);

        $flashMessages = <<<HTML
<div class="platform-alert platform-alert-front platform-alert-error">
            <div class="main-width">
                <span class="fa fa-exclamation-circle"></span> My own message
            </div>
        </div>
HTML;

        $this->assertSame($flashMessages, $body['flashMessages']);
    }
}