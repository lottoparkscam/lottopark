<?php

namespace Feature\Modules\Payments;

use Container;
use Fuel\Core\Str;
use Modules\Payments\Jeton\JetonFacade;
use Modules\Payments\PaymentFacadeContract;
use Modules\Payments\PaymentLogger;
use Modules\Payments\PaymentRegistry;
use ReflectionClass;
use Services\Shared\Logger\LoggerContract;
use Test_Feature;
use Wrappers\Decorators\ConfigContract;

class ContainerTest extends Test_Feature
{
    /** @test */
    public function get_by_alias__jeton_payments_facade(): void
    {
        // Given
        $paymentSlug = 'jeton';
        $alias = sprintf('payments.%s.facade', $paymentSlug);

        $expectedContract = PaymentFacadeContract::class;
        $expectedConcreteInstance = JetonFacade::class;

        // When
        $actual = Container::get($alias);

        // Then
        $this->assertInstanceOf($expectedConcreteInstance, $actual);
        $this->assertInstanceOf($expectedContract, $actual);
    }

    /** @test */
    public function payments__configs__check_all_injected_are_config_contract(): void
    {
        // Given
        $expectedClass = ConfigContract::class;
        $payments = $this->getPayments();

        foreach ($payments as $slug) {
            $actual = Container::getPaymentFacade($slug);
            $this->checkClassInjections($actual, ConfigContract::class, $expectedClass);
        }
    }

    /** @test */
    public function payments__loggers__check_all_injected_are_decorated(): void
    {
        // Given
        $expectedClass = PaymentLogger::class;
        $payments = $this->getPayments();

        foreach ($payments as $slug) {
            $actual = Container::getPaymentFacade($slug);
            $this->checkClassInjections($actual, LoggerContract::class, $expectedClass);
        }
    }

    private function getPayments(): array
    {
        $payments = Container::get(ConfigContract::class)->get('payments');
        $payments = array_filter($payments, fn (string $key) => !in_array($key, PaymentRegistry::IGNORED_KEYS), ARRAY_FILTER_USE_KEY);
        return array_keys($payments);
    }

    private function checkClassInjections(object $actual, string $contract, string $expectedConcrete): void
    {
        // When
        $reflection = new ReflectionClass($actual);
        $props = $reflection->getProperties();

        foreach ($props as $p) {
            $p->setAccessible(true);
            $v = $p->getValue($actual);

            if (!is_object($v)) {
                continue;
            }

            $actualClass = get_class($actual);

            $inPaymentsNameSpace = Str::strpos($actualClass, '\Payments\\') !== false;

            if ($v instanceof $contract) {
                if ($actualClass !== $expectedConcrete && $inPaymentsNameSpace) {
                    $this->assertInstanceOf($expectedConcrete, $v, sprintf('Property %s in class %s has no %s class injected..', $p->name, $actualClass, $expectedConcrete));
                }
            }

            if (!$inPaymentsNameSpace) {
                continue;
            }

            $this->checkClassInjections($v, $contract, $expectedConcrete);
        }
    }

    /**
     * @test
     * @dataProvider domain__contains_config_base_url_dataProvider
     * @param string $baseUrl
     * @param string $expected
     */
    public function domain__contains_config_base_url__in_expected_format(string $baseUrl, string $expected): void
    {
        // Given
        $_SERVER['HTTP_HOST'] = parse_url($baseUrl, PHP_URL_HOST);
        $mock = $this->createMock(ConfigContract::class);

        $newContainer = Container::forgeFresh();
        $newContainer->set(ConfigContract::class, $mock);

        // When
        $actual = $newContainer->get('domain');

        // Then
        $this->assertSame($expected, $actual);
    }

    public function domain__contains_config_base_url_dataProvider(): array
    {
        return [
            'full base url with www' => ['https://www.lottopark.loc/', 'lottopark.loc'],
            'full base url' => ['https://lottopark.loc/', 'lottopark.loc'],
        ];
    }
}
