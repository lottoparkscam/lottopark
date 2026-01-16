<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Services;

use Exception;
use Helpers_Currency;
use Helpers_General;
use Models\WhitelabelPaymentMethod;
use PaymentMethodService;
use Tests\Fixtures\WhitelabelPaymentMethodCurrencyFixture;
use Tests\Fixtures\WhitelabelPaymentMethodFixture;
use Test_Feature;

final class PaymentMethodServiceTest extends Test_Feature
{
    private WhitelabelPaymentMethodFixture $whitelabelPaymentMethodFixture;
    private WhitelabelPaymentMethodCurrencyFixture $whitelabelPaymentMethodCurrencyFixture;
    private PaymentMethodService $paymentMethodServiceUnderTest;

    private WhitelabelPaymentMethod $whitelabelPaymentMethod;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelPaymentMethodFixture = $this->container->get(WhitelabelPaymentMethodFixture::class);
        $this->whitelabelPaymentMethodCurrencyFixture = $this->container->get(WhitelabelPaymentMethodCurrencyFixture::class);
        $this->paymentMethodServiceUnderTest = $this->container->get(PaymentMethodService::class);

        $this->whitelabelPaymentMethod = $this->whitelabelPaymentMethodFixture
            ->with('basic')
            ->createOne([
                'language_id' => Helpers_General::get_default_language_id(),
                'name' => 'Test Payment',
            ]);
    }

    /**
     * @test
     */
    public function isCurrencyValidForWhitelabelPaymentMethod_WhitelabelPaymentMethodIsNotSet(): void
    {
        $this->expectExceptionMessage('WhitelabelPaymentMethod is not set.');

        $this->paymentMethodServiceUnderTest->isCurrencySupportedForWhitelabelPaymentMethod();
    }

    /**
     * @test
     */
    public function isCurrencyValidForWhitelabelPaymentMethod_InvalidCurrency(): void
    {
        $this->expectExceptionMessage('The selected currency "XXX" is not supported. Someone is tampering with payment form!');

        $this->paymentMethodServiceUnderTest->setCurrencyByCode('XXX');
    }

    /**
     * @test
     */
    public function isCurrencyValidForWhitelabelPaymentMethod_CurrencyIsNotSet(): void
    {
        $this->expectExceptionMessage('Currency is not set.');

        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = false;

        $this->paymentMethodServiceUnderTest->setWhitelabelPaymentMethod($this->whitelabelPaymentMethod->id);
        $this->paymentMethodServiceUnderTest->isCurrencySupportedForWhitelabelPaymentMethod();
    }

    /**
     * @test
     */
    public function getEnabledCurrenciesForWhitelabelPaymentMethod(): void
    {
        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = true;

        $this->addCurrencyForWhitelabelPaymentMethod('PHP', false);
        $this->addCurrencyForWhitelabelPaymentMethod('USD');
        $this->addCurrencyForWhitelabelPaymentMethod('PLN');
        $this->addCurrencyForWhitelabelPaymentMethod('EUR');

        $this->paymentMethodServiceUnderTest->loadWhitelabelEnabledPaymentMethodCurrencies([
            $this->whitelabelPaymentMethod->id
        ]);

        $actual = $this->paymentMethodServiceUnderTest->getEnabledCurrenciesForWhitelabelPaymentMethod($this->whitelabelPaymentMethod->id);

        $this->assertCount(3, $actual);
    }

    /**
     * @test
     */
    public function isCurrencyDefaultForWhitelabelPaymentMethod(): void
    {
        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = true;

        $this->addCurrencyForWhitelabelPaymentMethod('USD');
        $this->addCurrencyForWhitelabelPaymentMethod('PLN', true, true);
        $this->addCurrencyForWhitelabelPaymentMethod('EUR');

        $this->paymentMethodServiceUnderTest->loadWhitelabelEnabledPaymentMethodCurrencies([
            $this->whitelabelPaymentMethod->id
        ]);

        $actual = $this->paymentMethodServiceUnderTest->getDefaultCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod->id);

        $this->assertNotNull($actual);
        $this->assertTrue($actual->isDefault);
        $this->assertSame('PLN', $actual->currency->code);
    }

    public function allowUserToSelectCurrencyDataProvider(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    /**
     * @test
     * @dataProvider allowUserToSelectCurrencyDataProvider
     * @throws Exception
     */
    public function isUserAllowedToSelectPaymentCurrency(bool $userAllowed, bool $expected): void
    {
        $whitelabelPaymentMethod = $this->whitelabelPaymentMethodFixture
            ->with('basic')
            ->createOne([
                'language_id' => Helpers_General::get_default_language_id(),
                'name' => 'Test Payment',
                'allow_user_to_select_currency' => $userAllowed
            ]);

        $this->paymentMethodServiceUnderTest->setWhitelabelPaymentMethod($whitelabelPaymentMethod->id);

        $this->assertSame($expected, $this->paymentMethodServiceUnderTest->isUserAllowedToSelectPaymentCurrency());
    }

    public function paymentMethodCurrencyDataProvider(): array
    {
        /**
         * currency code  => enabled true/false
         */
        return [
            'currency is not added' => [['USD' => true, 'EUR' => true, 'PLN' => true], false],
            'currency added but disabled' => [['USD' => true, 'EUR' => true, 'PHP' => false], false],
            'currency added and enabled' => [['USD' => true, 'EUR' => true, 'PHP' => true], true],
        ];
    }

    /**
     * @test
     * @dataProvider paymentMethodCurrencyDataProvider
     * @throws Exception
     */
    public function isCurrencySupportedForWhitelabelPaymentMethod(
        array $whitelabelPaymentMethodCurrencies,
        bool $expected
    ): void {
        $selectedCurrency = Helpers_Currency::findCurrencyByCode('PHP');

        $selectedCurrencyId = (int) $selectedCurrency['id'];
        $selectedCurrencyCode = $selectedCurrency['code'];

        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = true;

        foreach ($whitelabelPaymentMethodCurrencies as $currencyCode => $enabled) {
            $this->addCurrencyForWhitelabelPaymentMethod(
                $currencyCode,
                $enabled
            );
        }

        $this->paymentMethodServiceUnderTest->setWhitelabelPaymentMethod($this->whitelabelPaymentMethod->id);
        $this->paymentMethodServiceUnderTest->setCurrencyByCode($selectedCurrencyCode);

        $actual = $this->paymentMethodServiceUnderTest->isCurrencySupportedForWhitelabelPaymentMethod();

        $this->assertSame($expected, $actual);
        $this->assertSame($selectedCurrencyId, $this->paymentMethodServiceUnderTest->getCurrencyId());
    }

    private function addCurrencyForWhitelabelPaymentMethod(
        string $currencyCode,
        bool $enabled = true,
        bool $default = false
    ): void {
        $this->whitelabelPaymentMethodCurrencyFixture->createOneForWhitelabelPaymentMethod(
            $this->whitelabelPaymentMethod,
            $currencyCode,
            $enabled,
            $default
        );
    }
}
