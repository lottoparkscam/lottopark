<?php

namespace Tests\Unit\Classes\Lotto\Lotteries;

use Test_Unit;

final class EuroDreamsTest extends Test_Unit
{
    public function is_winning_type(int $tier_n, int $tier_b, int $match_n, int $match_b): bool
    {
        if (
            // Match 6 & 1
            $tier_n == 6 &&
            $tier_b == 1 &&
            $tier_n == $match_n &&
            $tier_b == $match_b
        ) {
            return true;
        } elseif (
            // Match 6 & 0
            $tier_n == 6 &&
            $tier_b == 0 &&
            $tier_n == $match_n &&
            $tier_b == $match_b
        ) {
            return true;
        } elseif (
            // Match 5,4,3,2 and bonus 0 || 1
            ($tier_n == 5 || $tier_n == 4 || $tier_n == 3 || $tier_n == 2) &&
            $tier_n == $match_n
        ) {
            return true;
        } else {
            return false;
        }
    }

    public static function drawCases(): array
    {
        return [
            // [tier_n, tier_b, match_n, match_b, win]

            // TIER 1 - NO WINS
            [1, 0, 0, 0, false],
            [1, 0, 0, 1, false],
            [1, 0, 1, 0, false],
            [1, 0, 1, 1, false],
            [1, 0, 2, 0, false],
            [1, 0, 2, 1, false],
            [1, 0, 3, 0, false],
            [1, 0, 3, 1, false],
            [1, 0, 4, 0, false],
            [1, 0, 4, 1, false],
            [1, 0, 5, 0, false],
            [1, 0, 5, 1, false],
            [1, 0, 6, 0, false],
            [1, 0, 6, 1, false],

            // TIER 2
            [2, 0, 0, 0, false],
            [2, 0, 0, 1, false],
            [2, 0, 1, 0, false],
            [2, 0, 1, 1, false],
            [2, 0, 2, 0, true], // WIN
            [2, 0, 2, 1, true], // WIN
            [2, 0, 3, 0, false],
            [2, 0, 3, 1, false],
            [2, 0, 4, 0, false],
            [2, 0, 4, 1, false],
            [2, 0, 5, 0, false],
            [2, 0, 5, 1, false],
            [2, 0, 6, 0, false],
            [2, 0, 6, 1, false],

            // TIER 3
            [3, 0, 0, 0, false],
            [3, 0, 0, 1, false],
            [3, 0, 1, 0, false],
            [3, 0, 1, 1, false],
            [3, 0, 2, 0, false],
            [3, 0, 2, 1, false],
            [3, 0, 3, 0, true], // WIN
            [3, 0, 3, 1, true], // WIN
            [3, 0, 4, 0, false],
            [3, 0, 4, 1, false],
            [3, 0, 5, 0, false],
            [3, 0, 5, 1, false],
            [3, 0, 6, 0, false],
            [3, 0, 6, 1, false],

            // TIER 4
            [4, 0, 0, 0, false],
            [4, 0, 0, 1, false],
            [4, 0, 1, 0, false],
            [4, 0, 1, 1, false],
            [4, 0, 2, 0, false],
            [4, 0, 2, 1, false],
            [4, 0, 3, 0, false],
            [4, 0, 3, 1, false],
            [4, 0, 4, 0, true], // WIN
            [4, 0, 4, 1, true], // WIN
            [4, 0, 5, 0, false],
            [4, 0, 5, 1, false],
            [4, 0, 6, 0, false],
            [4, 0, 6, 1, false],

            // TIER 5
            [5, 0, 0, 0, false],
            [5, 0, 0, 1, false],
            [5, 0, 1, 0, false],
            [5, 0, 1, 1, false],
            [5, 0, 2, 0, false],
            [5, 0, 2, 1, false],
            [5, 0, 3, 0, false],
            [5, 0, 3, 1, false],
            [5, 0, 4, 0, false],
            [5, 0, 4, 1, false],
            [5, 0, 5, 0, true], // WIN
            [5, 0, 5, 1, true], // WIN
            [5, 0, 6, 0, false],
            [5, 0, 6, 1, false],

            // TIER 6-0
            [6, 0, 0, 0, false],
            [6, 0, 0, 1, false],
            [6, 0, 1, 0, false],
            [6, 0, 1, 1, false],
            [6, 0, 2, 0, false],
            [6, 0, 2, 1, false],
            [6, 0, 3, 0, false],
            [6, 0, 3, 1, false],
            [6, 0, 4, 0, false],
            [6, 0, 4, 1, false],
            [6, 0, 5, 0, false],
            [6, 0, 5, 1, false],
            [6, 0, 6, 0, true], // WIN
            [6, 0, 6, 1, false],

            // TIER 6-1
            [6, 1, 0, 0, false],
            [6, 1, 0, 1, false],
            [6, 1, 1, 0, false],
            [6, 1, 1, 1, false],
            [6, 1, 2, 0, false],
            [6, 1, 2, 1, false],
            [6, 1, 3, 0, false],
            [6, 1, 3, 1, false],
            [6, 1, 4, 0, false],
            [6, 1, 4, 1, false],
            [6, 1, 5, 0, false],
            [6, 1, 5, 1, false],
            [6, 1, 6, 0, false],
            [6, 1, 6, 1, true], // WIN - JACKPOT
        ];
    }

    /**
     * @test
     * @dataProvider drawCases
     */
    public function areNumbersWinningTest(int $tier_n, int $tier_b, int $match_n, int $match_b, bool $expected): void
    {
        $actual = $this->is_winning_type($tier_n, $tier_b, $match_n, $match_b);
        $this->assertSame($expected, $actual);
    }
}
