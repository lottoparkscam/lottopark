<?php

namespace Unit\Modules\Payments;

use Exception;
use Models\WhitelabelTransaction;
use Modules\Payments\PaymentAcceptorContract;
use Modules\Payments\PaymentAcceptorDecorator;
use PHPUnit\Framework\MockObject\MockObject;
use Repositories\Orm\TransactionRepository;
use Services\Shared\Logger\LoggerContract;
use Test_Unit;
use Wrappers\Db;

/** @covers \Modules\Payments\PaymentAcceptorDecorator */
final class PaymentAcceptorDecoratorTest extends Test_Unit
{
    /** @var MockObject|TransactionRepository */
    private TransactionRepository $transactionRepo;
    /** @var PaymentAcceptorContract|MockObject */
    private PaymentAcceptorContract $acceptor;
    /** @var MockObject|Db */
    private Db $db;
    /** @var MockObject|LoggerContract */
    private LoggerContract $logger;
    private PaymentAcceptorDecorator $service;

    public function setUp(): void
    {
        parent::setUp();

        $this->transactionRepo = $this->createMock(TransactionRepository::class);
        $this->acceptor = $this->getMockForAbstractClass(PaymentAcceptorContract::class);
        $this->db = $this->createMock(Db::class);
        $this->logger = $this->createMock(LoggerContract::class);

        $this->service = new PaymentAcceptorDecorator(
            $this->transactionRepo,
            $this->acceptor,
            $this->db,
            $this->logger
        );
    }

    /** @test */
    public function confirm_RequestDetailsPassed_ShouldLogInfo(): array
    {
        // Given request as array
        $request = ['some_value' => 123];
        $whitelabelId = 1;

        // And repository returning transaction
        $transaction = new WhitelabelTransaction(['token' => 123]);
        $this->transactionRepo->method('getByToken')->willReturn($transaction);

        // Then log should be created
        $this->logger
            ->expects($this->once())
            ->method('logInfo')
            ->with('Confirm request received', $request);

        // When transaction is confirmed
        $this->service->confirm($transaction->prefixed_token, $whitelabelId, $request);

        return [$transaction, $request];
    }

    /**
     * @test
     * @depends confirm_RequestDetailsPassed_ShouldLogInfo
     */
    public function confirm_TransactionDetailsShouldBePopulatedWithRequestDetails(array $args): void
    {
        // Given transaction and request data
        [$transaction, $request] = $args;

        // Should contain request data in extra data field
        $this->assertSame($request, $transaction->getAdditionalData());
    }

    /** @test */
    public function confirm_LoggerExceptionOccurs_PaymentShouldBeConfirmedAndErrorThrownAfterTransaction(): void
    {
        // Given transaction returned by repo
        $whitelabelId = 1;
        $transaction = new WhitelabelTransaction(['token' => 123]);
        $this->transactionRepo->method('getByToken')->willReturn($transaction);

        // And logger | transaction interaction that throws an exception
        $exception = new Exception('Some error');
        $this->logger->method('logInfo')->willThrowException($exception);

        // And db transaction that should be committed
        $this->db->expects($this->once())->method('commit_transaction');

        // And the error was thrown in logger, should be logged after all
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Some error');

        // When service confirm action is called
        $this->service->confirm($transaction->prefixed_token, $whitelabelId, []);
    }
}
