<?php

use DI\ContainerBuilder;
use DI\DependencyException;
use DI\NotFoundException;
use Fuel\Core\Fuel;
use Modules\Payments\PaymentFacadeContract;
use Modules\Payments\PaymentFacadeLocator;

class Container
{
    private static ?DI\Container $instance = null;

    public static function forge(bool $asSingleton = true): DI\Container
    {
        if (!self::$instance || !$asSingleton) {
            self::$instance = self::forgeFresh();
        }
        return self::$instance;
    }

    /**
     * Initialized new Container,
     * without using singleton pattern (as in forge).
     *
     * It's not recommended to use this approach manually, due performance.
     *
     * @return \DI\Container
     * @throws Exception
     */
    public static function forgeFresh(): DI\Container
    {
        $container = new ContainerBuilder();

        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'container-config.php');
        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'lcs-container-config.php');
        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'payments-container-config.php');
        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'mediacle-container-config.php');
        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'view-container-config.php');
        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'alert-container-config.php');
        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'last-steps-container-config.php');
        $container->addDefinitions(__DIR__ . DIRECTORY_SEPARATOR . 'whitelabel-user-oauth-2-service-container-config.php');

        if (Fuel::$env === Fuel::TEST) {
            $container->addDefinitions(__DIR__ . '/../tests/fixtures/fixtures-container.php');
        }

        return $container->build();
    }

    /**
     * @template T
     * @param class-string<T> $full_class_name
     * @return T
     *
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function get(string $full_class_name)
    {
        return self::forge()->get($full_class_name);
    }

    /**
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function make(string $name, array $parameters = [])
    {
        return self::forge()->make($name, $parameters);
    }

    /**
     * @param string $paymentSlug
     * @return PaymentFacadeContract
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function getPaymentFacade(string $paymentSlug): PaymentFacadeContract
    {
        $locator = self::get(PaymentFacadeLocator::class);
        return $locator->getBySlug($paymentSlug);
    }

    /**
     * @param int $paymentId
     * @return PaymentFacadeContract
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function getPaymentFacadeById(int $paymentId): PaymentFacadeContract
    {
        $locator = self::get(PaymentFacadeLocator::class);
        return $locator->getById($paymentId);
    }

    public function __clone()
    {
        return null;
    }
}
