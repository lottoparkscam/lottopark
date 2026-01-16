<?php

declare(strict_types=1);

namespace Tests\E2E\Classes\Forms;

use Forms_Wordpress_Lottery_Basket;
use Models\WhitelabelPaymentMethod;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelPaymentMethodFixture;
use Tests\Fixtures\WhitelabelPaymentMethodCurrencyFixture;
use Exceptions\PaymentMethod\WhitelabelPaymentMethodCurrencyNotAllowedException;
use Exceptions\PaymentMethod\WhitelabelPaymentMethodCurrencyNotSupportedException;
use Helpers_General;
use Test_Feature;

final class WordpressLotteryBasketTest extends Test_Feature
{
    private WhitelabelPaymentMethodFixture $whitelabelPaymentMethodFixture;
    private WhitelabelPaymentMethodCurrencyFixture $whitelabelPaymentMethodCurrencyFixture;

    private WhitelabelPaymentMethod $whitelabelPaymentMethod;

    public function setUp(): void
    {
        parent::setUp();

        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelPaymentMethodFixture = $this->container->get(WhitelabelPaymentMethodFixture::class);
        $this->whitelabelPaymentMethodCurrencyFixture = $this->container->get(WhitelabelPaymentMethodCurrencyFixture::class);

        $this->whitelabel = $this->whitelabelFixture->createOne();

        $this->whitelabelPaymentMethod = $this->whitelabelPaymentMethodFixture
            ->with('basic')
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'name' => 'Test Payment',
                'payment_method_id' => 1,
                'language_id' => Helpers_General::get_default_language_id(),
            ]);
    }

    /**
     * @test
     */
    public function selectUserPaymentCurrency_UserNotAllowedToSelectCurrency(): void
    {
        $this->expectException(WhitelabelPaymentMethodCurrencyNotAllowedException::class);
        $this->expectExceptionMessage('User selected currency for gateway that does not allow to specify custom currency');

        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = false;

        $lotteryBasketForm = $this->getLotteryBasketFormInstance(
            [
                'payment' => [
                    'userSelectedCurrency' => 'PLN'
                ],
            ]
        );

        $lotteryBasketForm->getUserSelectedPaymentCurrencyTab();
    }

    /**
     * @test
     */
    public function selectUserPaymentCurrency_SelectedCurrencyIsNotSupported(): void
    {
        $userSelectedCurrency = 'PLN';

        $this->expectException(WhitelabelPaymentMethodCurrencyNotSupportedException::class);
        $this->expectExceptionMessage(sprintf(
            'The selected currency "%s" is not supported. Someone is tampering with payment form!',
            $userSelectedCurrency
        ));

        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = true;

        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'USD');
        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'EUR');

        $lotteryBasketForm = $this->getLotteryBasketFormInstance(
            [
                'payment' => [
                    'userSelectedCurrency' => $userSelectedCurrency
                ],
            ]
        );

        $lotteryBasketForm->getUserSelectedPaymentCurrencyTab();
    }

    /**
     * @test
     */
    public function selectUserPaymentCurrency_InvalidCurrency(): void
    {
        $this->expectExceptionMessage(
            'The selected currency "XXX" is not supported. Someone is tampering with payment form!',
        );

        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = true;

        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'USD');
        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'EUR');
        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'PLN');

        $lotteryBasketForm = $this->getLotteryBasketFormInstance(
            [
                'payment' => [
                    'userSelectedCurrency' => 'XXX'
                ],
            ]
        );

        $lotteryBasketForm->getUserSelectedPaymentCurrencyTab();
    }

    /**
     * @test
     */
    public function selectUserPaymentCurrency_ShouldReturnCorrectCurrency(): void
    {
        $this->whitelabelPaymentMethod->allowUserToSelectCurrency = true;

        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'USD');
        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'EUR');
        $this->addCurrencyForWhitelabelPaymentMethod($this->whitelabelPaymentMethod, 'PLN');

        $lotteryBasketForm = $this->getLotteryBasketFormInstance(
            [
                'payment' => [
                    'userSelectedCurrency' => 'PLN'
                ],
            ]
        );

        $currencyTab = $lotteryBasketForm->getUserSelectedPaymentCurrencyTab();

        $this->assertNotEmpty($currencyTab);
        $this->assertSame('PLN', $currencyTab['code']);
    }

    private function getLotteryBasketFormInstance(array $inputPost): Forms_Wordpress_Lottery_Basket {
        return new Forms_Wordpress_Lottery_Basket(
            $this->whitelabel->to_array(),
            [],
            [],
            [],
            Helpers_General::PAYMENT_TYPE_OTHER,
            $this->whitelabelPaymentMethod->payment_method_id,
            $this->whitelabelPaymentMethod->id,
            [],
            $inputPost
        );
    }

    private function addCurrencyForWhitelabelPaymentMethod(
        WhitelabelPaymentMethod $whitelabelPaymentMethod,
        string $currencyCode,
        bool $enabled = true,
        bool $default = false
    ): void {
        $this->whitelabelPaymentMethodCurrencyFixture->createOneForWhitelabelPaymentMethod(
            $whitelabelPaymentMethod,
            $currencyCode,
            $enabled,
            $default
        );
    }
}
