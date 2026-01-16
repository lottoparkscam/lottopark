<?php

namespace Services\LotteryProvider;

use Models\Lottery;

final class TheLotterLotteryMap
{
    /** @var array<string, int> LOTTERIES */
    private const LOTTERIES = [
        'lotto-at' => 1,
        'la-primitiva' => 11,
        'powerball-au' => 12,
        'saturday-lotto-au' => 14,
        'lotto-6aus49' => 20,
        'superenalotto' => 22,
        'powerball' => 25,
        'mega-millions' => 60,
        'euromillions' => 99,
        'oz-lotto' => 105,
        'el-gordo-primitiva' => 113,
        'bonoloto' => 146,
        'eurojackpot' => 153,
        Lottery::EURODREAMS_SLUG => 272,
        Lottery::WEEKDAY_WINDFALL_SLUG => 290,
        Lottery::EUROMILLIONS_SUPERDRAW_SLUG => 188,
    ];

    public static function getLotteryIdBySlug(string $slug): int|null
    {
        return self::LOTTERIES[$slug] ?? null;
    }

    public static function getAllLotterySlugsByTheLotterProvider(): array
    {
        return array_keys(self::LOTTERIES);
    }
}