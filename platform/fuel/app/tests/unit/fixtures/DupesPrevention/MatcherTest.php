<?php

namespace Unit\Fixtures;

use Models\Currency;
use Models\Raffle;
use Orm\Model as Orm;
use Test_Unit;
use Tests\Fixtures\Raffle\RaffleFixture;
use Tests\Fixtures\Utils\DupesPrevention\Matcher;
use Tests\Fixtures\Utils\DupesPrevention\Overridable;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Utils\DupesPrevention\Matcher
 */
final class MatcherTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();
        $this->raffleFixture = $this->container->get(RaffleFixture::class);
    }

    /** @test */
    public function execute_ModelWithRelations_ThatShouldBeReplaced(): void
    {
        // Given Raffle with few Currency type relations
        /** @var Raffle $raffle */
        $raffle = $this->raffleFixture->with(
            'basic',
            function (Raffle $r) {
                $r->currency->code = 'USD';
                $r->getFirstRule()->currency->code = 'USD';
                $r->whitelabel_raffle->whitelabel->currency->code = 'USD';
            }
        )->makeOne();

        // And Matcher with handler for Currency that should be replaced with code = REPLACED
        $handlers = [
            Currency::class => function (Currency $new): ?Orm {
                return new Currency(['code' => 'REPLACED']);
            }
        ];

        $sut = new Matcher(
            new Overridable($handlers)
        );

        // When method is executed
        $sut->execute($raffle);

        // Then all occurrences of defined in handler model should be replaced
        $this->assertSame('REPLACED', $raffle->currency->code);
        $this->assertSame('REPLACED', $raffle->getFirstRule()->currency->code);
        $this->assertSame('REPLACED', $raffle->whitelabel_raffle->whitelabel->currency->code);

        foreach ($raffle->rules as $rule) {
            foreach ($rule->tiers as $tier) {
                $this->assertSame('REPLACED', $tier->currency->code);
            }
        }
    }
}
