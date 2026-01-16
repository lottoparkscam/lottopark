<?php

namespace Unit\Fixtures;

use Generator;
use Models\Currency as Currency;
use Test_Unit;
use Tests\Fixtures\CurrencyFixture;

/**
 * @group fixture
 * @covers \Tests\Fixtures\CurrencyFixture
 */
final class CurrencyFixtureTest extends Test_Unit
{
    private const RANDOM = -1.0;
    private CurrencyFixture $fixture;

    public function setUp(): void
    {
        parent::setUp();
        $this->fixture = $this->container->get(CurrencyFixture::class);
    }

    /**
     * @test
     * @dataProvider provideStates
     *
     * @param string $expectedCode
     * @param float $expectedRate
     */
    public function with_CodeState_ShouldCreateWithUpperCaseAndRate(string $expectedCode, float $expectedRate): void
    {
        // Given fixture

        // With state
        $this->fixture->with(strtolower($expectedCode));

        // When new currency created
        /** @var Currency $currency */
        $currency = $this->fixture->makeOne();

        // Then
        $this->assertSame($expectedCode, $currency->code);
        if ($expectedRate !== self::RANDOM) {
            $this->assertSame($expectedRate, $currency->rate);
        }
    }

    public function provideStates(): Generator
    {
        yield 'usd' => ['USD', 1.0];
        yield 'eur' => ['EUR', 0.8418];
        yield 'pln' => ['PLN', 3.5896];
        yield 'zxc' => ['ZXC', self::RANDOM];
        yield 'abc' => ['ABC', self::RANDOM];
    }

    /** @test */
    public function makeOne_NoStatesPassed_ShouldCreateRandomCodeInDefinedRange(): void
    {
        // Given fixture

        // And currencies codes that can be made
        $supported = $this->fixture::SUPPORTED_CODES;

        // When makeOne is called without any states
        /** @var Currency $result */
        $result = $this->fixture->makeOne();

        // Then generated code should be in hardcoded range
        // because it will generate too many unused codes and will pollute DB
        $this->assertTrue(in_array($result->code, $supported));
    }
}
