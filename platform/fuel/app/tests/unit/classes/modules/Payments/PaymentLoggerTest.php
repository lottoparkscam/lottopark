<?php

namespace Unit\Modules\Payments;

use Factory_Orm_Transaction;
use Models\WhitelabelTransaction;
use Models\WhitelabelPaymentMethod;
use Modules\Payments\PaymentLogger;
use Repositories\Orm\PaymentLogRepository;
use Test_Unit;

class PaymentLoggerTest extends Test_Unit
{
    private PaymentLogRepository $repo;
    private WhitelabelTransaction $transaction;

    private PaymentLogger $logger;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = $this->createMock(PaymentLogRepository::class);

        $factory = $this->container->get(Factory_Orm_Transaction::class);
        $this->transaction = $factory->build(false);
        $wlPaymentMethod = new WhitelabelPaymentMethod();
        $wlPaymentMethod->payment_method_id = 1000;
        $this->transaction->whitelabel_payment_method = $wlPaymentMethod;
        $this->logger = new PaymentLogger($this->repo);
    }

    /**
     * @test
     * @dataProvider with_context_dataProvider
     *
     * @param string $type
     * @param array $context
     */
    public function log__with_context__stores_in_repo(string $type, array $context): void
    {
        // Given
        $message = 'Some message';

        $this->repo
            ->expects($this->once())
            ->method('save')
            ->with($this->anything(), true);

        // When
        $method = "log$type";
        $this->logger->$method($message, $context);
    }

    /**
     * @test
     * @dataProvider with_context_dataProvider
     *
     * @param string $type
     */
    public function log__without_context__stores_in_repo(string $type): void
    {
        // Given
        $context = [];
        $message = 'Some message';

        $this->repo
            ->expects($this->once())
            ->method('save')
            ->with($this->anything(), true);

        // When
        $method = "log$type";
        $this->logger->$method($message, $context);
    }

    public function with_context_dataProvider(): array
    {
        $this->setUp();
        return [
            'info' => ['Info', ['transaction' => $this->transaction]],
            'warning' => ['Warning', ['transaction' => $this->transaction]],
            'success' => ['Success', ['transaction' => $this->transaction]],
            'error' => ['Info', ['transaction' => $this->transaction]],
        ];
    }
}
