<?php

namespace Services\Api\Slots;

use Fuel\Core\Input;
use Repositories\SlotWhitelistIpRepository;

class SecurityService
{
    private SlotWhitelistIpRepository $slotWhitelistIpRepository;

    public function __construct(SlotWhitelistIpRepository $slotWhitelistIpRepository)
    {
        $this->slotWhitelistIpRepository = $slotWhitelistIpRepository;
    }

    public function isCurrentIpAllowed(int $slotProviderId): bool
    {
        $allowedIps = $this->slotWhitelistIpRepository->getAllowedIpsForSlotProvider($slotProviderId);
        $ip = Input::real_ip();

        return in_array($ip, $allowedIps);
    }
}
