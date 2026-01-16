<?php

namespace unit\classes\services\MiniGame\Game;

use ReflectionMethod;
use Services\MiniGame\Game\GgWorldTicTacBooGameService;
use Test_Unit;

class GgWorldTicTacBooGameServiceTest extends Test_Unit
{
    public function testGenerateWinningGrid(): void
    {
        $service = new GgWorldTicTacBooGameService();

        foreach ([0, 1] as $winningSymbol) {
            $grid = $service->generateWinningGrid($winningSymbol);

            $this->assertNotEmpty($grid);

            $this->assertCount(3, $grid);
            foreach ($grid as $row) {
                $this->assertCount(3, $row);
            }

            $winningLines = [
                [[0, 0], [0, 1], [0, 2]],
                [[1, 0], [1, 1], [1, 2]],
                [[2, 0], [2, 1], [2, 2]],
                [[0, 0], [1, 0], [2, 0]],
                [[0, 1], [1, 1], [2, 1]],
                [[0, 2], [1, 2], [2, 2]],
                [[0, 0], [1, 1], [2, 2]],
                [[0, 2], [1, 1], [2, 0]]
            ];

            $reflectionMethod = new ReflectionMethod(GgWorldTicTacBooGameService::class, 'countWinningLines');
            $reflectionMethod->setAccessible(true);

            $winningSymbolLines = $reflectionMethod->invoke($service, $grid, $winningSymbol, $winningLines);
            $this->assertEquals(1, $winningSymbolLines);

            $losingSymbol = 1 - $winningSymbol;
            $losingSymbolLines = $reflectionMethod->invoke($service, $grid, $losingSymbol, $winningLines);
            $this->assertEquals(0, $losingSymbolLines);
        }
    }

    public function testGetCombinations(): void
    {
        $service = new GgWorldTicTacBooGameService();

        $reflectionMethod = new ReflectionMethod(GgWorldTicTacBooGameService::class, 'getCombinations');
        $reflectionMethod->setAccessible(true);

        $arr = [1, 2, 3, 4];
        $k = 2;
        $combinations = $reflectionMethod->invoke($service, $arr, $k);

        $expectedCombinations = [
            [1, 2],
            [1, 3],
            [1, 4],
            [2, 3],
            [2, 4],
            [3, 4]
        ];

        $this->assertEqualsCanonicalizing($expectedCombinations, $combinations);

        $k = 0;
        $combinations = $reflectionMethod->invoke($service, $arr, $k);

        // when k=0 should return an array with an empty array
        $this->assertEquals([[]], $combinations);

        $k = 5;
        $combinations = $reflectionMethod->invoke($service, $arr, $k);

        // when k > n should return an empty array
        $this->assertEmpty($combinations);
    }

    public function testCountWinningLines(): void
    {
        $service = new GgWorldTicTacBooGameService();

        $grid = [
            [1, 1, 1],
            [0, 0, null],
            [null, null, null]
        ];

        $winningSymbol = 1;
        $losingSymbol = 0;

        $winningLines = [
            [[0, 0], [0, 1], [0, 2]],
            [[1, 0], [1, 1], [1, 2]],
            [[2, 0], [2, 1], [2, 2]],
            [[0, 0], [1, 0], [2, 0]],
            [[0, 1], [1, 1], [2, 1]],
            [[0, 2], [1, 2], [2, 2]],
            [[0, 0], [1, 1], [2, 2]],
            [[0, 2], [1, 1], [2, 0]]
        ];

        $reflectionMethod = new ReflectionMethod(GgWorldTicTacBooGameService::class, 'countWinningLines');
        $reflectionMethod->setAccessible(true);

        $winningSymbolLines = $reflectionMethod->invoke($service, $grid, $winningSymbol, $winningLines);
        $this->assertEquals(1, $winningSymbolLines);

        $losingSymbolLines = $reflectionMethod->invoke($service, $grid, $losingSymbol, $winningLines);
        $this->assertEquals(0, $losingSymbolLines);

        $grid = [
            [1, 1, 1],
            [1, 1, 1],
            [1, 1, 1]
        ];

        $winningSymbolLines = $reflectionMethod->invoke($service, $grid, $winningSymbol, $winningLines);
        $this->assertEquals(8, $winningSymbolLines);

        $losingSymbolLines = $reflectionMethod->invoke($service, $grid, $losingSymbol, $winningLines);
        $this->assertEquals(0, $losingSymbolLines);
    }
}
