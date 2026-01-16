<?php

declare(strict_types=1);

namespace Traits\Payments;

use Container;
use PaymentMethodService;
use Exception;
use Exceptions\PaymentMethod\WhitelabelPaymentMethodCurrencyNotAllowedException;
use Exceptions\PaymentMethod\WhitelabelPaymentMethodCurrencyNotSupportedException;

trait PaymentMethodTrait
{

    /**
     * If user specified a currency to pay in, do series of checks:
     * - to ensure that custom currencies are enabled for this payment method
     * - to ensure that valid currency was selected
     * - recalculate amount into selected currency
     *
     * @throws Exception
     */
    public function selectUserPaymentCurrency(string $userSelectedCurrency): PaymentMethodService
    {
        $paymentMethodService = Container::get(PaymentMethodService::class);
        $paymentMethodService->setWhitelabelPaymentMethod($this->whitelabel_payment_method_id);

        // Some payments allow user to select currency, then overwrite payment values for payment sender
        if (!$paymentMethodService->isUserAllowedToSelectPaymentCurrency()) {
            throw new WhitelabelPaymentMethodCurrencyNotAllowedException();
        }

        $paymentMethodService->setCurrencyByCode($userSelectedCurrency);

        // Is user provided currency valid for whitelabel payment method and is it enabled
        if (!$paymentMethodService->isCurrencySupportedForWhitelabelPaymentMethod()) {
            throw new WhitelabelPaymentMethodCurrencyNotSupportedException(
                $paymentMethodService->getCurrencyCode()
            );
        }

        return $paymentMethodService;
    }
}
