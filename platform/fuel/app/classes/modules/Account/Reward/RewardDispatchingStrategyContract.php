<?php

namespace Modules\Account\Reward;

use Models\WhitelabelRaffleTicketLine;

interface RewardDispatchingStrategyContract
{
    public function dispatchPrize(WhitelabelRaffleTicketLine $line): void;
}
