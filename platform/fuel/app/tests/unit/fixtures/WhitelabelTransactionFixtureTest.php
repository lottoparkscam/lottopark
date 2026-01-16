<?php

namespace Unit\Fixtures;

use Generator;
use Models\Currency as Currency;
use Models\Whitelabel;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Test_Unit;
use Tests\Fixtures\WhitelabelTransactionFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\WhitelabelTransactionFixture
 */
final class WhitelabelTransactionFixtureTest extends Test_Unit
{
    private WhitelabelTransactionFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(WhitelabelTransactionFixture::class);
    }

    /** @test */
    public function withWhitelabel_ShouldUseSameWhitelabelInstanceForAllCreateMany(): void
    {
        // Given whitelabel
        $wl = new Whitelabel();

        // And fixture withWhitelabel
        $this->fixture->withWhitelabel($wl);
        // And with basic state where we also generate some relations
        $this->fixture->with('basic');

        // When makeMany called
        $models = $this->fixture->makeMany([], 5);

        // Then each model wl relation should be the same object
        /** @var WhitelabelTransaction $model */
        foreach ($models as $model) {
            $this->assertSame($wl, $model->whitelabel);
        }
    }

    /** @test */
    public function withUser_ShouldUseSameUserInstanceForAllCreateMany(): void
    {
        // Given whitelabel
        $user = new WhitelabelUser();

        // And fixture withUser
        $this->fixture->withUser($user);
        // And with basic state where we also generate some relations
        $this->fixture->with('basic');

        // When makeMany called
        $models = $this->fixture->makeMany([], 5);

        // Then each model user relation should be the same object
        /** @var WhitelabelTransaction $model */
        foreach ($models as $model) {
            $this->assertSame($user, $model->user);
        }
    }

    /**
     * @dataProvider provideWithCurrency
     * @test
     */
    public function withCurrency_ShouldUseSameUserInstanceForAllCreateMany(
        Currency $transaction,
        ?Currency $payment = null
    ): void {
        // Given currency for transaction
        // and optionally for payment

        // And fixture with currency for transaction
        $this->fixture->withCurrency($transaction, $payment);
        // And with basic state where we also generate some relations
        $this->fixture->with('basic');

        // When makeMany called
        $models = $this->fixture->makeMany([], 5);

        // Then each model currency relation should be the same object
        /** @var WhitelabelTransaction $model */
        foreach ($models as $model) {
            $this->assertSame($transaction, $model->currency);
            // And if payment currency provided
            // Then payment currency should contains its value
            if (!empty($payment)) {
                $this->assertSame($payment, $model->payment_currency);
                return;
            }
            // Otherwise, payment currency should be same as transaction
            $this->assertSame($model->currency, $model->payment_currency);
        }
    }

    public function provideWithCurrency(): Generator
    {
        yield 'currency for transaction, no currency for payment - should use transaction`s currency' => [
            new Currency()
        ];
        yield 'currency for transaction and payment - should use payment currency' => [
            new Currency(),
            new Currency(),
        ];
    }
}
