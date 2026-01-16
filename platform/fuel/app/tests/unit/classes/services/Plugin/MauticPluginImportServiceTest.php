<?php

declare(strict_types=1);

namespace Tests\Unit\Classes\Services\Plugin;

use Services\Plugin\MauticPluginImportService;
use Test_Unit;

final class MauticPluginImportServiceTest extends Test_Unit
{
    private MauticPluginImportService $mauticImportService;

    public function setUp(): void
    {
        parent::setUp();

        $this->mauticImportService = $this->container->get(MauticPluginImportService::class);
    }

    /**
     * @test
     */
    public function importTaskTimeout(): void
    {
        $expected = MauticPluginImportService::SCHEDULER_INTERVAL - 10;

        $this->assertSame($expected, MauticPluginImportService::getTaskTimeout());
    }

    /**
     * @test
     */
    public function importLockTimeout(): void
    {
        $this->assertSame(1800, MauticPluginImportService::LOCK_TIMEOUT);
    }

    /**
     * @test
     */
    public function importSchedulerInterval(): void
    {
        $this->assertSame(600, MauticPluginImportService::SCHEDULER_INTERVAL);
        $this->assertSame(10, MauticPluginImportService::getSchedulerIntervalInMinutes());
    }

    /**
     * @test
     */
    public function importWhitelabelUsersLimit(): void
    {
        $this->assertSame(50, MauticPluginImportService::WHITELABEL_USERS_LIMIT);
    }

    /**
     * @test
     */
    public function getImportField_ExistingField_ShouldReturnSameValue(): void
    {
        $fieldName = 'status';
        $status = MauticPluginImportService::STATUS_PENDING;

        $this->mauticImportService->setImportStatus($status);

        $actual = $this->mauticImportService->getImportField($fieldName);

        $this->assertSame($status, $actual);
    }

    /**
     * @test
     */
    public function getImportField_ExistingWhitelabelField_ShouldReturnSameValue(): void
    {
        $fieldName = 'sinceUserId';

        $whitelabelId = 1;
        $whitelabelUserId = 10;

        $this->mauticImportService->setImport([$fieldName => $whitelabelUserId], $whitelabelId);

        $actual = (int) $this->mauticImportService->getImportField($fieldName, $whitelabelId);

        $this->assertSame($whitelabelUserId, $actual);
    }

    /**
     * @test
     */
    public function getImportField_NotExistingWhitelabelField_ShouldReturnNull(): void
    {
        $fieldName = 'sinceUserId';

        $whitelabelId = 1;
        $whitelabelUserId = 10;
        $differentWhitelabelId = 2;

        $this->mauticImportService->setImport([$fieldName => $whitelabelUserId], $whitelabelId);

        $actual = $this->mauticImportService->getImportField($fieldName, $differentWhitelabelId);

        $this->assertNull($actual);
    }
}
