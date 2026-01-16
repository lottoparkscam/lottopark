<?php

namespace Modules\Payments\Jeton;

use Exception;
use Modules\Payments\AbstractPaymentFacade;
use Modules\Payments\PaymentAcceptorDecorator;
use Modules\Payments\PaymentFacadeContract;
use Modules\Payments\PaymentStatus;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Wrappers\Decorators\ConfigContract;

/**
 * Class JetonFacade
 * Encapsulates Jeton's payment flow logic.
 */
class JetonFacade extends AbstractPaymentFacade implements PaymentFacadeContract
{
    private JetonCheckoutUrlHandler $checkoutUrlHandler;
    private JetonTransactionHandler $transactionHandler;

    public function __construct(
        JetonCheckoutUrlHandler $checkoutUrlHandler,
        JetonTransactionHandler $transactionHandler,
        ConfigContract $config,
        PaymentAcceptorDecorator $acceptorDecorator,
        TransactionRepository $transactionRepository,
        WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository
    ) {
        $this->checkoutUrlHandler = $checkoutUrlHandler;
        $this->transactionHandler = $transactionHandler;
        parent::__construct($config, $acceptorDecorator, $transactionRepository, $whitelabelPaymentMethodRepository);
    }

    /**
     * @param string $transactionPrefixedToken
     * @param float $amount
     * @param string $currencyCode
     * @param mixed ...$args $currency = 'EN'
     *
     * @return string
     *
     * @throws Exception
     */
    public function requestCheckoutUrl(string $transactionPrefixedToken, int $whitelabelId, float $amount, string $currencyCode, ...$args): string
    {
        [$language] = $args;

        return $this->checkoutUrlHandler->processPayment(
            $transactionPrefixedToken,
            $whitelabelId,
            $amount,
            $currencyCode,
            $language
        );
    }

    public function getPaymentStatus(string $transactionPrefixedToken, int $whitelabelId, ...$args): PaymentStatus
    {
        return $this->transactionHandler->getPaymentStatus($transactionPrefixedToken, $whitelabelId);
    }

    public function getCustomizableOptions(array $data = []): array
    {
        return ['jeton_base_url', 'jeton_api_key'];
    }
}
