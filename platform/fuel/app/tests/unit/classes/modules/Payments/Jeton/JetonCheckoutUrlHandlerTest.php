<?php

namespace Unit\Services\Payments\Jeton;

use Exception;
use Models\WhitelabelTransaction;
use Modules\Payments\Jeton\Client\JetonCheckoutPayClient;
use Modules\Payments\Jeton\JetonCheckoutUrlHandler;
use Modules\Payments\PaymentUrlHelper;
use Orm\RecordNotFound;
use Repositories\Orm\TransactionRepository;
use RuntimeException;
use Services\Shared\Logger\LoggerContract;
use Test_Unit;

class JetonCheckoutUrlHandlerTest extends Test_Unit
{
    private JetonCheckoutPayClient $payClient;
    private LoggerContract $logger;
    private TransactionRepository $repo;
    private PaymentUrlHelper $urlHelper;
    private JetonCheckoutUrlHandler $s;

    public function setUp(): void
    {
        parent::setUp();

        $this->payClient = $this->createMock(JetonCheckoutPayClient::class);
        $this->logger = $this->createMock(LoggerContract::class);
        $this->repo = $this->createMock(TransactionRepository::class);
        $this->urlHelper = $this->createMock(PaymentUrlHelper::class);
        $this->s = new JetonCheckoutUrlHandler($this->payClient, $this->repo, $this->logger, $this->urlHelper);
    }

    /** @test */
    public function processPayment__valid__returns_url(): void
    {
        // Given
        $transactionPrefixedToken = 'ASC123';
        $amount = 12.12;
        $currencyCode = 'USD';
        $language = 'EN';
        $expectedUrl = 'some_url.com';

        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_id = 1;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($transactionPrefixedToken, $transaction->whitelabel_id)
            ->willReturn($transaction);

        $this->urlHelper
            ->expects($this->once())
            ->method('getConfirmationUrl')
            ->with($transaction)
            ->willReturn($expectedUrl);

        $this->payClient
            ->expects($this->once())
            ->method('request')
            ->with(
                $transaction,
                $amount,
                $currencyCode,
                $expectedUrl,
                $language
            )
            ->willReturn($this->mockResponse(['checkout' => $expectedUrl]));

        // When
        $actual = $this->s->processPayment(
            $transactionPrefixedToken,
            $transaction->whitelabel_id,
            $amount,
            $currencyCode
        );

        // Then
        $this->assertSame($expectedUrl, $actual);
    }

    /** @test */
    public function processPayment__exception_different_than_orm_not_found__logs_and_re_throw_exception(): void
    {
        // Given
        $exception = new Exception('Some error');
        $transaction = new WhitelabelTransaction();
        $transaction->token = 123;
        $transaction->whitelabel_payment_method_id = 1;
        $transaction->whitelabel_id = 1;

        // Except
        $this->expectException(get_class($exception));
        $this->expectExceptionMessage($exception->getMessage());

        // Given
        $transactionPrefixedToken = 'asdasd';
        $amount = 12.12;
        $currencyCode = 'USD';

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->willReturn($transaction);

        $this->urlHelper
            ->method('getConfirmationUrl')
            ->willReturn('someurl.com');

        $this->payClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        $this->logger
            ->expects($this->once())
            ->method('logErrorFromException')
            ->with($exception);

        // When
        $this->s->processPayment(
            $transactionPrefixedToken,
            $transaction->whitelabel_id,
            $amount,
            $currencyCode
        );
    }

    /** @test */
    public function processPayment__orm_record_not_found_exception__re_throws_and_not_logs(): void
    {
        // Given
        $thrownException = new RecordNotFound('Some error');

        // Except
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Transaction #asdasd not found');

        // Given
        $transactionPrefixedToken = 'asdasd';
        $whitelabelId = 1;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($transactionPrefixedToken, $whitelabelId)
            ->willThrowException($thrownException);

        $this->logger
            ->expects($this->never())
            ->method('logErrorFromException');

        // When
        $this->s->processPayment(
            $transactionPrefixedToken,
            $whitelabelId,
            20,
            'USD'
        );
    }
}
