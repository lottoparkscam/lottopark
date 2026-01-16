<?php

declare(strict_types=1);

namespace Tests\Unit\Classes\Services;

use Helpers_General;
use Services\TransactionService;
use Tests\Fixtures\WhitelabelTransactionFixture;
use Test_Unit;

final class TransactionServiceTest extends Test_Unit
{
    private TransactionService $transactionServiceUnderTest;
    private WhitelabelTransactionFixture $whitelabelTransactionFixture;

    public function setUp(): void
    {
        parent::setUp();

        $this->transactionServiceUnderTest = $this->container->get(TransactionService::class);
        $this->whitelabelTransactionFixture = $this->container->get(WhitelabelTransactionFixture::class);
    }

    public function getTypesDataProvider(): array
    {
        return [
            [Helpers_General::TYPE_TRANSACTION_PURCHASE, 'Purchase'],
            [Helpers_General::TYPE_TRANSACTION_DEPOSIT, 'Deposit'],
        ];
    }

    public function getStatusesDataProvider(): array
    {
        return [
            [-1, 'Pending'],
            [Helpers_General::STATUS_TRANSACTION_PENDING, 'Pending'],
            [Helpers_General::STATUS_TRANSACTION_APPROVED, 'Approved'],
            [Helpers_General::STATUS_TRANSACTION_ERROR, 'Error'],
        ];
    }

    /**
     * @test
     * @dataProvider getTypesDataProvider
     */
    public function getTransactionType(int $type, string $expectedTypeFormatted): void
    {
        $whitelabelTransaction = $this->whitelabelTransactionFixture->makeOne([
            'type' => $type
        ]);

        $typeFormatted = $this->transactionServiceUnderTest->getTransactionType($whitelabelTransaction);

        $this->assertSame($expectedTypeFormatted, $typeFormatted);
    }

    /**
     * @test
     * @dataProvider getStatusesDataProvider
     */
    public function getTransactionStatus(int $status, string $expectedStatusFormatted): void
    {
        $whitelabelTransaction = $this->whitelabelTransactionFixture->makeOne([
            'status' => $status
        ]);

        $actualStatusFormatted = $this->transactionServiceUnderTest->getTransactionStatus($whitelabelTransaction);

        $this->assertSame($expectedStatusFormatted, $actualStatusFormatted);
    }
}
