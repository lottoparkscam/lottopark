<?php


namespace Feature\Repositories\Orm;

use Container;
use DateTimeImmutable;
use Factory_Orm_Transaction;
use Helpers_General;
use Repositories\Orm\TransactionRepository;
use Services\Shared\System;
use Test_Feature;
use Wrappers\Decorators\ConfigContract;

class TransactionRepositoryTest extends Test_Feature
{
    private TransactionRepository $repo;
    private ConfigContract $config;
    private System $system;
    private Factory_Orm_Transaction $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->repo = Container::get(TransactionRepository::class);
        $this->config = Container::get(ConfigContract::class);
        $this->system = Container::get(System::class);

        $this->factory = Container::get(Factory_Orm_Transaction::class);
    }

    /** @test */
    public function getPendingTransactions__has_dates_in_range(): void
    {
        // Given
        $date = (new DateTimeImmutable('-30 minutes'))->format('Y-m-d H:i:s');
        $this->factory::create(3, [
            'status' => Helpers_General::STATUS_TRANSACTION_PENDING,
            'date' => $date
        ]);

        // When
        $actual = $this->repo->getPendingTransaction(...$this->getPreparedCriteria());

        // Then
        $this->assertNotEmpty($actual);
    }

    /** @test */
    public function getPendingTransactions__has_dates_in_range__returns_limited_results(): void
    {
        // Given
        $limit = 2;

        $date = (new DateTimeImmutable('-30 minutes'))->format('Y-m-d H:i:s');
        $this->factory::create(3, [
            'status' => Helpers_General::STATUS_TRANSACTION_PENDING,
            'date' => $date
        ]);

        // When
        $actual = count($this->repo->getPendingTransaction(...$this->getPreparedCriteria($limit)));

        // Then
        $this->assertSame($limit, $actual);
    }

    private function getPreparedCriteria($limit = 5): array
    {
        $retryIntervalMins = 3;
        $lastAttemptDate = $this->system->date()->modify("- $retryIntervalMins minutes");

        $minimalAgeMins = 15;
        $dateTo = $this->system->date()->modify("- $minimalAgeMins minutes");

        $maxAge = '7 days';
        $dateFrom = $this->system->date()->modify("- $maxAge");

        return [$dateFrom, $dateTo, $lastAttemptDate, $limit];
    }
}
