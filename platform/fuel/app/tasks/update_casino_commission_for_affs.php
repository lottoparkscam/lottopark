<?php

namespace Fuel\Tasks;

use Container;
use Services\Api\Slots\UpdateDailyCommissionUsdForAffiliateUsersService;

final class Update_Casino_Commission_For_Affs
{
    private UpdateDailyCommissionUsdForAffiliateUsersService $updateDailyCasinoCommissionService;
    private bool $skipDate;

    public function __construct($skipDate = false)
    {
        $this->updateDailyCasinoCommissionService = Container::get(UpdateDailyCommissionUsdForAffiliateUsersService::class);
        $this->skipDate = $skipDate;
    }

    public function run()
    {
        $this->updateDailyCasinoCommissionService->updateCommissionsByTier(1, $this->skipDate);
        $this->updateDailyCasinoCommissionService->updateCommissionsByTier(2, $this->skipDate);
    }
}
