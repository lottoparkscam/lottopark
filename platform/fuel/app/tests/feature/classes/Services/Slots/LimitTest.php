<?php

use Fuel\Core\DB;
use Wrappers\Orm;
use Models\Whitelabel;
use Models\SlotProvider;
use Models\SlotTransaction;
use Models\WhitelabelSlotProvider;
use Services\Api\Slots\LimitService;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class LimitTest extends Test_Feature
{
    private LimitService $limitService;
    private Whitelabel $whitelabel;
    private WhitelabelSlotProvider $whitelabelSlotProvider;
    private SlotProvider $slotProvider;

    public function setUp(): void
    {
        parent::setUp();
        $_SERVER['HTTP_HOST'] = 'lottopark.loc';
        Orm::disableCaching();
        $this->limitService = Container::get(LimitService::class);
        $this->whitelabel = Whitelabel::find(1);
        $this->slotProvider = SlotProvider::find('first', [
            'where' => [
                'slug' => 'slotegrator'
            ]
        ]);

        $this->whitelabelSlotProvider = new WhitelabelSlotProvider([
            'slot_provider_id' => $this->slotProvider->id,
            'whitelabel_id' => $this->whitelabel->id,
            'is_enabled' => true,
            'max_monthly_money_around_usd' => 100,
        ]);
        $this->whitelabelSlotProvider->save();
    }

    public function tearDown(): void
    {
        parent::tearDown();
        $this->whitelabelSlotProvider->delete();
    }

    /**
     * @test
     * @dataProvider isWhitelabelLimitReached_dataProvider
     */
    public function isWhitelabelLimitReached_shouldReturnLimitIsNotReached(bool $shouldBeLimitEnabled): void
    {
        $this->whitelabelSlotProvider->isLimitEnabled = $shouldBeLimitEnabled;
        $this->whitelabelSlotProvider->save();

        $whitelabelId = $this->whitelabel->id;

        $isLimitDisabled = $this->whitelabelSlotProvider->isLimitEnabled;
        if ($isLimitDisabled) {
            $isLimitReached = false;
        } else {
            $isLimitReached = $this->limitService->isWhitelabelLimitReached($whitelabelId);
        }
        $this->assertFalse($isLimitReached);
    }

    /**
     * @test
     * @dataProvider isWhitelabelLimitReached_dataProvider
     */
    public function isWhitelabelLimitReached_shouldReturnLimitIsReached(bool $shouldBeLimitEnabled): void
    {
        $this->whitelabelSlotProvider->isLimitEnabled = $shouldBeLimitEnabled;
        $this->whitelabelSlotProvider->save();

        $whitelabelId = $this->whitelabel->id;
        $whitelabelSlotProviderId = $this->whitelabelSlotProvider->id;
        $someRandomJsonWithAdditionalData = json_encode(['asd' => 123]);
        $token = 123123123;
        $token2 = 1231231234;
        $providerTransactionId = 123123123;
        $providerTransactionId2 = 1231231234;

        DB::query('SET foreign_key_checks = 0')->execute();
        $slotTransaction = new SlotTransaction();
        $slotTransaction->whitelabelSlotProviderId = $whitelabelSlotProviderId;
        $slotTransaction->whitelabelUserId = 1;
        $slotTransaction->slotGameId = 1;
        $slotTransaction->currencyId = 1;
        $slotTransaction->token = $token;
        $slotTransaction->isCanceled = false;
        $slotTransaction->amount = 50;
        $slotTransaction->amountUsd = 25000;
        $slotTransaction->amountManager = 123;
        $slotTransaction->type = 'bet';
        $slotTransaction->action = 'bet';
        $slotTransaction->providerTransactionId = $providerTransactionId;
        $slotTransaction->additionalData = $someRandomJsonWithAdditionalData;
        $slotTransaction->createdAt = date('Y-m-d H:i:s');
        $slotTransaction->updatedAt = date('Y-m-d H:i:s');
        $slotTransaction->save();

        $slotTransaction = new SlotTransaction();
        $slotTransaction->whitelabelSlotProviderId = $whitelabelSlotProviderId;
        $slotTransaction->whitelabelUserId = 1;
        $slotTransaction->slotGameId = 1;
        $slotTransaction->currencyId = 1;
        $slotTransaction->token = $token2;
        $slotTransaction->isCanceled = false;
        $slotTransaction->amount = 50;
        $slotTransaction->amountUsd = 25000;
        $slotTransaction->amountManager = 123;
        $slotTransaction->type = 'bet';
        $slotTransaction->action = 'bet';
        $slotTransaction->providerTransactionId = $providerTransactionId2;
        $slotTransaction->additionalData = $someRandomJsonWithAdditionalData;
        $slotTransaction->createdAt = date('Y-m-d H:i:s');
        $slotTransaction->updatedAt = date('Y-m-d H:i:s');
        $slotTransaction->save();
        DB::query('SET foreign_key_checks = 1')->execute();
        $isLimitReached = $this->limitService->isWhitelabelLimitReached($whitelabelId);
        $isLimitEnabled = $this->whitelabelSlotProvider->isLimitEnabled;

        if ($isLimitEnabled) {
            $this->assertTrue($isLimitReached);
        } else {
            $this->assertFalse($isLimitReached);
        }
    }

    public function isWhitelabelLimitReached_dataProvider(): array
    {
        return [[true, false]];
    }
}
