<?php

namespace Tests\Unit\Classes\Services;

use DateTimeImmutable;
use Exception;
use Services\PaymentService;
use Services\Shared\System;
use Test_Unit;

class PaymentServiceTest extends Test_Unit
{
    /**
     * @test
     * @dataProvider dataProvider
     * First argument is whitelabel->domain
     * - here we will never have casino subdomain
     * Second argument is expected base url. We cannot have casino here as IPN will not reach us
     */
    public function paymentConfirmationBaseUrl(string $domain, string $expectedUrl, bool $simulateActionTakenFromCasinoSubDomain = false): void
    {
        if ($simulateActionTakenFromCasinoSubDomain) {
            $_SERVER['HTTP_HOST'] = 'casino.' . $domain;
        } else {
            $_SERVER['HTTP_HOST'] = $domain;
        }
        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);

        $paymentService = new PaymentService($system);
        $actualUrl = $paymentService->getPaymentConfirmationBaseUrl();

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    public function dataProvider(): array
    {
        return [
            ['lottopark.com', 'http://lottopark.com'],
            ['lottohoy.com', 'http://www.lottohoy.com'],
            ['doublejack.online', 'http://doublejack.online'],
            ['lottopark.com', 'http://lottopark.com', true],
            ['lottohoy.com', 'http://www.lottohoy.com', true],
            ['doublejack.online', 'http://doublejack.online', true],
        ];
    }

    /** @test */
    public function paymentConfirmationFullUrl(): void
    {
        $expectedUrl = 'http://lottopark.com/order/confirm/wonderlandpay/3/';
        $domain = 'lottopark.com';

        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);
        $paymentService->configure('wonderlandpay', '3');
        $actualUrl = $paymentService->getPaymentConfirmationFullUrl();

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    /** @test */
    public function paymentConfirmationFullUrl_TerminatesPaymentByThrowingException(): void
    {
        $domain = 'lottopark.com';
        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);

        $this->expectException(Exception::class);
        $paymentService->getPaymentConfirmationFullUrl();
    }

    /** @test */
    public function paymentConfirmationFullUrl_MissingUri_TerminatesPaymentByThrowingException(): void
    {
        $domain = 'lottopark.com';
        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);

        $paymentService->configure('', 3);
        $this->expectException(Exception::class);
        $paymentService->getPaymentConfirmationFullUrl();
    }

    /** @test */
    public function paymentConfirmationFullUrl_MissingWhitelabelPaymenMethodtId_TerminatesPaymentByThrowingException(): void
    {
        $domain = 'lottopark.com';
        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);

        $paymentService->configure('wonderlandpay');
        $this->expectException(Exception::class);
        $paymentService->getPaymentConfirmationFullUrl();
    }

    /** @test */
    public function paymentResultFullUrl(): void
    {
        $expectedUrl = 'https://lottopark.com/order/result/pspgate/430/';
        $domain = 'lottopark.com';
        $_SERVER['HTTP_HOST'] = $domain;

        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);
        $paymentService->configure('pspgate', '430');
        $actualUrl = $paymentService->getPaymentResultFullUrl();

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    /** @test */
    public function paymentResultFullUrl_CasinoReturnsCasinoAsSubDomain(): void
    {
        $expectedUrl = 'https://casino.lottopark.com/order/result/pspgate/430/';
        $domain = 'lottopark.com';
        $_SERVER['HTTP_HOST'] = 'casino.' . $domain;

        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);
        $paymentService->configure('pspgate', '430');
        $actualUrl = $paymentService->getPaymentResultFullUrl();

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    /** @test */
    public function paymentResultFullUrl_ReturnsTransactionTokenInQueryStringWhenPassed(): void
    {
        $expectedUrl = 'https://lottopark.com/order/result/pspgate/430/?token=LPD207791039';
        $domain = 'lottopark.com';
        $_SERVER['HTTP_HOST'] = $domain;

        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);
        $paymentService->configure('pspgate', '430');
        $actualUrl = $paymentService->getPaymentResultFullUrl('LPD207791039');

        $this->assertEquals($expectedUrl, $actualUrl);
    }

    /** @test */
    public function paymentResultFullUrl_MissingWhitelabelPaymenMethodtId_TerminatesPaymentByThrowingException(): void
    {
        $domain = 'lottopark.com';
        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);

        $paymentService->configure('', 0);
        $this->expectException(Exception::class);
        $paymentService->getPaymentResultFullUrl();
    }

    /** @test */
    public function paymentResultFullUrl_TokenIsIncorrect(): void
    {
        $domain = 'lottopark.com';
        $system = new System('PROD', new DateTimeImmutable(), ['host' => $domain], null);
        $paymentService = new PaymentService($system);

        $paymentService->configure('pspgate', '430');
        $this->expectException(Exception::class);
        $paymentService->getPaymentResultFullUrl('310');
    }

    /**
     * @test
     * @dataProvider convertAmountToCentsDataProvider
     * @param mixed|string|float $amount
     */
    public function convertAmountToCents_VerifyDifferentAmountsAreConvertedCorrectly($amount, int $expectedAmountInCents): void
    {
        $result = PaymentService::convertAmountToCents($amount);
        $this->assertEquals($expectedAmountInCents, $result);
    }

    public static function convertAmountToCentsDataProvider(): array
    {
        return [
            [10.00, 1000],
            [50.00, 5000],
            [50, 5000],
            ['59', 5900],
            ['50.00', 5000],
            [4.89, 489],
            ['4.89', 489],
            [40.89, 4089],
            ['40.89', 4089],
            [40.01, 4001],
            ['40.01', 4001],
            [99.99, 9999],
            ['99.99', 9999],
        ];
    }
}
