<?php

namespace Services\Api\Balance;

use Models\Whitelabel;
use Models\WhitelabelUser;
use Repositories\Orm\WhitelabelUserBalanceLogRepository;

class ChargeService
{
    private Whitelabel $whitelabel;

    private WhitelabelUserBalanceLogRepository $whitelabelUserBalanceLogRepository;

    public function __construct(WhitelabelUserBalanceLogRepository $whitelabelUserBalanceLogRepository)
    {
        $this->whitelabelUserBalanceLogRepository = $whitelabelUserBalanceLogRepository;
    }

    public function setWhitelabel(Whitelabel $whitelabel): void
    {
        $this->whitelabel = $whitelabel;
    }

    public function isBalanceAmountLimitExceeded(float $amount, WhitelabelUser $whitelabelUser): bool
    {
        $maxDailyBonusAmountPerUser = $this->whitelabel->maxDailyBalanceChangePerUser;
        $usedBonusAmountPerUserToday = $this->whitelabelUserBalanceLogRepository->calculateChangedBonusBalancePerUser($whitelabelUser);
        $leftBonusAmount = $maxDailyBonusAmountPerUser - $usedBonusAmountPerUserToday;

        return $amount > $leftBonusAmount;
    }
}
