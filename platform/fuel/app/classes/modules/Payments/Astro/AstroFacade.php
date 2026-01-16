<?php

namespace Modules\Payments\Astro;

use GuzzleHttp\Exception\GuzzleException;
use Modules\Payments\AbstractPaymentFacade;
use Modules\Payments\PaymentAcceptorDecorator;
use Modules\Payments\PaymentFacadeContract;
use Modules\Payments\PaymentStatus;
use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Throwable;
use Wrappers\Decorators\ConfigContract;

class AstroFacade extends AbstractPaymentFacade implements PaymentFacadeContract
{
    private AstroCheckoutUrlHandler $checkoutUrlHandler;
    private AstroStatusHandler $statusHandler;

    public function __construct(
        ConfigContract $config,
        PaymentAcceptorDecorator $acceptorDecorator,
        TransactionRepository $transactionRepository,
        AstroCheckoutUrlHandler $depositClient,
        AstroStatusHandler $statusHandler,
        WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository
    ) {
        parent::__construct($config, $acceptorDecorator, $transactionRepository, $whitelabelPaymentMethodRepository);
        $this->checkoutUrlHandler = $depositClient;
        $this->statusHandler = $statusHandler;
    }

    /**
     * @param string $transactionPrefixedToken
     * @param float $amount
     * @param string $currencyCode
     * @param mixed ...$args - country
     * @return string
     * @throws GuzzleException
     * @throws Throwable
     */
    public function requestCheckoutUrl(string $transactionPrefixedToken, int $whitelabelId, float $amount, string $currencyCode, ...$args): string
    {
        $country = $args[0] ?? null;

        return $this->checkoutUrlHandler->processPayment(
            $transactionPrefixedToken,
            $whitelabelId,
            $amount,
            $currencyCode,
            $country
        );
    }

    public function getPaymentStatus(string $transactionPrefixedToken, int $whitelabelId, ...$args): PaymentStatus
    {
        return $this->statusHandler->getStatus($transactionPrefixedToken, $whitelabelId);
    }

    public function getCustomizableOptions(): array
    {
        return [
            'astro_base_url', 'astro_api_key', 'astro_secret_key', 'astro_default_country'
        ];
    }
}
