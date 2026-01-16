<?php

use Fuel\Core\Input;
use Models\SlotProvider;
use Models\SlotWhitelistIp;
use Repositories\SlotProviderRepository;
use Services\Api\Slots\SecurityService;

class SecurityTest extends Test_Feature
{
    private SecurityService $securityService;
    private SlotProviderRepository $slotProviderRepository;
    private SlotProvider $slotProvider;
    private SlotWhitelistIp $slotWhitelistIp;
    protected $in_transaction = false;

    public function setUp(): void
    {
        parent::setUp();
        $this->securityService = Container::get(SecurityService::class);
        $this->slotProviderRepository = Container::get(SlotProviderRepository::class);
        $this->slotProvider = $this->slotProviderRepository->findOneBySlug('slotegrator');
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (!empty($this->slotWhitelistIp)) {
            $this->slotWhitelistIp->delete();
        }
    }

    /** @test */
    public function isCurrentIpAllowed_forCorrectIp_shouldAllow()
    {
        // Given
        $ip = Input::real_ip();
        $slotWhitelistIp = new SlotWhitelistIp();
        $slotWhitelistIp->ip = $ip;
        $slotWhitelistIp->slotProviderId = $this->slotProvider->id;
        $slotWhitelistIp->save();
        $this->slotWhitelistIp = $slotWhitelistIp;

        // When
        $checkIp = $this->securityService->isCurrentIpAllowed($this->slotProvider->id);

        // Then
        $this->assertTrue($checkIp);
    }

    /** @test */
    public function isCurrentIpAllowed_forIncorrectIp_shouldDeny()
    {
        // When
        $checkIp = $this->securityService->isCurrentIpAllowed($this->slotProvider->id);

        // Then
        $this->assertFalse($checkIp);
    }
}
