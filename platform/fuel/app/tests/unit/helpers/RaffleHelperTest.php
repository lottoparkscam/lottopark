<?php

namespace Tests\Unit\Helpers;

use Helpers\RaffleHelper;
use Test_Unit;

class RaffleHelperTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider providerRafflePrizeInKindSlugsCases
     */
    public function prizeInKindSlugToLotterySlug(string $inputSlug, string $expectedSlug): void
    {
        $convertedSlug = RaffleHelper::prizeInKindSlugToLotterySlug($inputSlug);
        $this->assertEquals($expectedSlug, $convertedSlug);
    }

    public static function providerRafflePrizeInKindSlugsCases(): array
    {
        return [
            ['100x-euromillions', 'euromillions'],
            ['10x-mega-millions', 'mega-millions'],
            ['5x-gg-world-x', 'gg-world-x'],
            ['1x-gg-world-million', 'gg-world-million'],
            ['unrelated-lottery-slug', 'unrelated-lottery-slug'],
        ];
    }
}
