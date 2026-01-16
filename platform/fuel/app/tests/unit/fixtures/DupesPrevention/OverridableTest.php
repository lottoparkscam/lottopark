<?php

namespace Unit\Fixtures;

use Models\Currency;
use Models\Raffle;
use Orm\Model as Orm;
use Test_Unit;
use Tests\Fixtures\Utils\DupesPrevention\Overridable;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Utils\DupesPrevention\Overridable
 */
final class OverridableTest extends Test_Unit
{
    /** @test */
    public function hasHandler_HandlerExists_WillReturnTrue(): Overridable
    {
        // Given suite with some predefined handlers
        $handlers = [
            Currency::class => function (Currency $new): ?Orm {
            }
        ];
        $sut = new Overridable($handlers);

        // And a Model that exists in handlers definitions
        $model = new Currency();

        // When checking that Currency handler exists
        // Then it should be true
        $this->assertTrue($sut->hasHandler($model));

        return $sut;
    }

    /**
     * @test
     * @depends hasHandler_HandlerExists_WillReturnTrue
     */
    public function hasHandler_HandlerDoesNotExist_WillReturnFalse(Overridable $sut): void
    {
        // Given a Model that does not exist in handlers definitions
        $model = new Raffle();

        // When checking that Currency handler exists
        // Then it should be false
        $this->assertFalse($sut->hasHandler($model));
    }

    /** @test */
    public function findReplacementInDb_HandlerExists_WillReturnNewValue(): Overridable
    {
        // Given suite with some predefined handlers
        $handlers = [
            Currency::class => function (Currency $new): ?Orm {
                return new Currency(['code' => 'EUR']);
            }
        ];
        $sut = new Overridable($handlers);

        // And a Model that exists in handlers definitions and has code = PLN
        $model = new Currency(['code' => 'PLN']);

        // When looking for replacement in DB
        /** @var Currency $model */
        $model = $sut->findReplacementInDb($model);

        // Then the original Model should be replaced
        $codeFromReplacementClosure = 'EUR';
        $this->assertSame($model->code, $codeFromReplacementClosure);

        return $sut;
    }

    /** @test */
    public function findReplacementInDb_HandlerDoesNotExist_WillReturnNull(): void
    {
        // Given suite with some predefined handlers
        $handlers = [
            Currency::class => function (Currency $new): ?Orm {
                return null;
            }
        ];
        $sut = new Overridable($handlers);

        // And a Model that exists in handlers definitions
        $model = new Currency();

        // When looking for replacement in DB
        $model = $sut->findReplacementInDb($model);

        // Then the original Model should be replaced by null
        $this->assertNull($model);
    }
}
