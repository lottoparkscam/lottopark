<?php

namespace Unit\Classes\Services;

use Services\MemoizeService;
use Test_Unit;

class MemoizeTest extends Test_Unit
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function findCacheResult_fromManyFunctions()
    {
        $exampleClass = new ExampleClass();
        $sum = $exampleClass->sum(1, 2, 3);
        $this->assertSame(6, $sum);

        $sum = $exampleClass->sum(1, 2, 4);
        $this->assertSame(7, $sum);

        $sum = $exampleClass->sum(1, 2, 3);
        $this->assertSame(6, $sum);

        $concat = $exampleClass->concat('a', 'b', 'c');
        $this->assertSame('abc', $concat);

        $concat = $exampleClass->concat('d', 'e', 'f');
        $this->assertSame('def', $concat);

        $concat = $exampleClass->concat('a', 'b', 'c');
        $this->assertSame('abc', $concat);
    }
}

class ExampleClass
{
    private MemoizeService $memoizeService;
    public function __construct()
    {
        $this->memoizeService = new MemoizeService();
    }

    function sum(...$args): int
    {
        $this->memoizeService->prepareArgs(...$args);
        $sum = 0;

        foreach ($args as $arg) {
            $sum += $arg;
        }

        $this->memoizeService->addResultToCache($sum);
        return $this->memoizeService->findCachedResult();
    }

    function concat(...$args): string
    {
        $this->memoizeService->prepareArgs(...$args);
        $text = '';

        foreach ($args as $arg) {
            $text .= $arg;
        }

        $this->memoizeService->addResultToCache($text);
        return $this->memoizeService->findCachedResult();
    }
}
