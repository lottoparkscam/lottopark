<?php

namespace Tests\Feature\Classes\Repositories;

use Models\Whitelabel;
use Models\WhitelabelReferStatistics;
use Models\WhitelabelUser;
use Repositories\WhitelabelReferStatisticsRepository;
use Repositories\WhitelabelRepository;
use Test_Feature;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelUserFixture;

class WhitelabelReferStatisticsRepositoryTest extends Test_Feature
{
    private WhitelabelReferStatisticsRepository $whitelabelReferStatisticsRepository;
    private WhitelabelRepository $whitelabelRepository;
    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelUserFixture $whitelabelUserFixture;
    private WhitelabelUser $whitelabelUser;
    private WhitelabelUser $newWhitelabelUser;
    private Whitelabel $newWhitelabel;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelReferStatisticsRepository = $this->container->get(WhitelabelReferStatisticsRepository::class);
        $this->whitelabelRepository = $this->container->get(WhitelabelRepository::class);
        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelUserFixture = $this->container->get(WhitelabelUserFixture::class);

        $this->whitelabelUser = $this->whitelabelUserFixture->with('basic')->createOne();
        $this->newWhitelabelUser = $this->whitelabelUserFixture->with('basic')->createOne();
        $this->newWhitelabel = $this->whitelabelFixture->with('basic')->createOne();
    }

    /** @test */
    public function findOneByWhitelabelAndUser_withCorrectRelations(): void
    {
        $whitelabel = $this->whitelabelRepository->findOneByTheme('LottoPark');

        (new WhitelabelReferStatistics([
            'whitelabel_user_id' => $this->whitelabelUser->id,
            'whitelabel_id' => $whitelabel->id,
            'token' => 135246,
            'clicks' => 0,
            'unique_clicks' => 0,
            'registrations' => 0,
            'free_tickets' => 0
        ]))->save();

        $whitelabelReferStatistics = $this->whitelabelReferStatisticsRepository->findOneByWhitelabelAndUser(
            $whitelabel->id,
            $this->whitelabelUser->id,
            135246,
        );

        $this->assertSame($this->whitelabelUser->id, $whitelabelReferStatistics->whitelabelUser->id);
        $this->assertSame($whitelabel->id, $whitelabelReferStatistics->whitelabel->id);
        $this->assertSame(135246, $whitelabelReferStatistics->token);
    }

    /** @test */
    public function findOneByWhitelabelAndUser_withIncorrectWhitelabel(): void
    {
        $whitelabel = $this->whitelabelRepository->findOneByTheme('LottoPark');

        (new WhitelabelReferStatistics([
            'whitelabel_user_id' => $this->whitelabelUser->id,
            'whitelabel_id' => $this->newWhitelabel->id,
            'token' => 135246,
            'clicks' => 0,
            'unique_clicks' => 0,
            'registrations' => 0,
            'free_tickets' => 0
        ]))->save();

        $whitelabelReferStatistics = $this->whitelabelReferStatisticsRepository->findOneByWhitelabelAndUser(
            $whitelabel->id,
            $this->whitelabelUser->id,
            135246,
        );

        $this->assertNull($whitelabelReferStatistics);
    }

    /** @test */
    public function findOneByWhitelabelAndUser_withIncorrectUser(): void
    {
        $whitelabel = $this->whitelabelRepository->findOneByTheme('LottoPark');

        (new WhitelabelReferStatistics([
            'whitelabel_user_id' => $this->newWhitelabelUser->id,
            'whitelabel_id' => $whitelabel->id,
            'token' => 135246,
            'clicks' => 0,
            'unique_clicks' => 0,
            'registrations' => 0,
            'free_tickets' => 0
        ]))->save();

        $whitelabelReferStatistics = $this->whitelabelReferStatisticsRepository->findOneByWhitelabelAndUser(
            $whitelabel->id,
            $this->whitelabelUser->id,
            135246,
        );

        $this->assertNull($whitelabelReferStatistics);
    }

    /** @test */
    public function findOneByWhitelabelAndUser_withIncorrectToken(): void
    {
        $whitelabel = $this->whitelabelRepository->findOneByTheme('LottoPark');

        (new WhitelabelReferStatistics([
            'whitelabel_user_id' => $this->whitelabelUser->id,
            'whitelabel_id' => $whitelabel->id,
            'token' => 135246,
            'clicks' => 0,
            'unique_clicks' => 0,
            'registrations' => 0,
            'free_tickets' => 0
        ]))->save();

        $whitelabelReferStatistics = $this->whitelabelReferStatisticsRepository->findOneByWhitelabelAndUser(
            $whitelabel->id,
            $this->whitelabelUser->id,
            135247,
        );

        $this->assertNull($whitelabelReferStatistics);
    }
}
