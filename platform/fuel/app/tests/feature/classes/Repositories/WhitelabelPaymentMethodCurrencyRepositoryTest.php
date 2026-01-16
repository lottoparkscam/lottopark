<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Repositories;

use Models\Currency;
use Models\Whitelabel;
use Models\WhitelabelPaymentMethod;
use Repositories\Orm\CurrencyRepository;
use Repositories\Orm\WhitelabelPaymentMethodCurrencyRepository;
use Tests\Fixtures\WhitelabelFixture;
use Tests\Fixtures\WhitelabelPaymentMethodCurrencyFixture;
use Tests\Fixtures\WhitelabelPaymentMethodFixture;
use Helpers_General;
use Test_Feature;

final class WhitelabelPaymentMethodCurrencyRepositoryTest extends Test_Feature
{
    private Whitelabel $whitelabel;

    private WhitelabelFixture $whitelabelFixture;
    private WhitelabelPaymentMethodFixture $whitelabelPaymentMethodFixture;
    private WhitelabelPaymentMethodCurrencyFixture $whitelabelPaymentMethodCurrencyFixture;

    private CurrencyRepository $currencyRepository;
    private WhitelabelPaymentMethodCurrencyRepository $whitelabelPaymentMethodCurrencyRepositoryUnderTest;

    private int $defaultLanguageId;

    public function setUp(): void
    {
        parent::setUp();

        $this->whitelabelFixture = $this->container->get(WhitelabelFixture::class);
        $this->whitelabelPaymentMethodFixture = $this->container->get(WhitelabelPaymentMethodFixture::class);
        $this->whitelabelPaymentMethodCurrencyFixture = $this->container->get(WhitelabelPaymentMethodCurrencyFixture::class);

        $this->currencyRepository = $this->container->get(CurrencyRepository::class);
        $this->whitelabelPaymentMethodCurrencyRepositoryUnderTest = $this->container->get(WhitelabelPaymentMethodCurrencyRepository::class);

        $this->whitelabel = $this->whitelabelFixture->createOne();
        $this->defaultLanguageId = Helpers_General::get_default_language_id();
    }

    /**
     * @test
     */
    public function getAllEnabledCurrencies(): void
    {
        $whitelabelPaymentMethod1 = $this->whitelabelPaymentMethodFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'name' => 'Test Payment 1',
                'payment_method_id' => 1,
                'language_id' => $this->defaultLanguageId,
            ]);

        $whitelabelPaymentMethod2 = $this->whitelabelPaymentMethodFixture
            ->withWhitelabel($this->whitelabel)
            ->createOne([
                'name' => 'Test Payment 2',
                'payment_method_id' => 2,
                'language_id' => $this->defaultLanguageId,
            ]);

        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod1, 'USD');
        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod1, 'EUR', true, true);
        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod1, 'GBP', false);
        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod1, 'PLN');

        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod2, 'USD');
        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod2, 'EUR', true, true);
        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod2, 'GBP', false);
        $this->addCurrencyForWhitelabelPaymentMethod($whitelabelPaymentMethod2, 'PLN', false);

        $whitelabelPaymentMethodIds = [
            $whitelabelPaymentMethod1->id,
            $whitelabelPaymentMethod2->id,
        ];

        $whitelabelEnabledPaymentMethodCurrencies = $this->whitelabelPaymentMethodCurrencyRepositoryUnderTest->getAllEnabledCurrencies($whitelabelPaymentMethodIds);

        /** @var Currency $currencyUsd */
        $currencyUsd = $this->currencyRepository->findOneByCode('USD');

        $allWhitelabelPaymentMethodIds = array_column(
            $currencyUsd->whitelabelPaymentMethodCurrencies,
            'whitelabel_payment_method_id'
        );

        $addedWhitelabelPaymentMethodIds = array_intersect($whitelabelPaymentMethodIds, $allWhitelabelPaymentMethodIds);

        $this->assertCount(5, $whitelabelEnabledPaymentMethodCurrencies);
        $this->assertCount(2, $addedWhitelabelPaymentMethodIds);
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
