<?php

namespace Feature\Fixtures;

use Generator;
use Models\Currency;
use Models\Whitelabel;
use Models\WhitelabelTransaction as Transaction;
use Models\WhitelabelUser as User;
use PHPUnit\Framework\ExpectationFailedException;
use Stwarog\FuelFixtures\Fuel\Factory;
use Stwarog\FuelFixtures\Fuel\UowPersistence;
use Test_Feature;
use Tests\Fixtures\CurrencyFixture;
use Tests\Fixtures\FixturesProviderTrait;
use Tests\Fixtures\Utils\DupesPrevention\FeatureToggle;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelTransactionFixture as TransactionFixture;
use Tests\Fixtures\WhitelabelUserFixture;

/** @group fixture */
final class GenericFixturesTest extends Test_Feature
{
    use FixturesProviderTrait;

    private TransactionFixture $transactionFixture;
    private WhitelabelUserFixture $userFixture;
    private CurrencyFixture $currencyFixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->transactionFixture = $this->container->get(TransactionFixture::class);
        $this->userFixture = $this->container->get(WhitelabelUserFixture::class);
        $this->currencyFixture = $this->container->get(CurrencyFixture::class);
    }

    /** @test */
    public function createMany_Whitelabel(): void
    {
        $count = 20;
        $this->container->get(WhitelabelFixture::class)->setPrefixPoolLimit($count);

        $email = 'test@lottopark.work';
        $whitelabelFixture = $this->container->get(WhitelabelFixture::class);

        $whitelabelFixture->with(WhitelabelFixture::BASIC, WhitelabelFixture::CURRENCY)->createMany([
            'email' => $email
        ], $count);

        $this->assertDbHasRows(Whitelabel::class, ['email', '=', $email], $count);
    }

    public function provideFixtureWithoutBasicState(): Generator
    {
        /**
         * @var string $name
         * @var array<Factory> $data
         */
        foreach ($this->provideFixture() as $name => $data) {
            [$fixture] = $data;
            if (!$fixture->hasState('basic')) {
                yield $name => [$fixture];
            }
        }
    }

    public function provideFixtureWithBasicState(): Generator
    {
        /**
         * @var string $name
         * @var array<Factory> $data
         */
        foreach ($this->provideFixture() as $name => $data) {
            [$fixture] = $data;
            if ($fixture->hasState('basic')) {
                yield $name => [$fixture];
            }
        }
    }

    /**
     * @test
     * @group random
     * @group skipped
     * @dataProvider provideFixtureWithoutBasicState
     */
    public function createOne_BasicStateNotExists_ShouldBeAbleToPersistModelInDb(Factory $fixture): void
    {
        // Given fixture have no BASIC state

        // When createOne called
        $actual = $fixture->createOne();

        if (!isset($actual['id'])) {
            $this->skip('Model created by ' . get_class($fixture) . ' has no id property to check constraint');
        }

        // Then fixture should be able to persist model without any exceptions
        $this->assertDbHasRows($fixture::getClass(), ['id', '=', $actual['id']]);
    }

    /**
     * @test
     * @group random
     * @group skipped
     * @dataProvider provideFixtureWithBasicState
     */
    public function createOne_BasicStateExists_ShouldBeAbleToPersistModelInDb(Factory $fixture): void
    {
        // Given fixture have BASIC state
        $fixture->with($fixture::BASIC);

        // When createOne called
        $actual = $fixture->createOne();

        if (!isset($actual['id'])) {
            $this->skip('Model created by ' . get_class($fixture) . ' has no id property to check constraint');
        }

        // Then fixture should be able to persist model without any exceptions
        $this->assertDbHasRows($fixture::getClass(), ['id', '=', $actual['id']]);
    }

    /**
     * @test
     * @dataProvider provideFixtureWithBasicState
     */
    public function createMany_BasicStateExists_ShouldBeAbleToPersistModelsInDb(Factory $fixture): void
    {
        // Given fixture have BASIC state
        $fixture->with($fixture::BASIC);

        // When createOne called
        $models = $fixture->createMany([], 2);

        foreach ($models as $actual) {
            if (!isset($actual['id'])) {
                $this->skip('Model created by ' . get_class($fixture) . ' has no id property to check constraint');
            }

            // Then fixture should be able to persist model without any exceptions
            $this->assertDbHasRows($fixture::getClass(), ['id', '=', $actual['id']]);
        }
    }

    /**
     * There are two ways of persisting data in db, by regular Fuel -> save call on each model
     * or using Stwarog/FuelUow - with huge performance hit. We force here script to use the more efficient way.
     *
     * @test
     * @dataProvider provideFixture
     */
    public function checkAllFixturesContainsUowSavingStrategy(Factory $fixture): void
    {
        // Given all fixtures registered in fixtures-container.php

        // When got Fixture saving strategy
        $actual = $fixture->getPersistence();

        // Then it should be an instance of
        $expected = UowPersistence::class;
        $this->assertInstanceOf($expected, $actual, 'Register fixture in fixtures-container.php!');
    }

    /** @test */
    public function createOne_BasicStateUsedAndDupesPreventionEnabled_SavesInDbWithoutCurrencyDuplications(): void
    {
        // Prepare - before test run, DB must be seeded with existing EUR, USD and not existing ABC data
        try {
            $this->assertDbHasRows(Currency::class, ['code', '=', 'EUR'], 1);
            $this->assertDbHasRows(Currency::class, ['code', '=', 'USD'], 1);
            $this->assertDbHasRows(Currency::class, ['code', '=', 'ZXC'], 0);
        } catch (ExpectationFailedException $e) {
            $this->markTestSkipped('Invalid DB state before running test: ' . __METHOD__);
        }

        // Given seeded Currencies
        $before = Currency::query()->get();
        $before = array_map(fn(Currency $m) => $m->code, $before);

        // And dupes prevention feature toggle is enabled (disallow dupes)
        $this->container->get(FeatureToggle::class)->disallowDupes();

        // And fixture that have some Currency Fixture dependencies
        /** @var User $user */
        $user = $this->userFixture->with(
            'currency.pln',
            'whitelabel.basic',
            'basic'
        )->makeOne();

        $f = $this->transactionFixture->withWhitelabel($user->whitelabel)->with(
            'currency.zxc',
            'basic',
            'payment_currency.eur',
        );

        // When new entry is created
        /** @var Transaction $newModel */
        $f->createOne();

        // Then each Currency value (Currency Model) that has been generated and already exists in Database
        // should be replaced by the original value from DB
        // and there should be no dupes stored after persistence

        $after = Currency::query()->get();
        $after = array_map(fn(Currency $m) => $m->code, $after);
        $totalCount = array_count_values(array_values($after));

        foreach ($totalCount as $code => $count) {
            $this->assertSame(1, $count, 'Code ' . $code . ' has duplicated value ' . $code);
        }
    }

    /** @test */
    public function createOne_BasicStateUsedAndDupesPreventionDisabled_SavesInDbWithCurrencyDuplications(): void
    {
        // Given seeded Currencies
        $before = Currency::query()->get();
        $before = array_map(fn(Currency $m) => $m->code, $before);
        $beforeCount = count($before);

        // And dupes prevention feature toggle is disabled (allow dupes)
        $this->container->get(FeatureToggle::class)->allowDupes();

        // And fixture that have some Currency Fixture dependencies
        /** @var User $user */
        $user = $this->userFixture->with(
            'currency.pln',
            'whitelabel.basic',
            'basic',
            function (User $u) {
                $u->currency->code = 'PLN';
            }
        )->makeOne();

        $f = $this->transactionFixture->withWhitelabel($user->whitelabel)->with(
            'currency.zxc',
            'basic',
            'payment_currency.eur',
            function (Transaction $t) {
                $t->currency->code = 'ZXC';
            }
        );

        // When new entry is created
        /** @var Transaction $newModel */
        $f->createOne();

        // Then each Currency value (Currency Model) that has been generated can have duplicates
        $after = Currency::query()->get();
        $after = array_map(fn(Currency $m) => $m->code, $after);
        $this->assertGreaterThan($beforeCount, count($after));
    }
}
