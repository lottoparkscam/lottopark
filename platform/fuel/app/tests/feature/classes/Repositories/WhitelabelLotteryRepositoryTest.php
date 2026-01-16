<?php

namespace Tests\Feature\Classes\Repositories;

use Exceptions\Generic\WhitelabelNotFound;
use Models\Lottery;
use Models\WhitelabelLottery;
use Repositories\LotteryRepository;
use Repositories\WhitelabelLotteryRepository;
use Test_Feature;

class WhitelabelLotteryRepositoryTest extends Test_Feature
{
    private LotteryRepository $lotteryRepository;
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;

    private const TESTING_LOTTERY_SLUG = 'powerball';

    public function setUp(): void
    {
        parent::setUp();
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->whitelabelLotteryRepository = $this->container->get(WhitelabelLotteryRepository::class);
    }

    /** @test */
    public function isDisabledForCurrentWhitelabelByLotterySlug_whitelabelNotExists(): void
    {
        $this->expectException(WhitelabelNotFound::class);

        // Given
        $this->container->set('whitelabel', null);

        // When
        $this->whitelabelLotteryRepository->isDisabledForCurrentWhitelabelByLotterySlug(self::TESTING_LOTTERY_SLUG);

        // Then throw Exception
    }

    /** @test */
    public function isDisabledForCurrentWhitelabelByLotterySlug_whitelabelLotteryNotExists(): void
    {
        // Given
        /** @var WhitelabelLottery $whitelabelLottery */
        $whitelabelLottery = $this->whitelabelLotteryRepository->findOneBy('lottery_id', 1);
        $whitelabelLottery->delete();

        // When
        $isEnabled = $this->whitelabelLotteryRepository->isDisabledForCurrentWhitelabelByLotterySlug(self::TESTING_LOTTERY_SLUG);

        // Then
        $this->assertTrue($isEnabled);
    }

    /** @test */
    public function isDisabledForCurrentWhitelabelByLotterySlug_whitelabelLotteryIsDisabled(): void
    {
        // Given
        /** @var WhitelabelLottery $whitelabelLottery */
        $whitelabelLottery = $this->whitelabelLotteryRepository->findOneBy('lottery_id', 1);
        $whitelabelLottery->isEnabled = false;
        $whitelabelLottery->save();

        // When
        $isEnabled = $this->whitelabelLotteryRepository->isDisabledForCurrentWhitelabelByLotterySlug(self::TESTING_LOTTERY_SLUG);

        // Then
        $this->assertTrue($isEnabled);
    }

    /** @test */
    public function isDisabledForCurrentWhitelabelByLotterySlug_lotteryIsDisabled(): void
    {
        // Given
        /** @var Lottery $lottery */
        $lottery = $this->lotteryRepository->findOneBy('id', 1);
        $lottery->isEnabled = false;
        $lottery->save();

        // When
        $isEnabled = $this->whitelabelLotteryRepository->isDisabledForCurrentWhitelabelByLotterySlug(self::TESTING_LOTTERY_SLUG);

        // Then
        $this->assertTrue($isEnabled);
    }

    /** @test */
    public function isDisabledForCurrentWhitelabelByLotterySlug_isEnabled(): void
    {
        // Given
        /** @var WhitelabelLottery $whitelabelLottery */
        $whitelabelLottery = $this->whitelabelLotteryRepository->findOneBy('lottery_id', 1);
        $whitelabelLottery->isEnabled = true;
        $whitelabelLottery->save();

        /** @var Lottery $lottery */
        $lottery = $this->lotteryRepository->findOneBy('id', 1);
        $lottery->isEnabled = true;
        $lottery->save();

        // When
        $isEnabled = $this->whitelabelLotteryRepository->isDisabledForCurrentWhitelabelByLotterySlug(self::TESTING_LOTTERY_SLUG);

        // Then
        $this->assertFalse($isEnabled);
    }
}
