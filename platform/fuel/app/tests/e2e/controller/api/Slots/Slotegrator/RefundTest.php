<?php


use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;

class RefundTest extends AbstractSlotegrator
{
    /** @test */
    public function shouldCorrectRefund()
    {
        // given - we should bet before
        $requestParams = $this->getDefaultRequestParams([
            'transaction_id' => 'test1234'
        ]);

        $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        $this->checkUserBalance(4.97);
        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'type' => 'bet',
            'provider_transaction_id' => 'test1234',
            'is_canceled' => false
        ]);

        $requestParams = $this->getDefaultRequestParams([
            'action' => 'refund',
            'amount' => 80,
            'bet_transaction_id' => 'test1234',
            'type' => null
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

        $this->checkLog([
            'action' => 'refund'
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'type' => 'bet',
            'provider_transaction_id' => 'test1234',
            'is_canceled' => true
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'action' => 'refund',
            'type' => null
        ]);

        $this->checkUserBalance(100.0);
    }

    /** @test */
    public function withNotExistedTransaction_shouldNotIncreaseUserBalance()
    {
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'refund',
            'amount' => 80.0,
            'bet_transaction_id' => 'test1234',
            'type' => null
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

        $this->checkLog([
            'action' => 'refund'
        ]);

        $this->checkTransaction(0.0, 0.0, [
            'amount' => 0,
            'action' => 'refund',
            'type' => null
        ]);

        $this->checkUserBalance(100.0);
    }

    /** @test */
    public function whenTransactionToRefundIsCanceled()
    {
        // given - we should bet before
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'bet',
            'type' => 'bet',
            'transaction_id' => 'test1234',
        ]);

        $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        $requestParams = $this->getDefaultRequestParams([
            'action' => 'refund',
            'amount' => 80,
            'bet_transaction_id' => 'test1234',
            'type' => null
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

        $requestParams = $this->getDefaultRequestParams([
            'action' => 'refund',
            'amount' => 80,
            'bet_transaction_id' => 'test1235',
            'type' => null
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
    }
}
