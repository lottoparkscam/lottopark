<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Fuel\Core\Cache;
use Fuel\Core\DB;
use Models\SlotTransaction;
use Models\WhitelabelSlotProvider;
use Models\WhitelabelUser;
use Repositories\SlotTransactionRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelUserFixture;
use Modules\Mediacle\Models\SalesDataSlotTransactionModelAdapter;

final class SlotTransactionRepositoryTest extends Test_Feature
{
    protected $in_transaction = false;
    private SlotTransactionRepository $slotTransactionRepository;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelUser $whitelabelUser;
    private WhitelabelSlotProvider $whitelabelSlotProvider;

    public function setUp(): void
    {
        parent::setUp();
        $this->slotTransactionRepository = $this->container->get(SlotTransactionRepository::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->whitelabelAffSlotCommissionRepositoryTest = $this->container->get(WhitelabelAffSlotCommissionRepositoryTest::class);
        Cache::delete_all();

        DB::query('TRUNCATE slot_transaction;')->execute();

        $this->whitelabelUser = $this->createWhitelabelUser();
        $this->whitelabelSlotProvider = $this->createWhitelabelSlotProvider($this->whitelabelUser->whitelabel_id);
    }

    public function tearDown(): void
    {
        parent::tearDown();
        DB::query('TRUNCATE slot_transaction;')->execute();
        $this->whitelabelUser->delete();
        $this->whitelabelSlotProvider->delete();
    }

    public function createWhitelabelUser(): WhitelabelUser
    {
        $this->whitelabelUserFixture->addRandomUser(100, 0);
        $this->whitelabelUserFixture->user->isActive = 1;
        $this->whitelabelUserFixture->user->isDeleted = 0;
        $this->whitelabelUserFixture->user->casino_bonus_balance = 0;

        $user = $this->whitelabelUserFixture->user->to_array();
        $user = new WhitelabelUser($user, false);
        $user->save();

        return $user;
    }

    public function createWhitelabelSlotProvider(int $whitelabelId): WhitelabelSlotProvider
    {
        $this->whitelabelSlotProvider = new WhitelabelSlotProvider();
        $this->whitelabelSlotProvider->slotProviderId = 1;
        $this->whitelabelSlotProvider->whitelabelId = $whitelabelId;
        $this->whitelabelSlotProvider->isEnabled = true;
        $this->whitelabelSlotProvider->isLimitEnabled = true;
        $this->whitelabelSlotProvider->maxMonthlyMoneyAroundUsd = 50000;
        $this->whitelabelSlotProvider->save();
        return $this->whitelabelSlotProvider;
    }

    public function createSlotTransaction(
        string $action,
        float $amount,
        Carbon $createdAt
    ): SlotTransaction {
        if ($createdAt === null) {
            $createdAt = Carbon::now();
        }
        DB::query("SET foreign_key_checks = 0;")->execute(); // i didn't want to create xxxx relations for those tests

        $slotTransaction = new SlotTransaction();
        $slotTransaction->slotGameId = 1;
        $slotTransaction->slotOpenGameId = 1;
        $slotTransaction->currencyId = 1; //usd
        $slotTransaction->whitelabelUserId = $this->whitelabelUser->id;
        $slotTransaction->whitelabelSlotProviderId = $this->whitelabelSlotProvider->id;
        $slotTransaction->providerTransactionId = rand(1000000, 9999999);
        $slotTransaction->amount = $amount;
        $slotTransaction->amountManager = $amount;
        $slotTransaction->amountUsd = $amount;
        $slotTransaction->createdAt = $createdAt;
        $slotTransaction->token = rand(1000000, 9999999);
        $slotTransaction->action = $action;
        $slotTransaction->type = $action;
        $slotTransaction->additionalData = json_encode(['x']);
        $slotTransaction->save();
        DB::query("SET foreign_key_checks = 1;")->execute();

        return $slotTransaction;
    }

    /** @test */
    public function getUserSummaryForMediacle_TryingToGetForNotExistingWhitelabel_ShouldReturnEmpty(): void
    {
        $result = $this->slotTransactionRepository->getUserSummaryForMediacle(900, Carbon::now());

        $this->assertEmpty($result);
    }

    /**
     * @test
     * @dataProvider betWinTransactionProvider
     */
    public function getUserSummaryForMediacle_CasesFromDataProvider(
        array | float $betAmount,
        array | float $winAmount,
        float $ggr,
        float $costs,
        float $revenue,
        bool $shouldBeEmpty,
        ?Carbon $date = null
    ): void {
        // create dummy transactions from different days; they are not calculated in getUserSummaryForMediacle query
        for ($i = 2; $i <= 6; $i++) {
            $randomDate = Carbon::now()->subDays($i);
            $this->createSlotTransaction('bet', 20.00, $randomDate);
            $this->createSlotTransaction('win', 20.00, $randomDate);
        }
        if (empty($date)) {
            $date = Carbon::yesterday();
        }
        $betsAmountFloat = 0.0;
        $winsAmountFloat = 0.0;

        if (is_array($betAmount) && !empty($betAmount)) {
            foreach ($betAmount as $bet) {
                $this->createSlotTransaction('bet', $bet, $date);
                $betsAmountFloat += $bet;
            }
        } else {
            $this->createSlotTransaction('bet', $betAmount, $date);
            $betsAmountFloat = $betAmount;
        }

        if (is_array($winAmount) && !empty($winAmount)) {
            foreach ($winAmount as $win) {
                $this->createSlotTransaction('win', $win, $date);
                $winsAmountFloat += $win;
            }
        } else {
            $this->createSlotTransaction('win', $winAmount, $date);
            $winsAmountFloat = $winAmount;
        }

        /** @var SalesDataSlotTransactionModelAdapter[] $result */
        $result = $this->slotTransactionRepository->getUserSummaryForMediacle($this->whitelabelUser->whitelabel_id, Carbon::yesterday());

        if ($shouldBeEmpty) {
            $this->assertEmpty($result);
        } else {
            $this->assertNotEmpty($result);
            $this->assertSame($betsAmountFloat, $result[0]->getBets());
            $this->assertSame($winsAmountFloat, $result[0]->getWins());
            $this->assertSame($ggr, $result[0]->getGgr());
            $this->assertSame($costs, $result[0]->getCosts());
            $this->assertSame($revenue, $result[0]->getRevenues());
        }
    }

    public function betWinTransactionProvider(): array
    {
        // [[bets], [wins], ggr, costs, revenue, shouldBeEmpty, date]
        return [
            // positive - one bet, one win
            [100.0, 10.0, 90.0, 13.5, 76.5, false], // ggr > 0, should return the record and calculate properly
            [2.0, 0, 2.0, 0.3, 1.7, false], // ggr > 0, 0 wins

            // negative - one bet, one win
            [10.0, 100.0, 0, 0, 0.0, true], // ggr < 0, should not return the record
            [100.0, 100.0, 0, 0, 0.0, true], // ggr = 0, should not return the record

            // positive - many bets and wins
            [[10.0, 10.0, 10.0], [0.0], 30, 4.5, 25.5, false],
            [[10.0, 10.90, 10.30], [0.0], 31.2, 4.68, 26.52, false],

            // test dates
            [[10.0, 10.90, 10.30], [0.0], 31.2, 4.68, 26.52, false], // should show records from yesterday
            [[10.0, 10.90, 10.30], [100.0], 0, 0, 0, true], // should hide records from previous days with ggr <= 0


            [[10.0, 10.90, 10.30], [0.0], 0, 0, 0, true, Carbon::now()->subDays(3)], // should hide records from previous days
            [[10.0, 10.90, 10.30], [0.0], 0, 0, 0, true, Carbon::now()->subDays(2)], // should hide records from previous days
            [[10.0, 10.90, 10.30], [300.0], 0, 0, 0, true], // should hide records from yesterday with ggr <= 0
            [[10.0, 10.90, 10.30], [300.0], 0, 0, 0, true, Carbon::now()->subDays(2)], // should hide records from previous days with ggr <= 0

            // negative - many bets and wins, ggr < 0
            [[10.0, 10.90, 10.30], [12.0, 12.3, 22.4], 0, 0, 0, true],
        ];
    }
}
