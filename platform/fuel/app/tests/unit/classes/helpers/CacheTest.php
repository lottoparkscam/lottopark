<?php

namespace Tests\Unit\Classes\Helpers;

use Helpers_Cache;
use Test_Unit;

final class CacheTest extends Test_Unit
{
    /** @test */
    public function changeNumbersInCacheKeyToLetters_shouldReturnSame()
    {
        $testStringWithNumbers = 'test1test2test10';
        $expectedValue = 'testbtestctestba';
        $receivedValue = Helpers_Cache::changeNumbersInCacheKeyToLetters($testStringWithNumbers);

        $this->assertSame($expectedValue, $receivedValue);
    }
}
