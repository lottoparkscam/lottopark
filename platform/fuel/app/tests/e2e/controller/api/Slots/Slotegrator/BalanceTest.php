<?php


use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;

class BalanceTest extends AbstractSlotegrator
{
    /** @test */
    public function shouldReturnCorrectBalance()
    {
        $requestParams = [
            'action' => 'balance',
            'player_id' => $this->whitelabelUser->token,
            'currency' => 'EUR',
            'session_id' => 1234,
            'game_uuid' => 'abcd'
        ];
        $response = [
            'balance' => 84.18
        ];
        $headers = $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams);
        $this->checkSuccess($response, $requestParams, $headers);
        $this->checkLog();
    }
}
