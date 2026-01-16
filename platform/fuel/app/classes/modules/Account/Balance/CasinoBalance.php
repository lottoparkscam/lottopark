<?php

namespace Modules\Account\Balance;

class CasinoBalance extends AbstractBalance implements BalanceContract
{
    public function source(): string
    {
        return 'casino_balance';
    }
}
