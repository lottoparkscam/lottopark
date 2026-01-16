<?php

declare(strict_types=1);

namespace Exceptions\PaymentMethod;

use Exception;

class WhitelabelPaymentMethodCurrencyNotSupportedException extends Exception
{

    public function __construct(string $currencyCode)
    {
        parent::__construct(sprintf(
            'The selected currency "%s" is not supported. Someone is tampering with payment form!',
            $currencyCode
        ));
    }
}
