<?php

namespace Modules\Payments;

use DI\NotFoundException;
use Psr\Container\ContainerInterface;
use Wrappers\Decorators\ConfigContract;

use const ARRAY_FILTER_USE_BOTH;

class PaymentFacadeLocator
{
    private ContainerInterface $container;
    private ConfigContract $config;

    public function __construct(ContainerInterface $container, ConfigContract $config)
    {
        $this->container = $container;
        $this->config = $config;
    }

    public function getAlias(string $slug): string
    {
        return sprintf('payments.%s.facade', strtolower($slug));
    }

    /**
     * Determines if provided id/slug facade exsists.
     * It's not the best approach, but we have very weird code base in few places,
     * and it's cumbersome to refactor all these places..
     *
     * Below getBySlug / id methods are more explicits.
     *
     * @param mixed $idOrSlug
     * @return bool
     */
    public function hasFacade(mixed $idOrSlug = null): bool
    {
        if (empty($idOrSlug)) {
            return false;
        }

        try {
            if (is_numeric($idOrSlug)) {
                return !empty($this->getById((int)$idOrSlug));
            }
            return !empty($this->getBySlug($idOrSlug));
        } catch (NotFoundException $exception) {
            return false;
        }
    }

    /**
     * @param string $slug
     * @return PaymentFacadeContract
     *
     * throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function getBySlug(string $slug): PaymentFacadeContract
    {
        return $this->container->get($this->getAlias($slug));
    }

    /**
     * @param int $paymentId
     * @return PaymentFacadeContract
     *
     * throws NotFoundExceptionInterface  No entry was found for **this** identifier.
     * throws ContainerExceptionInterface Error while retrieving the entry.
     */
    public function getById(int $paymentId): PaymentFacadeContract
    {
        $paymentConfig = array_filter(
            $this->config->get('payments'),
            fn (array $v, string $key) => isset($v['id']) && $v['id'] == $paymentId,
            ARRAY_FILTER_USE_BOTH
        );
        if (empty($paymentConfig)) {
            throw new NotFoundException("Unable to find payment by id #$paymentId in /payments/config.php");
        }
        $paymentConfig = reset($paymentConfig);
        $slug = $paymentConfig['slug'];
        return $this->container->get($this->getAlias($slug));
    }
}
