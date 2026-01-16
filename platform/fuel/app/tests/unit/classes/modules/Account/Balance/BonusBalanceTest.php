<?php

namespace Tests\Unit\Classes\Modules\Account\Balance;

use Helpers_General;

class BonusBalanceTest extends AbstractBalanceTest
{
    protected const SOURCE = 'bonus_balance';
    protected const PAYMENT_TYPE = Helpers_General::PAYMENT_TYPE_BONUS_BALANCE;
    protected const AMOUNT_FIELD = 'bonus_amount';
}
