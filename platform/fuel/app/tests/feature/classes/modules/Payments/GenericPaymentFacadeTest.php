<?php

namespace Feature\Modules\Payments;

use Container;
use Modules\Payments\PaymentFacadeLocator;
use Modules\Payments\PaymentRegistry;
use RuntimeException;
use Test_Feature;
use Wrappers\Decorators\ConfigContract;

use const ARRAY_FILTER_USE_KEY;

class GenericPaymentFacadeTest extends Test_Feature
{
    private ConfigContract $baseConfig;
    private PaymentFacadeLocator $locator;

    public function setUp(): void
    {
        parent::setUp();
        $this->baseConfig = Container::get(ConfigContract::class);
        $this->locator = Container::get(PaymentFacadeLocator::class);
    }

    /**
     * @test
     * @dataProvider facade_dataProvider
     * @param string $slug
     */
    public function getConfig__no_fields_given__returns_all_options(string $slug): void
    {
        // Given
        $expected = $this->baseConfig->get("payments.$slug");

        // When
        $facade = $this->locator->getBySlug($slug);
        $customizable = $facade->getCustomizableOptions();
        $actual = $facade->getConfig();

        // Then
        foreach ($expected as $key => $value) {
            if (in_array($key, $customizable)) {
                continue;
            }
            $this->assertSame($expected[$key], $actual[$key]);
        }
    }

    /**
     * @test
     * @dataProvider facade_dataProvider
     * @param string $slug
     */
    public function getConfig__concrete_fields_given__returns_only_customizable_options(string $slug): void
    {
        // Given
        $config = $this->baseConfig->get("payments.$slug");

        // Given & When
        $facade = $this->locator->getBySlug($slug);
        $options = $facade->getCustomizableOptions();
        $actual = $facade->getConfig($options);

        $expected = array_keys(array_filter($config, fn (string $key) => in_array($key, $options), ARRAY_FILTER_USE_KEY));
        $actual = array_keys($actual);

        // Then
        $this->assertSame($expected, $actual);
    }

    /**
     * @test
     * @dataProvider facade_dataProvider
     * @param string $slug
     */
    public function getCustomizableOptions__options_exists_in_config(string $slug): void
    {
        // Given
        $config = $this->baseConfig->get("payments.$slug");

        // Given & When
        $facade = $this->locator->getBySlug($slug);
        $options = $facade->getCustomizableOptions();

        // Then
        foreach ($options as $key) {
            $this->assertArrayHasKey($key, $config, "Unable to find option $key in /config/payments.$slug");
        }
    }

    /**
     * @test
     * @dataProvider facade_dataProvider
     * @param string $slug
     */
    public function getCustomizableOptions__each_options_starts_with_payment_slug(string $slug): void
    {
        // Given
        $config = $this->baseConfig->get("payments.$slug");

        // Given & When
        $facade = $this->locator->getBySlug($slug);
        $options = $facade->getCustomizableOptions();

        // Then
        foreach ($options as $key) {
            $this->assertStringStartsWith($slug, $key);
        }
    }

    /**
     * @test
     * @dataProvider facade_dataProvider
     * @param string $slug
     */
    public function requestCheckoutUrl__not_existing_transaction__throws_exception(string $slug): void
    {
        // Expect
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transaction #ASD123 not found!');

        // Given
        $token = 'ASD123';
        $whitelabelId = 1;

        // When
        $facade = $this->locator->getBySlug($slug);
        $facade->requestCheckoutUrl($token, $whitelabelId, 20, 'USD', 'EN', 'arg1');
    }

    public function facade_dataProvider(): array
    {
        $registry = Container::get(PaymentRegistry::class);
        $payments = $registry->getPaymentSlugs();
        $provider = [];

        foreach ($payments as $slug) {
            $provider[$slug] = [$slug];
        }

        return $provider;
    }
}
