<?php

declare(strict_types=1);

namespace Services;

use Helpers_General;
use Repositories\WhitelabelPaymentMethodCustomizeRepository;

class PaymentMethodCustomizationService
{
    private WhitelabelPaymentMethodCustomizeRepository $whitelabelPaymentMethodCustomizeRepository;

    public function __construct(
        WhitelabelPaymentMethodCustomizeRepository $whitelabelPaymentMethodCustomizeRepository
    ) {
        $this->whitelabelPaymentMethodCustomizeRepository = $whitelabelPaymentMethodCustomizeRepository;
    }

    public function getWhitelabelPaymentMethodCustomizeData(int $whitelabelPaymentMethodId, int $languageId): array
    {
        $paymentMethodCustomize = $this->whitelabelPaymentMethodCustomizeRepository->getFullData(
            $whitelabelPaymentMethodId,
            $languageId
        );

        if (empty($paymentMethodCustomize)) {
            return $this->whitelabelPaymentMethodCustomizeRepository->getFullData(
                $whitelabelPaymentMethodId,
                Helpers_General::get_default_language_id()
            );
        }

        return $paymentMethodCustomize;
    }
}
