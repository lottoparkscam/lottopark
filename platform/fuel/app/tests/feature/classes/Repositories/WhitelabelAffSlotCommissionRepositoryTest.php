<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Fuel\Core\DB;
use Helpers_Time;
use Models\SlotTransaction;
use Models\Whitelabel;
use Models\WhitelabelAff;
use Models\WhitelabelAffCasinoGroup;
use Models\WhitelabelUser;
use Models\WhitelabelUserAff;
use Repositories\WhitelabelAffSlotCommissionRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelUserFixture;

final class WhitelabelAffSlotCommissionRepositoryTest extends Test_Feature
{
    private WhitelabelAffSlotCommissionRepository $whitelabelAffSlotCommissionRepository;
    private Whitelabel $whitelabel;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private string $now;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelAffSlotCommissionRepository = $this->container->get(WhitelabelAffSlotCommissionRepository::class);
        $this->whitelabel = $this->container->get('whitelabel');
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);

        Carbon::setTestNow('2022-01-22 12:00:00');
        $this->now = Carbon::now()->format(Helpers_Time::DATE_FORMAT);

        $this->whitelabel->defaultCasinoCommissionPercentageValueForTier1 = 50.0;
        $this->whitelabel->defaultCasinoCommissionPercentageValueForTier2 = 10.0;
        $this->whitelabel->save();
    }

    private function createWhitelabelAff(
        ?int $whitelabelAffCasinoGroupId = null,
        ?int $whitelabelAffParentId = null
    ): WhitelabelAff {
        $aff = new WhitelabelAff();
        $aff->whitelabelId = $this->whitelabel->id;
        $aff->languageId = $this->whitelabel->languageId;
        $aff->currencyId = $this->whitelabel->managerSiteCurrencyId;

        if (!is_null($whitelabelAffCasinoGroupId)) {
            $aff->whitelabelAffCasinoGroupId = $whitelabelAffCasinoGroupId;
        }

        if (!is_null($whitelabelAffParentId)) {
            $aff->whitelabelAffParentId = $whitelabelAffParentId;
        }

        $aff->isAccepted = 1;
        $aff->isConfirmed = 1;
        $aff->isActive = 1;
        $aff->login = 'asd';
        $aff->email = 'asd@gg.int';
        $aff->isDeleted = 0;
        $aff->token = 123123;
        $aff->subAffiliateToken = rand(10000, 90000);
        $aff->hash = '123';
        $aff->salt = '123';
        $aff->address_1 = '123';
        $aff->address_2 = '123';
        $aff->city = 'asd';
        $aff->country = 'PL';
        $aff->state = 'PL';
        $aff->zip = '00-222';
        $aff->phoneCountry = 'PL';
        $aff->phone = '123123';
        $aff->timezone = '';
        $aff->affLeadLifetime = 0;
        $aff->dateCreated = Carbon::now();
        $aff->isShowName = 0;
        $aff->hideLeadId = 0;
        $aff->hideTransactionId = 0;
        $aff->save();

        return $aff;
    }

    private function createWhitelabelUser(): WhitelabelUser
    {
        $this->whitelabelUserFixture->addRandomUser(100, 0);
        $this->whitelabelUserFixture->user->is_active = 1;
        $this->whitelabelUserFixture->user->is_deleted = 0;
        $this->whitelabelUserFixture->user->is_confirmed = 0;
        $this->whitelabelUserFixture->user->casino_bonus_balance = 0;
        $this->whitelabelUserFixture->user->delete();
        $user = $this->whitelabelUserFixture->user->to_array();
        $user = new WhitelabelUser($user);
        $user->save();
        $user->flush_cache();

        return $user;
    }

    private function createWhitelabelAffCasinoGroup(): WhitelabelAffCasinoGroup
    {
        $casinoGroup = new WhitelabelAffCasinoGroup();

        $casinoGroup->name = 'Testing casino';
        $casinoGroup->whitelabelId = $this->whitelabel->id;
        $casinoGroup->commissionPercentageValueForTier1 = 25.0;
        $casinoGroup->commissionPercentageValueForTier2 = 5.0;

        $casinoGroup->save();

        return $casinoGroup;
    }

    private function createWhitelabelAffUser(WhitelabelAff $whitelabelAff, WhitelabelUser $whitelabelUser): WhitelabelUserAff
    {
        $affUser = new WhitelabelUserAff();
        $affUser->whitelabelId = $this->whitelabel->id;
        $affUser->whitelabelAffId = $whitelabelAff->id;
        $affUser->whitelabelUserId = $whitelabelUser->id;
        $affUser->isAccepted = 1;
        $affUser->isDeleted = 0;
        $affUser->isExpired = 0;
        $affUser->isCasino = true;

        $affUser->save();

        return $affUser;
    }

    private function createSlotTransaction(WhitelabelUser $whitelabelUser, string $action): SlotTransaction
    {
        DB::query("SET foreign_key_checks = 0;")->execute(); // i didn't want to create xxxx relations for those tests

        $slotTransaction = new SlotTransaction();
        $slotTransaction->slotGameId = 1;
        $slotTransaction->slotOpenGameId = 1;
        $slotTransaction->currencyId = 1; //usd
        $slotTransaction->whitelabelUserId = $whitelabelUser->id;
        $slotTransaction->whitelabelSlotProviderId = 1;
        $slotTransaction->providerTransactionId = rand(1000000, 9999999);
        $slotTransaction->amount = 10;
        $slotTransaction->amountManager = 10;
        $slotTransaction->amountUsd = 10;
        $slotTransaction->createdAt = Carbon::now();
        $slotTransaction->token = rand(1000000, 9999999);
        $slotTransaction->action = $action;
        $slotTransaction->type = $action;
        $slotTransaction->additionalData = json_encode(['x']);
        $slotTransaction->save();
        DB::query("SET foreign_key_checks = 1;")->execute();

        return $slotTransaction;
    }

    /** @test */
    public function getCasinoCommissions_tier1_withoutGroup_positiveGgr_shouldCalculateCommissionUsdProperly(): void
    {
        $expectedCount = 1;

        $aff = $this->createWhitelabelAff();
        $whitelabelUser = $this->createWhitelabelUser();
        $this->createWhitelabelAffUser($aff, $whitelabelUser);
        $this->createSlotTransaction($whitelabelUser, 'bet');

        $actual = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissions(1, $this->now);

        $this->assertCount($expectedCount, $actual);

        $commission = $actual[0];
        $this->assertValidCommission(
            $commission,
            10.00,
            0.0,
            10.00,
            10.00,
            4.25
        );
    }

    /**
     * @test
     * We store negative commissions in db but it won't be shown for aff user
     */
    public function getCasinoCommissions_tier1_withoutGroup_negativeGgr_shouldReturnNegativeCommissionUsd(): void
    {
        $expectedCount = 1;

        $aff = $this->createWhitelabelAff();
        $whitelabelUser = $this->createWhitelabelUser();

        $this->createWhitelabelAffUser($aff, $whitelabelUser);
        $this->createSlotTransaction($whitelabelUser, 'bet');
        $this->createSlotTransaction($whitelabelUser, 'win');
        $this->createSlotTransaction($whitelabelUser, 'win');

        $actual = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissions(1, $this->now);

        $this->assertCount($expectedCount, $actual);

        $commission = $actual[0];
        $this->assertValidCommission(
            $commission,
            10.00,
            20.0,
            -10,
            -10.00,
            -4.25
        );
    }

    /** @test */
    public function getCasinoCommissions_tier1_withGroup_positiveGgr_shouldReturnCommissionUsdBasedOnGroupCommission(): void
    {
        $expectedCount = 1;

        $casinoGroup = $this->createWhitelabelAffCasinoGroup();

        $aff = $this->createWhitelabelAff($casinoGroup->id);
        $whitelabelUser = $this->createWhitelabelUser();
        $this->createWhitelabelAffUser($aff, $whitelabelUser);
        $this->createSlotTransaction($whitelabelUser, 'bet');

        $actual = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissions(1, $this->now);

        $this->assertCount($expectedCount, $actual);

        $commission = $actual[0];
        $this->assertValidCommission(
            $commission,
            10.00,
            0.0,
            10,
            10.00,
            2.12 // ggr 10, group commission 25%, 6% taxes
        );
    }

    /** @test */
    public function getCasinoCommissions_tier1_withGroup_positiveGgr_userHasBonusBalance_shouldReturnCommissionUsdBasedOnGroupCommission(): void
    {
        $expectedCount = 1;

        $casinoGroup = $this->createWhitelabelAffCasinoGroup();

        $aff = $this->createWhitelabelAff($casinoGroup->id);
        $whitelabelUser = $this->createWhitelabelUser();
        $whitelabelUser->casinoBonusBalance = 20;
        $whitelabelUser->save();

        $this->createWhitelabelAffUser($aff, $whitelabelUser);
        $this->createSlotTransaction($whitelabelUser, 'bet');

        $actual = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissions(1, $this->now);

        $this->assertCount($expectedCount, $actual);

        $commission = $actual[0];
        $this->assertValidCommission(
            $commission,
            10.00,
            0.0,
            10,
            -10.00,
            -2.12
        );
    }

    /** @test */
    public function getCasinoCommissions_tier2_withoutGroup_affHasNotAnyParent_ShouldReturnEmpty(): void
    {
        $expectedCount = 0;

        $aff = $this->createWhitelabelAff();
        $whitelabelUser = $this->createWhitelabelUser();
        $this->createWhitelabelAffUser($aff, $whitelabelUser);
        $this->createSlotTransaction($whitelabelUser, 'bet');

        $actual = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissions(2, $this->now);

        $this->assertCount($expectedCount, $actual);
    }

    /** @test */
    public function getCasinoCommissions_tier2_withoutGroup_positiveGgr_shouldCalculateCommissionUsdForParentProperly(): void
    {
        $expectedCount = 1;

        $aff = $this->createWhitelabelAff();
        $subAff = $this->createWhitelabelAff(null, $aff->id);

        $expectedWhitelabelAffId = $aff->id;
        $whitelabelUser = $this->createWhitelabelUser();
        $this->createWhitelabelAffUser($subAff, $whitelabelUser);
        $this->createSlotTransaction($whitelabelUser, 'bet');

        $actual = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissions(2, $this->now);

        $this->assertCount($expectedCount, $actual);
        $commission = $actual[0];

        // check if commission was added for aff parent
        $this->assertSame((int)$expectedWhitelabelAffId, (int)$commission['whitelabel_aff_id']);

        $this->assertValidCommission(
            $commission,
            10.00,
            0.0,
            10.00,
            10.00,
            0.85
        );
    }

    /** @test */
    public function getCasinoCommissions_tier2_withGroup_positiveGgr_shouldCalculateCommissionUsdForParentProperly(): void
    {
        $expectedCount = 1;

        $casinoGroup = $this->createWhitelabelAffCasinoGroup();
        $aff = $this->createWhitelabelAff($casinoGroup->id);
        $subAff = $this->createWhitelabelAff(null, $aff->id);
        $expectedWhitelabelAffId = $aff->id;
        $whitelabelUser = $this->createWhitelabelUser();
        $this->createWhitelabelAffUser($subAff, $whitelabelUser);
        $this->createSlotTransaction($whitelabelUser, 'bet');

        $actual = $this->whitelabelAffSlotCommissionRepository->getCasinoCommissions(2, $this->now);

        $this->assertCount($expectedCount, $actual);
        $commission = $actual[0];

        // check if commission was added for aff parent
        $this->assertSame((int)$expectedWhitelabelAffId, (int)$commission['whitelabel_aff_id']);
        $this->assertValidCommission(
            $commission,
            10.00,
            0.0,
            10.00,
            10.00,
            0.43
        );
    }

    private function assertValidCommission(
        array $commission,
        float $expectedSumOfBets,
        float $expectedSumOfWins,
        float $expectedGgr,
        float $expectedGgrWithoutCasinoBonusBalance,
        float $expectedNgrCommission
    ): void {
        $this->assertSame($expectedSumOfBets, (float)$commission['sum_of_bets']);
        $this->assertSame($expectedSumOfWins, (float)$commission['sum_of_wins']);
        $this->assertSame($expectedGgr, (float)$commission['ggr']);
        $this->assertSame($expectedGgrWithoutCasinoBonusBalance, (float)$commission['ggr_without_casino_bonus_balance']);
        $this->assertSame($expectedNgrCommission, (float)$commission['ngr_commission']);
    }
}
