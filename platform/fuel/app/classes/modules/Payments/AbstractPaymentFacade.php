<?php

namespace Modules\Payments;

use Repositories\Orm\TransactionRepository;
use Repositories\Orm\WhitelabelPaymentMethodRepository;
use Throwable;
use Wrappers\Decorators\ConfigContract;

use const ARRAY_FILTER_USE_KEY;

abstract class AbstractPaymentFacade implements PaymentFacadeContract
{
    protected ConfigContract $config;
    protected string $slug;
    private PaymentAcceptorDecorator $acceptorDecorator;
    private TransactionRepository $transactionRepository;
    private WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository;

    public function __construct(
        ConfigContract $config,
        PaymentAcceptorDecorator $acceptorDecorator,
        TransactionRepository $transactionRepository,
        WhitelabelPaymentMethodRepository $whitelabelPaymentMethodRepository
    ) {
        $this->config = $config;
        $this->slug = $this->guessSlug();
        $this->acceptorDecorator = $acceptorDecorator;
        $this->transactionRepository = $transactionRepository;
        $this->whitelabelPaymentMethodRepository = $whitelabelPaymentMethodRepository;
    }

    public function getConfig(array $fields = []): array
    {
        $data = $this->config->get("payments.{$this->slug}");
        if (empty($fields)) {
            return $data;
        }
        return array_filter($data, fn (string $key) => in_array($key, $fields), ARRAY_FILTER_USE_KEY);
    }

    public function getWhitelabelPaymentConfig(int $paymentMethodId, int $whitelabelId): array
    {
        $whitelabelPaymentMethodDataJson = $this->whitelabelPaymentMethodRepository->getDataJsonByPaymentIdAndWhitelabelId($paymentMethodId, $whitelabelId);
        $baseConfig = $this->getConfig();

        if (empty($whitelabelPaymentMethodDataJson)) {
            return $baseConfig;
        }
        $customizableOptions = $this->getCustomizableOptions();
        $dataJson = array_filter($whitelabelPaymentMethodDataJson, fn (string $key) => in_array($key, $customizableOptions), ARRAY_FILTER_USE_KEY);

        return array_merge($baseConfig, $dataJson);
    }

    public function getPaymentStatus(string $transactionPrefixedToken, int $whitelabelId, ...$args): PaymentStatus
    {
        return PaymentStatus::UNSUPPORTED();
    }

    /**
     * @param string $transactionPrefixedToken
     * @param array<string, mixed> $details
     * @throws Throwable
     */
    public function confirmPayment(string $transactionPrefixedToken, int $whitelabelId, array $details = []): void
    {
        $this->acceptorDecorator->confirm($transactionPrefixedToken, $whitelabelId, $details);
    }

    /**
     * @param string $transactionPrefixedToken
     * @throws Throwable
     */
    public function failPayment(string $transactionPrefixedToken, int $whitelabelId): void
    {
        $t = $this->transactionRepository->getByToken($transactionPrefixedToken, $whitelabelId);
        $t->setStatusAsErrorWithTicket();
        $this->transactionRepository->save($t);
    }

    private function guessSlug(): string
    {
        $class = get_called_class();
        $chunks = explode('\\', $class);
        $name = end($chunks);
        $name = strstr($name, 'Facade', true);
        return strtolower($name);
    }
}
