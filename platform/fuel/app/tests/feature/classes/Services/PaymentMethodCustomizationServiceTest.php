<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Services;

use Helpers_General;
use Model_Language;
use Models\WhitelabelPaymentMethod;
use Tests\Fixtures\WhitelabelPaymentMethodCustomizeFixture;
use Tests\Fixtures\WhitelabelPaymentMethodFixture;
use Services\PaymentMethodCustomizationService;
use Test_Feature;

final class PaymentMethodCustomizationServiceTest extends Test_Feature
{
    private PaymentMethodCustomizationService $paymentMethodCustomizationServiceUnderTest;
    private WhitelabelPaymentMethod $whitelabelPaymentMethod;
    private WhitelabelPaymentMethodFixture $whitelabelPaymentMethodFixture;
    private WhitelabelPaymentMethodCustomizeFixture $whitelabelPaymentMethodCustomizeFixture;
    private int $defaultLanguageId;
    private int $polishLanguageId;

    public function setUp(): void
    {
        parent::setUp();

        $this->paymentMethodCustomizationServiceUnderTest = $this->container->get(PaymentMethodCustomizationService::class);
        $this->whitelabelPaymentMethodFixture = $this->container->get(WhitelabelPaymentMethodFixture::class);
        $this->whitelabelPaymentMethodCustomizeFixture = $this->container->get(WhitelabelPaymentMethodCustomizeFixture::class);

        $this->defaultLanguageId = Helpers_General::get_default_language_id();
        $this->polishLanguageId = (int) Model_Language::find_one_by_code('pl_PL')['id'];

        $this->whitelabelPaymentMethod = $this->whitelabelPaymentMethodFixture
            ->with('basic')
            ->createOne([
                'payment_method_id' => 1,
                'language_id' => $this->defaultLanguageId,
                'name' => 'Test Payment'
            ]);
    }

    /**
     * @test
     */
    public function getWhitelabelPaymentMethodCustomizeData_WithoutAnyCustomization_ShouldReturnEmptyArray(): void
    {
        $actual = $this->paymentMethodCustomizationServiceUnderTest->getWhitelabelPaymentMethodCustomizeData(
            $this->whitelabelPaymentMethod->id,
            $this->polishLanguageId
        );

        $this->assertEmpty($actual);
    }

    public function getPaymentMethodCustomizationDataProvider(): array
    {
        return [
            'English Customization Only' => $this->getEnglishCustomizationOnly(),
            'Partially English Customization Only' => $this->getPartiallyEnglishCustomizationOnly(),
            'English And Polish Customization' => $this->getEnglishAndPolishCustomization(),
            'English And Partially Polish Customization' => $this->getEnglishAndPartiallyPolishCustomization(),
            'Polish Customization Only' => $this->getPolishCustomizationOnly(),
        ];
    }

    /**
     * @test
     * @dataProvider getPaymentMethodCustomizationDataProvider
     */
    public function getWhitelabelPaymentMethodCustomizeData(
        array $expectedArray,
        array $defaultCustomization = [],
        array $polishCustomization = [],
    ): void {

        if (!empty($defaultCustomization)) {
            $this->createPaymentMethodCustomization($this->whitelabelPaymentMethod, $this->defaultLanguageId, $defaultCustomization);
        }

        if (!empty($polishCustomization)) {
            $this->createPaymentMethodCustomization($this->whitelabelPaymentMethod, $this->polishLanguageId, $polishCustomization);
        }

        $actual = $this->paymentMethodCustomizationServiceUnderTest->getWhitelabelPaymentMethodCustomizeData(
            $this->whitelabelPaymentMethod->id,
            $this->polishLanguageId
        );

        $this->assertIsArray($actual);

        foreach ($this->whitelabelPaymentMethodCustomizeFixture->getDefaults() as $attribute => $defaultValue) {
            $this->assertArrayHasKey($attribute, $actual);
            $this->assertSame($expectedArray[$attribute], $actual[$attribute]);
        }
    }

    private function createPaymentMethodCustomization(
        WhitelabelPaymentMethod $whitelabelPaymentMethod,
        int $languageId,
        array $customizationData
    ): void {
        $this->whitelabelPaymentMethodCustomizeFixture
            ->withWhitelabelPaymentMethod($whitelabelPaymentMethod)
            ->withWhitelabelLanguageId($languageId)
            ->createOne($customizationData);
    }

    private array $englishPaymentMethodCustomization = [
        'title' => 'Payment Title',
        'title_for_mobile' => 'Payment Title For Mobile',
        'title_in_description' => 'Payment Title In Description',
        'description' => 'Payment Description',
        'additional_failure_text' => 'Additional Failure Text',
        'additional_success_text' => 'Additional Success Text',
    ];

    private array $polishPaymentMethodCustomization = [
        'title' => 'Tytuł Płatności',
        'title_for_mobile' => 'Tytuł Płatności dla wersji mobilnej',
        'title_in_description' => 'Tytuł płatności w opisie',
        'description' => 'Opis Płatności',
        'additional_failure_text' => 'Dodatkowy tekst nieudanej płatności',
        'additional_success_text' => 'Dodatkowy tekst udanej płatności',
    ];

    private function getEnglishCustomizationOnly(): array
    {
        $expected = $this->englishPaymentMethodCustomization;

        return [$expected, $this->englishPaymentMethodCustomization];
    }

    private function getPartiallyEnglishCustomizationOnly(): array
    {
        $expected = [
            'title' => 'Payment Title',
            'title_for_mobile' => 'Payment Title For Mobile',
            'title_in_description' => '',
            'description' => null,
            'additional_failure_text' => null,
            'additional_success_text' => 'Additional Success Text',
        ];

        $partiallyEnglishCustomization = [
            'title' => 'Payment Title',
            'title_for_mobile' => 'Payment Title For Mobile',
            'additional_success_text' => 'Additional Success Text',
        ];

        return [$expected, $partiallyEnglishCustomization];
    }

    private function getEnglishAndPolishCustomization(): array
    {
        $expected = $this->polishPaymentMethodCustomization;

        return [$expected, $this->englishPaymentMethodCustomization, $this->polishPaymentMethodCustomization];
    }

    private function getEnglishAndPartiallyPolishCustomization(): array
    {
        $expected = [
            'title' => 'Tytuł Płatności',
            'title_for_mobile' => 'Tytuł Płatności dla wersji mobilnej',
            'title_in_description' => '',
            'description' => 'Opis Płatności',
            'additional_failure_text' => 'Dodatkowy tekst nieudanej płatności',
            'additional_success_text' => null,
        ];

        $partiallyPolishCustomization = [
            'title' => 'Tytuł Płatności',
            'title_for_mobile' => 'Tytuł Płatności dla wersji mobilnej',
            'description' => 'Opis Płatności',
            'additional_failure_text' => 'Dodatkowy tekst nieudanej płatności'
        ];

        return [$expected, $this->englishPaymentMethodCustomization, $partiallyPolishCustomization];
    }

    private function getPolishCustomizationOnly(): array
    {
        $expected = $this->polishPaymentMethodCustomization;

        return [$expected, [], $this->polishPaymentMethodCustomization];
    }
}
