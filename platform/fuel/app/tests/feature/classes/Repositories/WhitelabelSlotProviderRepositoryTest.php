<?php

namespace Tests\Feature\Classes\Repositories;

use Repositories\WhitelabelSlotProviderRepository;
use Test_Feature;

final class WhitelabelSlotProviderRepositoryTest extends Test_Feature
{
    private WhitelabelSlotProviderRepository $whitelabelSlotProviderRepository;

    public function setUp(): void
    {
        parent::setUp();
        $this->whitelabelSlotProviderRepository = $this->container->get(WhitelabelSlotProviderRepository::class);
    }

    /** @test */
    public function findIdsOfEnabledSlotProvidersByWhitelabelId_WithoutSlotProviders_ShouldReturnEmptyArray(): void
    {
        $actual = $this->whitelabelSlotProviderRepository->findIdsOfEnabledSlotProvidersByWhitelabelId(600);

        $this->assertEmpty($actual);
    }
}
