<?php

namespace Unit\Validators;

use Repositories\Orm\CurrencyRepository;
use Test_Unit;
use Validators\CurrencyConverterValidator;

class CurrencyConverterValidatorTest extends Test_Unit
{
    private CurrencyRepository $currencyRepository;

    public function setUp(): void
    {
        $this->currencyRepository = $this->createMock(CurrencyRepository::class);
        $this->currencyRepository->method('getAllCodes')->willReturn(['EUR', 'USD']);
        $this->resetInput();
    }

    /** @test */
    public function currencyConverterValidator_ValidData(): void
    {
        $this->setInput('PATCH', ['amount' => '9999999999999.99', 'currency' => 'USD', 'convertToCurrency' => 'EUR',]);

        $currencyConverterValidator = new CurrencyConverterValidator($this->currencyRepository);
        $actualResult = $currencyConverterValidator->isValid();
        $this->assertTrue($actualResult);
    }

    /**
     * @test
     * @dataProvider invalidDataProvider
     */
    public function currencyConverterValidator_InvalidData(string $paymentAmountInGateway, string $paymentCurrencyInGateway, string $userSelectedCurrency): void
    {
        $this->setInput('PATCH', ['amount' => $paymentAmountInGateway, 'currency' => $paymentCurrencyInGateway, 'convertToCurrency' => $userSelectedCurrency,]);

        $currencyConverterValidator = new CurrencyConverterValidator($this->currencyRepository);
        $actualResult = $currencyConverterValidator->isNotValid();
        $this->assertTrue($actualResult);
    }

    public static function invalidDataProvider(): array
    {
        return [
            ['99999999999999', 'USD', 'EUR'], // Outside max range
            ['-10', 'USD', 'EUR'], // Outside min range
            ['10.00', 'EEE', 'EUR'], // Wrong currency code
            ['10.00', 'USD', 'EEE'], // Wrong currency code
            ['', 'USD', 'EUR'], // Field is required
            ['10.00', '', 'EUR'], // Field is required
            ['10.00', 'USD', ''], // Field is required
        ];
    }
}
