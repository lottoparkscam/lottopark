<?php


use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;

class RollbackTest extends AbstractSlotegrator
{
    /** @test */
    public function shouldCorrectRollback()
    {
        // given - we should add some actions before

        // add bet
        $requestParams = $this->getDefaultRequestParams([
            'transaction_id' => 'test1231'
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
        $betTransaction = $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'type' => 'bet',
            'provider_transaction_id' => 'test1231',
            'is_canceled' => false
        ]);

        // add win
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'win',
            'type' => 'win',
            'amount' => 200,
            'transaction_id' => 'test1232'
        ]);

        $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        $this->checkUserBalance(242.56);
        $winTransaction = $this->checkTransaction(237.59, 200, [
            'amount' => 200,
            'action' => 'win',
            'type' => 'win',
            'provider_transaction_id' => 'test1232',
            'is_canceled' => false
        ]);

        // add refund
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'refund',
            'type' => null,
            'amount' => 80,
            'transaction_id' => 'test1233',
            'bet_transaction_id' => 'test1231'
        ]);

        $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        $this->checkUserBalance(337.59);
        $refundTransaction = $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'action' => 'refund',
            'type' => null,
            'provider_transaction_id' => 'test1233',
            'is_canceled' => false
        ]);


        // when rollback
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'rollback',
            'type' => 'rollback',
            'amount' => 80,
            'transaction_id' => 'test1234',
            'rollback_transactions' => [
                array_merge($betTransaction->to_array(), ['transaction_id' => $betTransaction->providerTransactionId]),
                array_merge($winTransaction->to_array(), ['transaction_id' => $winTransaction->providerTransactionId]),
                array_merge($refundTransaction->to_array(), ['transaction_id' => $refundTransaction->providerTransactionId])
            ]
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
            'action' => 'rollback'
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'action' => 'rollback',
            'type' => 'rollback',
            'provider_transaction_id' => 'test1234',
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'type' => 'bet',
            'provider_transaction_id' => 'test1231',
            'is_canceled' => true
        ]);

        $this->checkTransaction(237.59, 200, [
            'amount' => 200,
            'action' => 'win',
            'type' => 'win',
            'provider_transaction_id' => 'test1232',
            'is_canceled' => true
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'action' => 'refund',
            'type' => null,
            'provider_transaction_id' => 'test1233',
            'is_canceled' => true
        ]);

        $this->checkUserBalance(100.0);
    }

    /** @test */
    public function whenSomeTransactionNotExists()
    {
        // given - we should add some actions before

        // add bet
        $requestParams = $this->getDefaultRequestParams([
            'transaction_id' => 'test1231'
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
        $betTransaction = $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'type' => 'bet',
            'provider_transaction_id' => 'test1231',
            'is_canceled' => false
        ]);

        // add refund
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'refund',
            'type' => null,
            'amount' => 80,
            'transaction_id' => 'test1233',
            'bet_transaction_id' => 'test1231'
        ]);

        $this->getResponse(
            'POST',
            '/api/slots/slotegrator/',
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            [
                'form_params' => $requestParams
            ]
        );

        $this->checkUserBalance(100.0);
        $refundTransaction = $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'action' => 'refund',
            'type' => null,
            'provider_transaction_id' => 'test1233',
            'is_canceled' => false
        ]);


        // when rollback
        $requestParams = $this->getDefaultRequestParams([
            'action' => 'rollback',
            'type' => 'rollback',
            'amount' => 80,
            'transaction_id' => 'test1234',
            'rollback_transactions' => [
                array_merge($betTransaction->to_array(), ['transaction_id' => $betTransaction->providerTransactionId]),
                [
                    'action' => 'win',
                    'type' => 'win',
                    'currency' => 'EUR',
                    'amount' => 200,
                    'transaction_id' => 'test1232'
                ],
                array_merge($refundTransaction->to_array(), ['transaction_id' => $refundTransaction->providerTransactionId])
            ]
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
            'action' => 'rollback'
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'action' => 'rollback',
            'type' => 'rollback',
            'provider_transaction_id' => 'test1234',
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'type' => 'bet',
            'provider_transaction_id' => 'test1231',
            'is_canceled' => true
        ]);

        $this->checkTransaction(95.03, 80, [
            'amount' => 80,
            'action' => 'refund',
            'type' => null,
            'provider_transaction_id' => 'test1233',
            'is_canceled' => true
        ]);

        $this->checkUserBalance(100.0);
    }
}
