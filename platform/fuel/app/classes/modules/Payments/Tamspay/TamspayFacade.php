<?php

namespace Modules\Payments\Tamspay;

use Modules\Payments\AbstractPaymentFacade;
use Modules\Payments\PaymentAcceptorDecorator;
use Modules\Payments\PaymentFacadeContract;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Wrappers\Decorators\ConfigContract;

final class TamspayFacade extends AbstractPaymentFacade implements PaymentFacadeContract
{
    private TamspayCheckoutUrlHandler $checkoutUrlHandler;

    public function __construct(
        ConfigContract $config,
        PaymentAcceptorDecorator $acceptorDecorator,
        TamspayCheckoutUrlHandler $checkoutUrlHandler,
        TransactionRepository $transactionRepository,
        WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository
    ) {
        $this->checkoutUrlHandler = $checkoutUrlHandler;
        parent::__construct($config, $acceptorDecorator, $transactionRepository, $whitelabelPaymentMethodRepository);
    }

    public function requestCheckoutUrl(string $transactionPrefixedToken, int $whitelabelId, float $amount, string $currencyCode, ...$args): string
    {
        return $this->checkoutUrlHandler->processPayment($transactionPrefixedToken, $whitelabelId);
    }

    public function getCustomizableOptions(): array
    {
        return [
            'tamspay_sid',
        ];
    }
}
