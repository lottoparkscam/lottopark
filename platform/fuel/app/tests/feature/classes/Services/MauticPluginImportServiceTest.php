<?php

declare(strict_types=1);

namespace Tests\Feature\Classes\Services;

use Services\Plugin\MauticPluginImportService;
use Test_Feature;

final class MauticPluginImportServiceTest extends Test_Feature
{
    private MauticPluginImportService $mauticImportService;

    private string $importFilename;

    public function setUp(): void
    {
        parent::setUp();

        $this->importFilename = __DIR__ . '/import-test.json';

        $this->mauticImportService = $this->container->get(MauticPluginImportService::class);
    }

    /**
     * @test
     */
    public function importFileExists(): void
    {
        $this->mauticImportService->setImportFilename($this->importFilename);
        $this->mauticImportService->start();

        $status1 = $this->mauticImportService->getImportStatus();

        $this->mauticImportService->finish();

        $status2 = $this->mauticImportService->getImportStatus();

        $this->assertFileExists($this->importFilename);
        $this->assertSame('pending', $status1);
        $this->assertSame('completed', $status2);
    }

    /**
     * @test
     */
    public function importFile_InvalidContent_ShouldRecreateFile(): void
    {
        file_put_contents($this->importFilename, 'wrong format');

        $this->mauticImportService->setImportFilename($this->importFilename);
        $this->mauticImportService->start();

        $this->mauticImportService->finish();

        $status = $this->mauticImportService->getImportStatus();

        $this->assertFileExists($this->importFilename);
        $this->assertSame('completed', $status);
    }

    public function getImportTimeDataProvider(): array
    {
        return [
            'import has just started' => [0, false],
            'import time has not reached the limit' => [230, false],
            'import time exceeded the limit' => [600, true],
        ];
    }

    /**
     * @test
     * @dataProvider getImportTimeDataProvider
     */
    public function isTimeoutExceeded(int $currentImportTimeExecution, bool $expectedIsTimeoutExceeded): void
    {
        $this->mauticImportService->setImportFilename($this->importFilename);

        // Mock that the import has been running since the given time
        $this->mauticImportService->startTimeInSeconds = time() - $currentImportTimeExecution;

        $started = $this->mauticImportService->start();

        $isTimeoutExceeded = $this->mauticImportService->isTimeoutExceeded();

        $this->mauticImportService->finish();

        $this->assertTrue($started);
        $this->assertSame($expectedIsTimeoutExceeded, $isTimeoutExceeded);
        $this->assertSame(0, $this->mauticImportService->startTimeInSeconds);
    }

    public function getIsLockedDataProvider(): array
    {
        $getDateTime = function ($dateTime) {
            return date('Y-m-d H:i:s', strtotime($dateTime));
        };

        return [
            'import in progress - cannot start a new import' => [$getDateTime('-5 minutes'), MauticPluginImportService::STATUS_PENDING, false, true, true],
            'import has got stuck - can start a new import' => [$getDateTime('-35 minutes'), MauticPluginImportService::STATUS_PENDING, true, false, true],
            'import completed earlier - can start a new import' => [$getDateTime('-2 minutes'), MauticPluginImportService::STATUS_COMPLETED, true, false, true],
            'import completed at any time - can start a new import' => [$getDateTime('-1 day'), MauticPluginImportService::STATUS_COMPLETED, true, false, true],
        ];
    }

    /**
     * @test
     * @dataProvider getIsLockedDataProvider
     */
    public function isLocked(
        string $datetimeStarted,
        string $status,
        bool $expectImportStarted,
        bool $expectLockedBeforeStart,
        bool $expectLockedAfterStart
    ): void {

        // Mock previous import
        file_put_contents($this->importFilename, json_encode([
            'datetimeStarted' => $datetimeStarted,
            'status' => $status
        ]));

        $this->mauticImportService->setImportFilename($this->importFilename);
        $this->mauticImportService->loadSettings();

        $isLockedBeforeStart = $this->mauticImportService->isLocked();

        $started = $this->mauticImportService->start();
        $isLockedAfterStart = $this->mauticImportService->isLocked();

        $this->assertSame($expectImportStarted, $started);
        $this->assertSame($expectLockedBeforeStart, $isLockedBeforeStart);
        $this->assertSame($expectLockedAfterStart, $isLockedAfterStart);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        if (file_exists($this->importFilename)) {
            unlink($this->importFilename);
        }
    }
}
