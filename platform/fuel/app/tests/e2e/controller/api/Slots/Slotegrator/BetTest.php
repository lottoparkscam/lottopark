<?php


use Models\Whitelabel;
use Test\E2e\Controller\Api\Slots\AbstractSlotegrator;

class BetTest extends AbstractSlotegrator
{
    /** @test */
    public function withDisabledWhitelabelSlotProvider_shouldNotBet()
    {
        $requestParams = $this->getDefaultRequestParams();
        $this->whitelabelSlotProvider->isEnabled = false;
        $this->whitelabelSlotProvider->save();

        $this->checkErrors(
            'Casino for this whitelabel is disabled',
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams)
        );
    }

    /** @test */
    public function withDisabledUser_shouldNotBet()
    {
        $requestParams = $this->getDefaultRequestParams();
        $this->whitelabelUser->isDeleted = true;
        $this->whitelabelUser->save();

        $this->checkErrors(
            'This user is disabled',
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams)
        );
    }

    /** @test */
    public function withSufficientBalance_shouldBet()
    {
        $requestParams = $this->getDefaultRequestParams();

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
        $this->assertSame(4.1837, $body['balance']);
        $this->assertGreaterThan(1000, $body['transaction_id']);

        $this->checkLog([
            'action' => 'bet'
        ]);

        $this->checkTransaction(95.03, 80.0, [
            'type' => 'bet'
        ]);
        $this->checkUserBalance(4.97);
    }

    /** @test */
    public function withFreespin_amountShouldBeZero()
    {
        $requestParams = $this->getDefaultRequestParams([
            'type' => 'freespin'
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

    /** @test */
    public function withInsufficientBalance_shouldNotBet()
    {
        $requestParams = $this->getDefaultRequestParams();
        $this->whitelabelUser->casinoBalance = 10;
        $this->whitelabelUser->save();

        $this->checkErrors(
            'Error while debit user',
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams),
            Controller_Api_Slots_Slotegrator::INSUFFICIENT_FUNDS_ERROR_CODE
        );

        $this->checkUserBalance(10.0);
    }

    /** @test */
    public function withInsufficientWhitelabelLimit_shouldNotBet()
    {
        $requestParams = $this->getDefaultRequestParams();
        $this->whitelabel->type = Whitelabel::TYPE_V2;
        $this->whitelabel->save();
        $this->whitelabelSlotProvider->maxMonthlyMoneyAroundUsd = 10;
        $this->whitelabelSlotProvider->isLimitEnabled = true;
        $this->whitelabelSlotProvider->save();

        $this->checkErrors(
            'Insufficient whitelabel limit to do bet transaction',
            $requestParams,
            $this->slotegratorSecurityService->prepareAccessHeaders('lottopark', $requestParams)
        );

        $this->checkUserBalance(100.0);

        $this->whitelabel->type = Whitelabel::TYPE_V1;
        $this->whitelabel->save();
    }
}
