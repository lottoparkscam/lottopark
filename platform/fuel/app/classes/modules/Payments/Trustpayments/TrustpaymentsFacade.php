<?php

namespace Modules\Payments\Trustpayments;

use Modules\Payments\AbstractPaymentFacade;
use Modules\Payments\PaymentAcceptorDecorator;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Wrappers\Decorators\ConfigContract;

final class TrustpaymentsFacade extends AbstractPaymentFacade
{
    private TrustpaymentsCheckoutUrlHandler $trustpaymentsCheckoutUrlHandler;

    public function __construct(
        ConfigContract $config,
        PaymentAcceptorDecorator $acceptorDecorator,
        TransactionRepository $transactionRepository,
        TrustpaymentsCheckoutUrlHandler $trustpaymentsCheckoutUrlHandler,
        WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository
    ) {
        $this->trustpaymentsCheckoutUrlHandler = $trustpaymentsCheckoutUrlHandler;
        parent::__construct($config, $acceptorDecorator, $transactionRepository, $whitelabelPaymentMethodRepository);
    }

    public function requestCheckoutUrl(string $transactionPrefixedToken, int $whitelabelId, float $amount, string $currencyCode, ...$args): string
    {
        return $this->trustpaymentsCheckoutUrlHandler->processPayment($transactionPrefixedToken, $whitelabelId);
    }

    public function getCustomizableOptions(): array
    {
        return [
            'trustpayments_sitereference'
        ];
    }
}
