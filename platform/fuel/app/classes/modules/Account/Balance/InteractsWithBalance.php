<?php

namespace Modules\Account\Balance;

interface InteractsWithBalance
{
    public function setBalanceStrategy(BalanceContract $balance): void;
}
