<?php

use Models\Lottery;
use Repositories\LotteryRepository;

final class LotteryHelper
{
    public static function getLotteryTimezonePerSlug(string $slug): string
    {
        try {
            $lotteryRepository = Container::get(LotteryRepository::class);
            /** @var Lottery $lottery */
            $lottery = $lotteryRepository->findOneBySlug($slug);
            return $lottery->timezone;
        } catch (Throwable) {
            return '';
        }
    }
}
