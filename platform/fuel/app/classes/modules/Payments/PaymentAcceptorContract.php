<?php

namespace Modules\Payments;

interface PaymentAcceptorContract
{
    public function accept(string $transactionPrefixedToken, int $whitelabelId): void;
}
