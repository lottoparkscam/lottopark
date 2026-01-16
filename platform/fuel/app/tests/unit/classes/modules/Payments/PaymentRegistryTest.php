<?php

namespace Unit\Modules\Payments;

use Fuel\Tasks\Factory\Utils\Faker;
use Modules\Payments\PaymentRegistry;
use Modules\Payments\PaymentType;
use Test_Unit;
use Wrappers\Decorators\ConfigContract;

class PaymentRegistryTest extends Test_Unit
{
    private ConfigContract $config;

    private PaymentRegistry $s;

    public function setUp(): void
    {
        parent::setUp();

        $this->config = $this->createMock(ConfigContract::class);
        $this->s = new PaymentRegistry($this->config);
    }

    /** @test */
    public function registerPayment__not_supported_method__do_nothing(): void
    {
        // Given
        $list = ['get_list_of_payment_method_classes' => []];
        $expected = $list;

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn([
                'payment1' => $this->getPaymentConfig()
            ]);

        $method = 'not existing one';
        $this->s->registerPayment($method, $list);

        // Then
        $this->assertSame($expected, $list);
    }

    /** @test */
    public function registerPayment_get_list_of_payment_method_classes(): void
    {
        // Given
        $id = 1;
        $form = 'form';
        $paymentConfigs = [
            'payment1' => $this->getPaymentConfig($id, $form),
        ];
        $expectedListAfterTest = [
            $id => $form
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $list = [];
        $this->s->registerPayment('get_list_of_payment_method_classes', $list);

        // Then
        $this->assertSame($expectedListAfterTest, $list);
    }

    /**
     * @test
     * @dataProvider register_payment__same_logic_dataProvider
     * @param string $method
     */
    public function registerPayment_with(string $method): void
    {
        // Given
        $id = 1;
        $form = 'form';
        $paymentConfigs = [
            'payment1' => $this->getPaymentConfig($id, '', '', $form),
        ];
        $expectedListAfterTest = [
            $id => $form
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $list = [];
        $this->s->registerPayment($method, $list);

        // Then
        $this->assertSame($expectedListAfterTest, $list);
    }

    public function register_payment__same_logic_dataProvider(): array
    {
        return [
            'get_list_of_payment_method_classes_for_validation' => ['get_list_of_payment_method_classes_for_validation'],
            'get_list_of_payment_method_classes_validation_special' => ['get_list_of_payment_method_classes_for_validation'],
        ];
    }

    /** @test */
    public function registerPayment_get_list_of_payment_method_classes_for_check_currency_support__currency_check_form_exists(): void
    {
        // Given
        $id = 1;
        $form = 'form';
        $paymentConfigs = [
            'payment1' => $this->getPaymentConfig($id, '', $form),
        ];
        $expectedListAfterTest = [
            $id => $form
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $list = [];
        $this->s->registerPayment('get_list_of_payment_method_classes_for_check_currency_support', $list);

        // Then
        $this->assertSame($expectedListAfterTest, $list);
    }

    /** @test */
    public function registerPayment_get_list_of_payment_method_classes_for_check_currency_support__currency_check_form_not_exists__skips(): void
    {
        // Given

        $config1 = $this->getPaymentConfig();
        $config2 = $config1;

        unset($config1['currency_check_form']);

        $paymentConfigs = [
            'payment1' => $config1,
            'payment2' => $config2
        ];
        $expectedListAfterTest = [
            $config1['id'] => $config2['currency_check_form']
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $list = [];
        $this->s->registerPayment('get_list_of_payment_method_classes_for_check_currency_support', $list);

        // Then
        $this->assertSame($expectedListAfterTest, $list);
    }

    /** @test */
    public function registerPayment_get_all_methods_with_URI(): void
    {
        // Given
        $config = $this->getPaymentConfig();
        $paymentConfigs = [
            'payment1' => $config,
        ];
        $expectedListAfterTest = [
            (string)$config['type'] => [
                $config['id'] => $config['url']
            ]
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $list = [];
        $this->s->registerPayment('get_all_methods_with_URI', $list);

        // Then
        $this->assertSame($expectedListAfterTest, $list);
    }

    /** @test */
    public function registerPayment_get_all_methods_with_URI__no_url_defined_passes_lower_cased_slug(): void
    {
        // Given
        $config = $this->getPaymentConfig();
        unset($config['url']);
        $paymentSlug = 'Payment1';
        $expectedPaymentSlug = 'payment1';
        $paymentConfigs = [
            $paymentSlug => $config,
        ];
        $expectedListAfterTest = [
            (string)$config['type'] => [
                $config['id'] => $expectedPaymentSlug
            ]
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $list = [];
        $this->s->registerPayment('get_all_methods_with_URI', $list);

        // Then
        $this->assertSame($expectedListAfterTest, $list);
    }

    /** @test */
    public function getPaymentSlugs__returns_configured_slugs_without_ignored_keys(): void
    {
        // Given
        $config1 = $this->getPaymentConfig();
        $config2 = $this->getPaymentConfig();
        $config3 = $this->getPaymentConfig();

        $expected = ['payment2', 'payment3'];

        $paymentConfigs = [
            'synchronizer' => $config1,
            'payment2' => $config2,
            'payment3' => $config3,
        ];

        $this->config
            ->expects($this->once())
            ->method('get')
            ->with('payments')
            ->willReturn($paymentConfigs);

        // When
        $actual = $this->s->getPaymentSlugs();

        // Then
        $this->assertSame($expected, $actual);
    }

    private function getPaymentConfig(
        ?int $id = null,
        string $paymentForm = 'payment_form',
        string $currencyCheckForm = 'check_form',
        string $validationForm = 'admin_custom_validation'
    ): array {
        return [
            'id' => $id ?? Faker::forge()->numberBetween(1, 1000),
            'payment_form' => $paymentForm,
            'currency_check_form' => $currencyCheckForm,
            'admin_custom_validation' => $validationForm,
            'type' => PaymentType::OTHER(),
            'url' => Faker::forge()->url()
        ];
    }
}
