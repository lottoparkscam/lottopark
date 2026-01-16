<?php

use Models\Lottery;

class Lotto_Lotteries_NorwegianKeno extends Lotto_Lotteries_Keno
{
    public const LOTTERY_JACKPOT = 2; // 100 000 * 20 (multiplier) / 1 000 000
    public const LOTTERY_NUMBERS_COUNT = 20;
    public const LOTTERY_NUMBERS_RANGE = [1, 70];
    public const PROVIDER_TIMEZONE = 'Europe/Oslo';

    protected string $lottery_slug = Lottery::NORWEGIAN_KENO_SLUG;
}
