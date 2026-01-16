<?php

namespace Tests\Feature\Tasks;

use Carbon\Carbon;
use Fuel\Tasks\Delete_Old_Logs;
use Services\Files\FileService;
use Test_Feature;

final class DeleteOldLogsTest extends Test_Feature
{
    private Delete_Old_Logs $deleteOldLogs;
    private int $deleteLogsAfterXDays;
    private Carbon $now;
    private FileService $fileService;

    public function setUp(): void
    {
        parent::setUp();
        $this->deleteOldLogs = $this->container->get(Delete_Old_Logs::class);
        $this->deleteLogsAfterXDays = $this->deleteOldLogs::INTERVAL_IN_DAYS;
        $this->fileService = $this->container->get(FileService::class);
        $this->now = new Carbon();
    }

    /** @test */
    public function run_shouldRemoveOlderThanXDays(): void
    {
        $pathToOldFile = $this->createLogFile();
        $oldFileName = pathinfo($pathToOldFile)['basename'];

        $fullPathToFiles = $this->deleteOldLogs->getFullFilesPath();

        $filesBeforeDelete = scandir($fullPathToFiles);
        // check if correctly detects old files
        $this->assertTrue(in_array($oldFileName, $filesBeforeDelete));

        $this->deleteOldLogs->run();

        $filesAfterDelete = scandir($fullPathToFiles);
        $deletedFiles = array_diff($filesBeforeDelete, $filesAfterDelete);

        // checks if file was successfully deleted
        $this->assertTrue(in_array($oldFileName, $deletedFiles));
    }

    /** @test */
    public function run_noFilesExistsShouldDoNothing(): void
    {
        $this->deleteOldLogs->now = Carbon::now();
        $this->deleteOldLogs->dateBeforeDayInterval = Carbon::now();
        $this->createLogFile(false);

        $fullPathToFiles = $this->deleteOldLogs->getFullFilesPath();
        $filesBeforeDelete = scandir($fullPathToFiles);

        $this->deleteOldLogs->run();

        $filesAfterDelete = scandir($fullPathToFiles);
        $deletedFiles = array_diff($filesBeforeDelete, $filesAfterDelete);

        // checks if file was successfully deleted
        $this->assertEmpty($deletedFiles);
    }

    /** @test */
    public function getBaseFilePath_willReturnTestingPath(): void
    {
        $validLogPath = '/var/log/php/whitelotto/tests/';

        $this->assertSame($validLogPath, $this->deleteOldLogs->getBaseFilePath());
    }

    /** @test */
    public function getFullFilesPath_ShouldReturnValidFolderBasedOnCarbon(): void
    {
        $yearAndMonthBeforeXDays = $this->now->subDays($this->deleteLogsAfterXDays)->format('Y/m/');
        $validFull = $this->deleteOldLogs->getBaseFilePath() . $yearAndMonthBeforeXDays;

        $actual = $this->deleteOldLogs->getFullFilesPath();
        $this->assertStringContainsString($yearAndMonthBeforeXDays, $actual);
        $this->assertSame($validFull, $actual);
    }

    public function createLogFile(bool $asOld = true): string
    {
        $logFolder = $this->deleteOldLogs->getBaseFilePath();
        $date = $asOld ? $this->now->subDays($this->deleteLogsAfterXDays) : $this->now;
        $filePath = $logFolder . $date->format('Y/m/d') . '.log';

        $this->fileService->createIfNotExists($filePath, true);

        return $filePath;
    }
}
