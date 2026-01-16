<?php

namespace Feature\Modules\Account\Balance;

use Fuel\Core\DB;
use Models\WhitelabelUser as User;
use Modules\Account\Balance\RegularBalance;
use Test_Feature;
use Tests\Fixtures\WhitelabelUserFixture;

final class RegularBalanceTest extends Test_Feature
{
    private WhitelabelUserFixture $userFixture;
    private RegularBalance $balance;
    private User $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->userFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->balance = $this->container->get(RegularBalance::class);

        DB::query("UPDATE `currency` SET `rate` = '0.8418' WHERE `currency`.`code` = 'EUR';")->execute();

        /** @var User $user */
        $this->userFixture->with(
            $this->userFixture::USD,
            $this->userFixture::BALANCE_10000,
            $this->userFixture::BASIC,
        );

        $this->user = $this->userFixture->createOne();
    }

    /** @test */
    public function debit_by100usdUserHas10000usd_balanceShouldBe9900usd(): void
    {
        // Given user with 10000 USD regular balance

        // And expected USD balance after debit
        $debitAmount = 100;
        $debitCurrency = 'USD';
        $expected = 10000 - $debitAmount;

        // When debit by 100 usd
        $this->balance->debit($this->user->id, $debitAmount, $debitCurrency);
        $this->balance->dispatch();

        // Then regular balance should be 9900 USD
        $this->assertDbHasRows(
            User::class,
            [
                ['id', '=', $this->user->id],
                ['balance', '=', $expected],
            ]
        );
    }

    /** @test */
    public function debit_by100eurUserHas10000usd_balanceShouldBeCalculatedTo9881usd(): void
    {
        // Given user with 10000 USD regular balance

        // And expected USD balance after debit
        // and converting requested EUR amounts to user USD
        $expected = 9881.20;
        $debitAmount = 25;
        $debitCurrency = 'EUR';

        // When debit is called with two equal calls
        $this->balance->debit($this->user->id, $debitAmount, $debitCurrency);
        $this->balance->debit($this->user->id, $debitAmount, $debitCurrency);
        $this->balance->debit($this->user->id, $debitAmount, $debitCurrency);
        $this->balance->debit($this->user->id, $debitAmount, $debitCurrency);
        $this->balance->dispatch();

        // Then regular balance should be 9900 USD
        $this->assertDbHasRows(
            User::class,
            [
                ['id', '=', $this->user->id],
                ['balance', '=', $expected],
            ]
        );
    }

    /**
     * @test
     * @return array<int, array<int, int>|float>
     */
    public function changeBalance_DispatchCalledInLoop_ShouldChangeProperlyBalance(): array
    {
        // Given balance service

        // And user 1 with 0 balance and USD currency
        /** @var User $userInTHeLoop */
        $userInTHeLoop = $this->userFixture->with($this->userFixture::BALANCE_0)->createOne();
        $currencyCode = $userInTHeLoop->currency->code;

        // When attempting to call many increase / debit methods in the loop with given amounts
        $amounts = [
            100,
            -50,
            25,
            -50,
            -3
        ];

        foreach ($amounts as $amount) {
            if ($amount > 0) {
                $this->balance->increase($userInTHeLoop->id, $amount, $currencyCode);
            } else {
                $this->balance->debit($userInTHeLoop->id, abs($amount), $currencyCode);
            }
            $this->balance->dispatch();
        }

        // Then result should be equal to sum of these values
        $userInTHeLoop->reload();
        $this->assertSame(0 + 22.00, $userInTHeLoop->balance);

        return [0 + 22.00, $amounts];
    }

    /**
     * @test
     * @depends changeBalance_DispatchCalledInLoop_ShouldChangeProperlyBalance
     */
    public function changeBalance_DispatchCalledOutOfLoop_ShouldHaveSameResultAsRunningOutOfLoop(array $data): void
    {
        [$expectedBalance, $amounts] = $data;

        // Given user 2 with no balance and USD currency
        /** @var User $userOutOfTheLoop */
        $userOutOfTheLoop = $this->userFixture->with($this->userFixture::BALANCE_0)->createOne();
        $currencyCode = $userOutOfTheLoop->currency->code;

        // When attempting to call many increase / debit methods
        foreach ($amounts as $amount) {
            if ($amount > 0) {
                $this->balance->increase($userOutOfTheLoop->id, $amount, $currencyCode);
            } else {
                $this->balance->debit($userOutOfTheLoop->id, abs($amount), $currencyCode);
            }
        }
        // And dispatch is called out of the loop
        $this->balance->dispatch();

        // Then result should be equal to sum of these values
        $userOutOfTheLoop->reload();
        $this->assertSame($expectedBalance, $userOutOfTheLoop->balance);
    }
}
