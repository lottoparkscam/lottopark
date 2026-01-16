<?php

namespace Tests\Unit\Classes\Modules\Account\Balance;

use Helpers_General;

class RegularBalanceTest extends AbstractBalanceTest
{
    protected const SOURCE = 'balance';
    protected const PAYMENT_TYPE = Helpers_General::PAYMENT_TYPE_BALANCE;
    protected const AMOUNT_FIELD = 'amount';
}
