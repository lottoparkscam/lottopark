<?php


use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;

class WinTest extends AbstractSlotegrator
{
    /** @test */
    public function shouldIncreaseBalance()
    {
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'win',
            'type' => 'win',
            'amount' => 70
        ]);

        $response = $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertSame(154.1841, $body['balance']);
        $this->assertGreaterThan(1000, $body['transaction_id']);

        $this->checkLog([
            'action' => 'win'
        ]);

        $this->checkTransaction(83.16, 70.0, [
            'amount' => 70,
            'type' => 'win'
        ]);

        $this->checkUserBalance(183.16);
    }

    /** @test */
    public function withFreespin_amountShouldBeZero()
    {
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'win',
            'type' => 'freespin',
            'amount' => 70
        ]);

        $response = $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertSame(84.18, $body['balance']);
        $this->assertGreaterThan(1000, $body['transaction_id']);

        $this->checkTransaction(0.0, 0.0, [
            'amount' => 0.0,
            'type' => 'freespin'
        ]);

        $this->checkUserBalance(100.0);
    }
}
