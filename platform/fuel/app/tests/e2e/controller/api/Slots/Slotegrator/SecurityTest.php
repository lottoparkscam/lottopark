<?php

use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;

class SecurityTest extends AbstractSlotegrator
{
    /** @test */
    public function get_checkIsUnavailable()
    {
        $response = $this->getResponse(
            'GET',
            '/api/slots/slotegrator/'
        );

        $this->assertSame(405, $response['status']);
    }

    /** @test */
    public function postIndex_checkHeaders()
    {
        $this->checkErrors('X-Merchant-Id header does not exist in this request', ['action' => 'wrong'], []);
        $this->checkErrors('X-Timestamp header does not exist in this request', ['action' => 'wrong'], [
            'X-Merchant-Id' => 'some'
        ]);
        $this->checkErrors('X-Nonce header does not exist in this request', ['action' => 'wrong'], [
            'X-Merchant-Id' => 'some',
            'X-Timestamp' => 'some'
        ]);
        $this->checkErrors('X-Sign header does not exist in this request', ['action' => 'wrong'], [
            'X-Merchant-Id' => 'some',
            'X-Timestamp' => 'some',
            'X-Nonce' => 'some'
        ]);
        $this->checkErrors('Invalid merchant_id', ['action' => 'some'], [
            'X-Merchant-Id' => 'some',
            'X-Timestamp' => 'some',
            'X-Nonce' => 'some',
            'X-Sign' => 'some',
        ]);

        $correctHeaders = $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', ['action' => 'some']);
        $this->checkErrors('Invalid sign', ['action' => 'some'], [
            'X-Merchant-Id' => $correctHeaders['X-Merchant-Id'],
            'X-Timestamp' => 123,
            'X-Nonce' => 'some',
            'X-Sign' => 'some',
        ]);

        $correctHeaders = $this->slotegratorSecurityService->prepareAccessHeaders(
            'lottopark',
            ['action' => 'some'],
            time() - 60,
            'some'
        );
        $this->checkErrors('Request expired', ['action' => 'some'], [
            'X-Merchant-Id' => $correctHeaders['X-Merchant-Id'],
            'X-Timestamp' => time() - 60,
            'X-Nonce' => 'some',
            'X-Sign' => $correctHeaders['X-Sign'],
        ]);

        $correctHeaders = $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', ['action' => 'some']);
        $this->checkErrors('No POST data provided', [], $correctHeaders);
    }

    /** @test */
    public function postIndex_checkActionParameter()
    {
        $this->checkErrors(
            "Action key cannot be empty",
            ['test' => 'test'],
            $this->slotegratorSecurityService->prepareAccessHeaders(
                'lottopark',
                ['test' => 'test']
            )
        );
        $requestParams = [
            'action' => 'balance',
            'currency' => 'USD'
        ];
        $this->checkErrors(
            "Player_id key does not exist in request data or value is empty",
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams)
        );
        $requestParams = [
            'action' => 'wrong',
            'player_id' => $this->whitelabelUser->token,
            'currency' => 'USD',
            'session_id' => 1234,
            'game_uuid' => $this->slotGame->uuid
        ];
        $this->checkErrors(
            "We do not support this endpoint yet",
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams)
        );
        $requestParams = [
            'action' => 'balance',
            'player_id' => 'test',
            'currency' => 'USD',
            'session_id' => 1234,
            'game_uuid' => $this->slotGame->uuid
        ];
        $this->checkErrors(
            "User with given player_id does not exist",
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams)
        );
    }

    /** @test */
    public function postIndex_checkWrongIp()
    {
        $this->clearWhitelistIp();

        $requestParams = [
            'action' => 'balance',
            'player_id' => '9999',
            'currency' => 'USD',
            'session_id' => 1234
        ];

        $this->checkErrors('This IP is not allowed', $requestParams, $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams));
    }

    /** @test */
    public function postIndex_checkWrongUser()
    {
        $requestParams = [
            'action' => 'balance',
            'player_id' => '9999',
            'currency' => 'USD',
            'session_id' => 1234,
            'game_uuid' => $this->slotGame->uuid
        ];
        $this->checkErrors(
            'User with given player_id does not exist',
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams)
        );
    }
}
