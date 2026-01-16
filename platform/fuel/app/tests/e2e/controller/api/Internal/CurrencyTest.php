<?php

use Models\Currency;

class CurrencyTest extends Test_E2e_Controller_Api
{
    private const CURRENCY_ENDPOINT = '/api/internal/currency/converter';

    /** @test */
    public function getConvertedCurrency_returnsCorrectResponse(): void
    {
        $response = $this->getResponse(
            'GET',
            self::CURRENCY_ENDPOINT . '?amount=10.00&currency=USD&convertToCurrency=EUR'
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertArrayHasKey('amount', $body);
        $this->assertArrayHasKey('currency', $body);

        $model = new Currency();
        $currency = $model->get_by_code('EUR');
        $expectedAmountAfterConversion = round($currency['rate'] * 10.00, 2);

        // Verify that conversion was correct, compare strings due to floats
        $this->assertEquals(strval($expectedAmountAfterConversion), strval($body['amount']));
        $this->assertEquals('EUR', $body['currency']);
    }

    /** @test */
    public function getConvertedCurrency_brokenAmountParameter_returnsEmpty(): void
    {
        $brokenAmount = 'xx.xx';

        $response = $this->getResponse(
            'GET',
            self::CURRENCY_ENDPOINT . "?amount={$brokenAmount}&currency=USD&convertToCurrency=EUR"
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertArrayHasKey('amount', $body);
        $this->assertArrayHasKey('currency', $body);

        $this->assertEquals(0, $body['amount']);
        $this->assertNull($body['currency']);
    }

    /**
     * @test
     * @dataProvider provider
     * Response should be:
     * - amount: 0
     * - currency: null
     */
    public function getConvertedCurrency_ExoticBrokenParameters_TestApi($paymentAmountInGateway, $paymentCurrencyInGateway, $userSelectedCurrency): void
    {

        $response = $this->getResponse(
            'GET',
            self::CURRENCY_ENDPOINT . "?amount={$paymentAmountInGateway}&currency={$paymentCurrencyInGateway}&convertToCurrency={$userSelectedCurrency}"
        );

        $body = $response['body'];
        $this->assertSame(200, $response['status']);
        $this->assertArrayHasKey('amount', $body);
        $this->assertArrayHasKey('currency', $body);

        $this->assertEquals(0, $body['amount']);
        $this->assertNull($body['currency']);
    }

    public function provider(): array
    {
        return [
            ['', 'USD', 'EUR'],
            ['10.00', 'USD', ''],
            ['10.00', '', 'EUR'],
            ['4x0.00', 'USD', 'EUR'],
            ['10.00', 'USDD', 'EUR'],
            ['10.00', 'USD', '10.00'],
            [null, 'USD', 'EUR'],
            ['10.00', null, 'EUR'],
            ['10.00', 'USD', null],
            ['99999999999999', 'USD', 'EUR'],
        ];
    }
}
