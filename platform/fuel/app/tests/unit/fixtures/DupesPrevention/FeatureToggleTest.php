<?php

namespace Unit\Fixtures;

use Generator;
use Test_Unit;
use Tests\Fixtures\Utils\DupesPrevention\FeatureToggle;
use Tests\Fixtures\Utils\DupesPrevention\InteractsWithDupesFeatureToggle;

/**
 * @group fixture
 * @covers \Tests\Fixtures\Utils\DupesPrevention\FeatureToggle
 */
final class FeatureToggleTest extends Test_Unit
{
    /** @test */
    public function constructor_NoValuesPassed_ByDefaultShouldBeEnabled(): void
    {
        $sut = new FeatureToggle();
        $this->assertFalse($sut->isDupesPreventionDisabled());
    }
    /** @test */
    public function constructor_ObjectShouldBeInstanceOfInteractsInterface(): void
    {
        $sut = new FeatureToggle();
        $this->assertInstanceOf(InteractsWithDupesFeatureToggle::class, $sut);
    }

    /** @test */
    public function disallowDupes_ValueShouldBeSetToEnabled(): void
    {
        $sut = new FeatureToggle(false);
        $sut->disallowDupes();
        $this->assertFalse($sut->isDupesPreventionDisabled());
    }

    /** @test */
    public function disallowDupes_ValueShouldBeSetToDisabled(): void
    {
        $sut = new FeatureToggle(true);
        $sut->allowDupes();
        $this->assertTrue($sut->isDupesPreventionDisabled());
    }

    /**
     * @test
     * @dataProvider provideConstructorArgs
     */
    public function constructor_ValuesPassed_StateShouldBeGivenValue(bool $value): void
    {
        $sut = new FeatureToggle($value);
        $this->assertNotSame($value, $sut->isDupesPreventionDisabled());
    }

    /**
     * @return Generator<string, bool>
     */
    public function provideConstructorArgs(): Generator
    {
        yield 'true' => [true];
        yield 'false' => [false];
    }
}
