<?php

namespace Unit\fixtures\Exceptions;

use fixtures\Exceptions\MissingRelation;
use Models\Currency;
use Models\Raffle;
use PHPUnit\Framework\TestCase;

/**
 * @group fixture
 * @covers \fixtures\Exceptions\MissingRelation
 */
final class MissingRelationTest extends TestCase
{
    /** @test */
    public function verify_OneMissingField_ThrowsException(): void
    {
        // Given model
        $model = new Raffle();

        // Then except exception
        $this->expectException(MissingRelation::class);
        $this->expectExceptionMessage(
            'Missing relations: relation in model: ' . get_class($model) . ' in:'
        );

        // When checked against not existing the relation
        MissingRelation::verify($model, 'relation');
    }

    /** @test */
    public function verify_ManyMissingFields_ThrowsAggregatedException(): void
    {
        // Given model
        $model = new Raffle();

        // Then except exception
        $this->expectException(MissingRelation::class);
        $this->expectExceptionMessage(
            'Missing relations: relation, relation2 in model: ' . get_class($model) . ' in:'
        );

        // When checked against not existing the relation
        MissingRelation::verify($model, 'relation', 'relation2');
    }

    /** @test */
    public function verify_NoErrors(): void
    {
        // Given model
        $model = new Raffle();
        $model->currency = new Currency();

        // When checked against existing the relation
        MissingRelation::verify($model, 'currency');

        // Then no error should be thrown
        $this->assertTrue(true);
    }
}
