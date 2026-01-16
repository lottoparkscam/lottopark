<?php

namespace classes\modules\Payments\Astro;

use InvalidArgumentException;
use Models\WhitelabelTransaction;
use Modules\Payments\Astro\AstroStatusHandler;
use Modules\Payments\Astro\Client\AstroCheckStatusClient;
use Modules\Payments\PaymentStatus;
use Repositories\Orm\TransactionRepository;
use Test_Unit;

class AstroStatusHandlerTest extends Test_Unit
{
    private AstroCheckStatusClient $client;
    private TransactionRepository $repo;
    private AstroStatusHandler $service;
    private array $additionalData;

    public function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createMock(AstroCheckStatusClient::class);
        $this->repo = $this->createMock(TransactionRepository::class);
        $this->service = new AstroStatusHandler($this->client, $this->repo);

        // Required field to check status of transaction
        $additionalDataJson = '{"deposit_external_id": "vK1dNSFPcaeBxQHMC71Gt4hX54jwvlRrAlO0eTaH"}';
        $this->additionalData = json_decode($additionalDataJson, true);
    }

    /**
     * @test
     * @dataProvider getStatus__maps_to_expected_formatDataProvider
     * @param string $responseStatus
     * @param PaymentStatus $expected
     */
    public function getStatus__maps_to_expected_format(string $responseStatus, PaymentStatus $expected): void
    {
        // Given
        $transactionId = 'abc123';
        $whitelabelId = 1;

        $transaction = new WhitelabelTransaction(['token' => $transactionId, 'whitelabel_id' => $whitelabelId]);
        $transaction->additional_data_json = $this->additionalData;

        $this->repo
            ->expects($this->once())
            ->method('getByToken')
            ->with($transactionId, $whitelabelId)
            ->willReturn($transaction);

        $response = ['status' => $responseStatus];
        $responseMock = $this->mockResponse($response);

        $this->client
            ->expects($this->once())
            ->method('request')
            ->with($transaction)
            ->willReturn($responseMock);

        // When
        $actual = $this->service->getStatus($transactionId, $whitelabelId);

        // Then
        $this->assertEquals($expected, $actual);
    }

    public function getStatus__maps_to_expected_formatDataProvider(): array
    {
        return [
            'pending' => ['PENDING', PaymentStatus::PENDING()],
            'APPROVED' => ['APPROVED', PaymentStatus::PAID()],
            'CANCELLED' => ['CANCELLED', PaymentStatus::FAILED()],
        ];
    }

    /** @test */
    public function getStatus__not_mapped__throws_invalid_argument_exception(): void
    {
        // Expect
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown not existing one code received');

        // Given
        $transactionId = 'abc123';
        $whitelabelId = 1;

        $transaction = new WhitelabelTransaction(['token' => $transactionId, 'whitelabel_id' => $whitelabelId]);
        $transaction->additional_data_json = $this->additionalData;

        $this->repo
            ->method('getByToken')
            ->with($transactionId, $whitelabelId)
            ->willReturn($transaction);

        $responseStatus = 'not existing one';
        $response = ['status' => $responseStatus];
        $responseMock = $this->mockResponse($response);

        $this->client
            ->method('request')
            ->with($transaction)
            ->willReturn($responseMock);

        // When
        $this->service->getStatus($transactionId, $whitelabelId);
    }

    /**
     * @test
     * Without deposit_external_id field the transaction is corrupted, and we cannot verify its status.
     */
    public function getStatus_CorruptedTransactionWithoutDepositExternalId_TransactionMarkedAsFailed(): void
    {
        $transactionId = 'abc123';
        $whitelabelId = 1;
        $transaction = new WhitelabelTransaction(['token' => $transactionId, 'whitelabel_id' => $whitelabelId]);

        $this->repo
            ->method('getByToken')
            ->with($transactionId, $whitelabelId)
            ->willReturn($transaction);

        $responseStatus = PaymentStatus::CORRUPTED();
        $response = ['status' => $responseStatus];
        $responseMock = $this->mockResponse($response);

        $this->client
            ->method('request')
            ->with($transaction)
            ->willReturn($responseMock);

        $actual = $this->service->getStatus($transactionId, $whitelabelId);
        $this->assertEquals($responseStatus, $actual);
    }
}
