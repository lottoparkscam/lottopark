<?php

namespace Tests\Feature\Classes\Repositories;

use Carbon\Carbon;
use Fuel\Core\DB;
use Repositories\LotteryRepository;
use Repositories\WhitelabelLotteryRepository;
use Test_Feature;

class LotteryRepositoryTest extends Test_Feature
{
    private LotteryRepository $lotteryRepository;
    private WhitelabelLotteryRepository $whitelabelLotteryRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->lotteryRepository = $this->container->get(LotteryRepository::class);
        $this->whitelabelLotteryRepository = $this->container->get(WhitelabelLotteryRepository::class);
    }

    /** @test */
    public function findEnabledForCurrentWhitelabel_onlyEnabled(): void
    {
        $lotteries = $this->lotteryRepository->getResults();
        foreach ($lotteries as $lottery) {
            if ($lottery->slug === 'powerball') {
                continue;
            }

            $lottery->isEnabled = false;
            $lottery->save();
        }

        $lotteries = $this->lotteryRepository->findEnabledForCurrentWhitelabel();
        $powerball = $lotteries[0];
        $this->assertCount(1, $lotteries);

        $listOfNeededLotteryColumns = [
            'name',
            'slug',
            'current_jackpot',
            'current_jackpot_usd',
            'price',
            'timezone',
            'last_date_local',
            'next_date_local',
            'additional_data',
            'last_numbers',
            'last_bnumbers',
            'draw_dates',
            'currency',
            'force_currency',
            'quick_pick_lines',
            'model',
            'tier',
            'volume',
            'income_type',
            'income',
            'fee',
            'provider',
        ];

        foreach ($listOfNeededLotteryColumns as $neededLotteryColumn) {
            $this->assertArrayHasKey($neededLotteryColumn, $powerball);
        }
    }

    /** @test */
    public function findEnabledForCurrentWhitelabel_onlyNotTemporarilyDisabled(): void
    {
        $lotteries = $this->lotteryRepository->getResults();
        foreach ($lotteries as $lottery) {
            if ($lottery->slug === 'powerball') {
                continue;
            }

            $lottery->isTemporarilyDisabled = true;
            $lottery->save();
        }

        $lotteries = $this->lotteryRepository->findEnabledForCurrentWhitelabel();
        $powerball = $lotteries[0];
        $this->assertCount(1, $lotteries);
        $this->assertSame('Powerball', $powerball['name']);
        $this->assertSame('powerball', $powerball['slug']);
    }

    /** @test */
    public function findEnabledForCurrentWhitelabel_onlyWhitelabelLotteryIsEnabled(): void
    {
        $lotteries = $this->lotteryRepository->getResults();
        foreach ($lotteries as $lottery) {
            if ($lottery->slug === 'powerball') {
                continue;
            }

            $whitelabelLotteries = $this->whitelabelLotteryRepository->findByLotteryId($lottery->id);
            foreach ($whitelabelLotteries as $whitelabelLottery) {
                $whitelabelLottery->isEnabled = false;
                $whitelabelLottery->save();
            }
        }

        $lotteries = $this->lotteryRepository->findEnabledForCurrentWhitelabel();
        $powerball = $lotteries[0];
        $this->assertCount(1, $lotteries);
        $this->assertSame('Powerball', $powerball['name']);
        $this->assertSame('powerball', $powerball['slug']);
    }

    /** @test */
    public function findWaitingForDraw(): void
    {
        // Given
        DB::update('lottery')
            ->set([
                'next_date_local' => null,
                'next_date_utc' => null,
            ])
            ->execute();
        $powerball = $this->lotteryRepository->findOneBySlug('powerball');
        $powerball->isEnabled = true;
        $powerball->nextDateUtc = (Carbon::now('UTC'))->subHour();
        $powerball->save();

        // When
        $foundLotteries = $this->lotteryRepository->findWaitingForDraw();

        // Then
        $this->assertCount(1, $foundLotteries);
        $this->assertSame($powerball->name, $foundLotteries[0]['name']);
        $this->assertSame($powerball->slug, $foundLotteries[0]['slug']);
        $this->assertSame($powerball->timezone, $foundLotteries[0]['timezone']);
        $this->assertSame($powerball->nextDateLocal, $foundLotteries[0]['next_date_local']);
        $this->assertSame($powerball->currency->code, $foundLotteries[0]['currency_code']);
    }

    /** @test */
    public function findWaitingForDraw_whenLotteryIsDisabled(): void
    {
        // Given
        DB::update('lottery')
            ->set([
                'next_date_local' => null,
                'next_date_utc' => null,
            ])
            ->execute();
        $powerball = $this->lotteryRepository->findOneBySlug('powerball');
        $powerball->isEnabled = false;
        $powerball->nextDateUtc = (Carbon::now())->subHour();
        $powerball->save();

        // When
        $foundLotteries = $this->lotteryRepository->findWaitingForDraw();

        // Then
        $this->assertCount(0, $foundLotteries);
    }
}
