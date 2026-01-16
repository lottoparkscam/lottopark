<?php

declare(strict_types=1);

namespace Exceptions\PaymentMethod;

use Exception;

class WhitelabelPaymentMethodCurrencyNotAllowedException extends Exception
{
    public function __construct()
    {
        parent::__construct('User selected currency for gateway that does not allow to specify custom currency');
    }
}
