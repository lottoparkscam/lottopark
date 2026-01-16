<?php

namespace Unit\Fixtures;

use fixtures\Exceptions\MissingRelation;
use Generator;
use Models\Whitelabel;
use Models\WhitelabelTransaction;
use Models\WhitelabelUser;
use Orm\Model;
use Stwarog\FuelFixtures\Fuel\Factory;
use Test_Unit;
use Tests\Fixtures\CurrencyFixture;
use Tests\Fixtures\FixturesProviderTrait;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\Raffle\RaffleRuleFixture;
use Tests\Fixtures\Raffle\RaffleRuleTierFixture;
use Tests\Fixtures\Raffle\RaffleTicketFixture;
use Tests\Fixtures\Raffle\RaffleTicketLineFixture;
use Tests\Fixtures\Raffle\WhitelabelRaffleFixture;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelTransactionFixture;
use Tests\Fixtures\WhitelabelUserFixture;
use Unit\Fixtures\Cases\HasStates;

use const SORT_STRING;

/** @group fixture */
final class GenericFixturesTest extends Test_Unit implements HasStates
{
    use FixturesProviderTrait;

    private const CAN_HAVE_ID_IN_MAKE_ONE = [
        Whitelabel::class => ['language_id', 'manager_site_currency_id'],
        WhitelabelUser::class => ['language_id', 'national_id'],
        WhitelabelTransaction::class => ['transaction_out_id'],
    ];

    public function provideFixtureWithAtLeastOneState(): Generator
    {
        self::setUp();
        foreach ($this->container->get('fixtures') as $fixtureName) {
            /** @var Factory $fixture */
            $fixture = $this->container->get($fixtureName);
            if (!empty($fixture->getStates())) {
                yield $fixtureName => [$this->container->get($fixtureName)];
            }
        }
    }

    public function provideFixtureWithoutDbStates(): Generator
    {
        self::setUp();
        foreach ($this->container->get('fixtures') as $fixtureName) {
            /** @var Factory $fixture */
            $fixture = $this->container->get($fixtureName);
            $stateNames = array_keys($fixture->getStates());
            $statesWithoutDbAsPrefix = array_filter($stateNames, fn (string $name) => strpos($name, 'DB_') === false);

            if (!empty($statesWithoutDbAsPrefix)) {
                foreach ($statesWithoutDbAsPrefix as $state) {
                    yield $fixtureName . '.' . $state => [$fixture, $state];
                }
            }
        }
    }

    /**
     * @test
     * @dataProvider provideFixture
     */
    public function makeOne_CreatesRandomInstance_WithoutDbCall(Factory $fixture): void
    {
        // When makeOne called
        $actual = $fixture->makeOne();

        // Then created model should be instance of desired class without calling DB
        $expected = $fixture::getClass();
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @test
     * @dataProvider provideFixtureWithoutDbStates
     */
    public function makeOne_StatesWithoutDbPrefix_WithoutDbCall(Factory $fixture, string $state): void
    {
        // Given fixture with state not starting from "DB_"
        $fixture->with($state);

        // When makeOne called
        try {
            $actual = $fixture->makeOne();
        } catch (MissingRelation $e) {
            // we do not care that entry was not created, we only want to check there is no DB call
            $this->assertTrue(true);
            return;
        }

        // Then created model should be instance of desired class without calling DB
        $expected = $fixture::getClass();
        $this->assertInstanceOf($expected, $actual);
    }

    /**
     * @test
     * @dataProvider provideFixture
     */
    public function makeMany_CreatesRandomInstances_WithoutDbCall(Factory $fixture): void
    {
        // When makeMany called
        $models = $fixture->makeMany([], 2);

        // Then created models should be instances of Currency without calling DB
        $expected = $fixture::getClass();
        array_filter($models, fn (Model $model) => $this->assertInstanceOf($expected, $model));
    }

    /**
     * This test is very important for valid ORM work. When we assign directly IDs on model as relation,
     * then we break "no technical details in the code" rule and we occur unexpected orm behaviour.
     * Also Unit of Work - packages required more object oriented syntax. ID's are generated on persistence process
     * underneath.
     *
     * @test
     * @param Factory $fixture
     * @dataProvider provideFixture
     */
    public function makeOne_CreatesRandomInstance_PropsDoesNotContainIds(Factory $fixture): void
    {
        // Given fixture default keys
        $default = array_keys($fixture->getDefaults());

        // And keys containing _id in name and not excluded in CAN_HAVE_ID_IN_MAKE_ONE array
        $keysWithIds = array_filter(
            $default,
            function (string $key) use ($fixture) {
                $modelName = (string)$fixture;
                $isNotExcluded = empty(self::CAN_HAVE_ID_IN_MAKE_ONE[$modelName])
                    ||
                    !in_array($key, self::CAN_HAVE_ID_IN_MAKE_ONE[$modelName]);
                $hasFieldContainingId = strpos($key, '_id') !== false;
                return $isNotExcluded && $hasFieldContainingId;
            }
        );

        // When random model is created
        $model = $fixture->makeOne();

        // Then none of properties containing "_id" should contains generated value
        foreach ($keysWithIds as $key) {
            $modelName = get_class($model);
            $this->assertEmpty($model->$key, "Property $key of model $modelName should be empty");
        }

        $this->assertTrue(true);
    }

    /**
     * This test is an overall view what states we got in each Fixture.
     *
     * @test
     * @dataProvider provideFixtureWithAtLeastOneState
     */
    public function getStates_ContainsExpectedStates(Factory $fixture): void
    {
        // Given expected states for each factory
        $expectedStatesCollection = [
            CurrencyFixture::getClass() => ['usd', 'eur', 'pln', 'abc', 'zxc'],
            RaffleFixture::getClass() => [
                'basic',
                'currency',
                'rule',
                'temporary_disabled',
                'ggworld',
                'temporary_enabled',
                'playable',
                'whitelabel_raffle',
                'regular_price',
                'bonus_disabled',
                'bonus_enabled',
                'bets50',
            ],
            RaffleRuleFixture::getClass() => [
                'currency',
                'ggworld',
                'tiers',
                'basic',
                'raffle',
            ],
            RaffleRuleTierFixture::getClass() => [
                'currency',
                'rule',
                'basic',
                'raffle_prize_in_kind'
            ],
            RaffleTicketFixture::getClass() => [
                'basic',
                'currency',
                'whitelabel',
                'user',
                'transaction',
                'raffle',
                'rule',
                'one_raffle',
                'lines',
                'draw'
            ],
            RaffleTicketLineFixture::getClass() => ['ticket', 'whitelabel', 'basic', 'raffle_prize',],
            WhitelabelRaffleFixture::getClass() => ['raffle', 'whitelabel', 'basic'],
            WhitelabelFixture::getClass() => ['currency', 'basic'],
            WhitelabelTransactionFixture::getClass() => [
                'payment_currency',
                'currency',
                'whitelabel',
                'user',
                'basic'
            ],
            WhitelabelUserFixture::getClass() => [
                'basic',
                'currency',
                'whitelabel',
                'without_whitelabel',
                'balance_1000',
                'bonus_balance_1000',
                'balance_0',
                'usd',
                'eur',
            ],
        ];

        $fixtureAsString = (string)$fixture;

        if (!isset($expectedStatesCollection[$fixtureAsString])) {
            $this->markTestSkipped('Missing expected state collection for ' . $fixtureAsString);
        }

        if (empty($expectedStatesCollection[$fixtureAsString])) {
            $this->markTestSkipped('Empty state collection for ' . $fixtureAsString);
        }

        $expected = $expectedStatesCollection[$fixtureAsString];
        asort($expected, SORT_STRING);

        // When getStates is called
        $statesCollection = $fixture->getStates();
        $actualStateNames = array_keys($statesCollection);
        asort($actualStateNames, SORT_STRING);

        // Then
        $this->assertSame(array_values($expected), array_values($actualStateNames), "Failed for $fixtureAsString");
    }
}
