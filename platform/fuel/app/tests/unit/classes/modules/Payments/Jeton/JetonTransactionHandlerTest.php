<?php

namespace Unit\Services\Payments\Jeton;

use GuzzleHttp\Exception\RequestException;
use Models\WhitelabelUserTicket;
use Models\WhitelabelTransaction;
use Modules\Payments\Jeton\Client\JetonStatusCheckClient;
use Modules\Payments\Jeton\Client\JetonTransactionType;
use Modules\Payments\Jeton\JetonTransactionHandler;
use Modules\Payments\PaymentStatus;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\RequestInterface;
use Repositories\Orm\TransactionRepository;
use Test_Unit;

class JetonTransactionHandlerTest extends Test_Unit
{
    /** @var TransactionRepository|MockObject */
    private TransactionRepository $repo;
    /** @var JetonStatusCheckClient|MockObject */
    private JetonStatusCheckClient $statusCheckClient;

    private JetonTransactionHandler $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repo = $this->createMock(TransactionRepository::class);
        $this->statusCheckClient = $this->createMock(JetonStatusCheckClient::class);
        $this->service = new JetonTransactionHandler($this->repo, $this->statusCheckClient);
    }

    /** @test */
    public function getPaymentStatus__payment_success__status_is_paid(): void
    {
        // Given
        $expectedStatus = PaymentStatus::PAID();
        $orderId = 1;

        $transaction = new WhitelabelTransaction();
        $transaction->status = $expectedStatus;
        $transaction->whitelabel_id = 1;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($orderId, $transaction->whitelabel_id, ['whitelabel_tickets'])
            ->willReturn($transaction);

        $response = ['status' => 'SUCCESS'];
        $this->statusCheckClient
            ->expects($this->once())
            ->method('request')
            ->with($transaction, JetonTransactionType::PAY())
            ->willReturn(
                $this->mockResponse($response)
            );

        // When
        $actual = $this->service->getPaymentStatus($orderId, $transaction->whitelabel_id);

        // Then
        $this->assertEquals($expectedStatus, $actual);
    }

    /** @test */
    public function getPaymentStatus__payment_not_success__status_is_pending(): void
    {
        // Given
        $expectedStatus = PaymentStatus::PENDING();
        $orderId = 1;

        $transaction = new WhitelabelTransaction();
        $transaction->status = $expectedStatus;
        $transaction->whitelabel_id = 1;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($orderId, $transaction->whitelabel_id, ['whitelabel_tickets'])
            ->willReturn($transaction);

        $response = ['status' => 'AWAITING'];
        $this->statusCheckClient
            ->expects($this->once())
            ->method('request')
            ->with($transaction, JetonTransactionType::PAY())
            ->willReturn(
                $this->mockResponse($response)
            );

        // When
        $actual = $this->service->getPaymentStatus($orderId, $transaction->whitelabel_id);

        // Then
        $this->assertEquals($expectedStatus, $actual);
    }

    /** @test */
    public function getPaymentStatus__payment_failed__transaction_is_failed(): void
    {
        // Given
        $expectedStatus = PaymentStatus::FAILED();
        $orderId = 1;

        $transaction = new WhitelabelTransaction();
        $transaction->whitelabel_tickets = [new WhitelabelUserTicket()];
        $transaction->status = $expectedStatus;
        $transaction->whitelabel_id = 1;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($orderId, $transaction->whitelabel_id, ['whitelabel_tickets'])
            ->willReturn($transaction);

        $response = ['status' => 'ERROR'];
        $this->statusCheckClient
            ->expects($this->once())
            ->method('request')
            ->with($transaction, JetonTransactionType::PAY())
            ->willReturn(
                $this->mockResponse($response)
            );

        // When
        $actual = $this->service->getPaymentStatus($orderId, $transaction->whitelabel_id);

        // Then
        $this->assertEquals($expectedStatus, $actual);
    }

    /** @test */
    public function getPaymentStatus__payment_not_success_and_guzzle_exception__transaction_is_pending(): void
    {
        // Given
        $expectedStatus = PaymentStatus::PENDING();

        $orderId = 1;
        $exception = new RequestException(
            'some error',
            $this->createMock(RequestInterface::class)
        );

        $transaction = new WhitelabelTransaction();
        $transaction->status = $expectedStatus;
        $transaction->whitelabel_id = 1;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($orderId, $transaction->whitelabel_id, ['whitelabel_tickets'])
            ->willReturn($transaction);

        $this->statusCheckClient
            ->expects($this->once())
            ->method('request')
            ->willThrowException($exception);

        // When
        $actual = $this->service->getPaymentStatus($orderId, $transaction->whitelabel_id);

        // Then
        $this->assertEquals($expectedStatus, $actual);
    }
}
