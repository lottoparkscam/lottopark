<?php

namespace Modules\Account\Balance;

/**
 * Charges user account from his regular balance.
 */
class RegularBalance extends AbstractBalance implements BalanceContract
{
    public const COLUMN_NAME = 'balance';

    public function source(): string
    {
        return self::COLUMN_NAME;
    }
}
