<?php

namespace Tests\Feature\Tasks;

use Carbon\Carbon;
use Exceptions\Files\FileNotFoundException;
use Fuel\Core\File;
use Fuel\Tasks\Sync_Logs;
use Helpers\ArrayHelper;
use Helpers_Time;
use PHPUnit\Framework\MockObject\MockObject;
use Services\Files\FileService;
use Services\Logs\FileLoggerService;
use Services\Logs\SlackLoggerService;
use Services\SlackService;
use Test_Feature;
use Wrappers\Decorators\ConfigContract;
use Services\Logs\LogObject;

final class SyncLogsTest extends Test_Feature
{
    private MockObject $slackLoggerService;
    private MockObject $fileLoggerService;
    private $syncLogs;
    private MockObject $slackService;
    private MockObject $fileService;
    private string $pathToLogFile;
    private string $pathToYesterdayFile;
    private string $pathToSyncDetails;
    private FileLoggerService $realFileLoggerService;
    private Carbon $now;
    private Carbon $dayBefore;
    private ConfigContract $configContract;

    public function setUp(): void
    {
        parent::setUp();
        $this->slackService = $this->createMock(SlackService::class);
        $this->slackLoggerService = $this->createMock(SlackLoggerService::class);
        $this->slackLoggerService->slackService = $this->slackService;
        $this->fileService = $this->createMock(FileService::class);

        $this->fileLoggerService = $this->createMock(FileLoggerService::class);

        $this->realFileLoggerService = $this->container->get(FileLoggerService::class);
        $this->configContract = $this->container->get(ConfigContract::class);

        Carbon::setTestNow('1410-12-10 13:12:11');

        $this->now = Carbon::now();
        $this->dayBefore = Carbon::now()->subDay();

        $this->pathToLogFile = '/var/log/php/whitelotto/tests/' . $this->now->format('Y/m/d') . '.log';
        $this->pathToYesterdayFile = '/var/log/php/whitelotto/tests/' . $this->dayBefore->format('Y/m/d') . '.log';

        $this->fileLoggerService->logFilePath = $this->pathToLogFile;
        $this->pathToSyncDetails = $this->configContract->get('log_path') . 'SyncDetails.log';

        if (!is_dir('/var/log/php/whitelotto/tests/' . $this->now->format('Y/m'))) {
            mkdir('/var/log/php/whitelotto/tests/' . $this->now->format('Y/m'), 755, true);
        }
        file_put_contents($this->pathToSyncDetails, '');
        $this->fileService->method('createIfNotExists')->willReturn(true);
        $this->syncLogs = $this->createTaskMock(
            $this->fileLoggerService,
            $this->slackLoggerService,
            $this->fileService
        );
    }

    public function tearDown(): void
    {
        // files should be unique per test thats why we delete them after test.
        $this->removeTestFile();
    }

    /** @test */
    public function runFileNotFoundShouldStopAndAddInfoLog(): void
    {
        $fileNotFoundException = new FileNotFoundException($this->pathToLogFile);

        $this->fileService
            ->expects($this->once())
            ->method('exists')
            ->willThrowException($fileNotFoundException);

        $this->slackLoggerService->slackService
            ->expects($this->once())
            ->method('info')
            ->withConsecutive([Sync_Logs::LOG_ASSISTANT_MESSAGES['logFileNotCreated'] . $fileNotFoundException->getMessage()]);

        $this->syncLogs->run();
    }

    /** @test */
    public function runFileExistsDoesNotHaveNotSynchronizedLogsShouldAddAssistantLog(): void
    {
        $this->removeTestFile();
        // create empty file
        file_put_contents($this->pathToLogFile, '');

        $this->slackLoggerService->slackService
            ->expects($this->exactly(2))
            ->method('assistant')
            ->withConsecutive([Sync_Logs::LOG_ASSISTANT_MESSAGES['nothingToSync']]);

        $this->syncLogs->run();
    }

    /** @test */
    public function runFileExistsShouldSyncLogs(): void
    {
        $expectedSynchronizedAt = '{"message":"LOG_ASSISTANT | Synchronized at: ","synchronizedAt":"1410-12-10 13:12:11","file":"\/var\/log\/php\/whitelotto\/tests\/1410\/12\/10.log","lastSynchronizedLine":0,"nextLineToSynchronize":1}';
        $expectedMessage = 'Adding new log in order to create log file';
        $this->removeTestFile();
        $this->realFileLoggerService->error($expectedMessage);

        $logsFromFile = $this->getLogsAsArray($this->pathToLogFile);
        $this->assertCount(1, $logsFromFile);
        $logFromFile = new LogObject(json_decode($logsFromFile[0]));

        $this->assertSame($expectedMessage, $logFromFile->message);

        $this->slackLoggerService->slackService
            ->expects($this->atLeastOnce())
            ->method('assistant')
            ->willReturn(true); // check if logs were sent successfully

        $logs = $this->getLogsAsArray($this->pathToLogFile);
        $this->fileService
            ->expects($this->atLeastOnce())
            ->method('getLastLines')
            ->willReturn(null);

        $this->fileService
            ->method('getAfterLine')
            ->with($this->pathToLogFile, 0)
            ->willReturn($logs);

        $this->slackLoggerService
            ->expects($this->once())
            ->method('sendLogs')
            ->with([$logFromFile])
            ->willReturn(['success' => true, 'line' => null]);


        $this->fileService
            ->expects($this->once())
            ->method('append')
            ->with($this->pathToSyncDetails, $expectedSynchronizedAt)
            ->willReturn(true);

        $this->syncLogs->run();
    }

    /** @test */
    public function runUpdatesSyncDetailsCorrectly(): void
    {
        $expectedYesterdaySuccess = json_encode([
            'message' => Sync_Logs::LOG_ASSISTANT_MESSAGES['yesterdayFileSynchronized'],
            'yesterdayFile' => $this->pathToYesterdayFile,
        ]);

        $expectedSyncDetails = json_encode([
            'message' => Sync_Logs::LOG_ASSISTANT_MESSAGES['synchronizedAt'],
            'synchronizedAt' => $this->now->format(Helpers_Time::DATETIME_FORMAT),
            'file' => $this->pathToLogFile,
            'lastSynchronizedLine' => 0,
            'nextLineToSynchronize' => 1
        ]);

        $this->removeTestFile();
        $this->realFileLoggerService->error('Adding new log in order to create log file');

        $logsFromFile = $this->getLogsAsArray($this->pathToLogFile);
        $this->assertCount(1, $logsFromFile);

        $task = $this->container->get(Sync_Logs::class);
        $task->run();

        $syncDetails = $this->getLogsAsArray($this->pathToSyncDetails);
        $this->assertSame($expectedYesterdaySuccess, $syncDetails[0]);
        $this->assertSame($expectedSyncDetails, $syncDetails[1]);
    }

    /** @skipped - currently not used due to changes in FileLoggerService */
    public function synchronizeUniqueLogsShouldProperlyDetectLogCountAndDoNotRepeatItOnSlack(): void
    {
        $expectedLogsCount = 5;

        for ($logCount = $expectedLogsCount; $logCount > 0; $logCount--) {
            $isSuccess = $this->realFileLoggerService->error('Testing duplicated log');
            $this->assertTrue($isSuccess);
        }

        $logs = $this->getLogsAsArray($this->pathToLogFile);
        $this->assertCount(5, $logs);

        $logObject = new LogObject(json_decode($logs[0]));
        $logObject->count = 5;

        $this->fileService
            ->expects($this->atLeastOnce())
            ->method('getLastLines')
            ->willReturn(null);

        $this->fileService
            ->method('getAfterLine')
            ->with($this->pathToLogFile, 0)
            ->willReturn($logs);

        $this->slackLoggerService
            ->expects($this->once())
            ->method('sendLogs')
            ->with([$logObject])
            ->willReturn(['success' => true, 'line' => null]);

        $this->syncLogs->run();
    }

    /** @test */
    public function synchronizeUniqueLogsReceivesExceptionFromSlackShouldAddInfoBeforeProblematicLog(): void
    {
        $expectedSynchronizedAt = '{"message":"LOG_ASSISTANT | Synchronized at: ","synchronizedAt":"1410-12-10 13:12:11","file":"\/var\/log\/php\/whitelotto\/tests\/1410\/12\/10.log","lastSynchronizedLine":0,"nextLineToSynchronize":1}';
        $expectedYesterdayFileSync = '{"message":"LOG_ASSISTANT | Yesterday file has been synchronized successfully.","yesterdayFile":"\/var\/log\/php\/whitelotto\/tests\/1410\/12\/10.log"}';

        $this->removeTestFile();

        $this->fileLoggerService
            ->expects($this->once())
            ->method('yesterdayFileExists')
            ->willReturn(false);

        $this->fileService
            ->expects($this->once())
            ->method('exists')
            ->withConsecutive([$this->pathToLogFile], [$this->pathToYesterdayFile])
            ->will($this->returnValue(true), $this->throwException(new FileNotFoundException($this->pathToYesterdayFile)));

        // add logs to create file
        $this->assertTrue(
            $this->realFileLoggerService->info('1st log..')
                && $this->realFileLoggerService->info('2nd log.. ')
                && $this->realFileLoggerService->info('3rd log.. ')
        );

        $logs = $this->getLogsAsArray($this->pathToLogFile);
        $logsObjects = array_map(function ($log) {
            return new LogObject(json_decode($log));
        }, $logs);

        $this->assertCount(3, $logs);

        $this->slackLoggerService
            ->expects($this->once())
            ->method('sendLogs')
            ->with($logsObjects)
            ->willReturn(['success' => false, 'line' => 0]);

        $this->fileService
            ->expects($this->atLeastOnce())
            ->method('getLastLines')
            ->willReturn(null);

        $this->fileService
            ->expects($this->atLeastOnce())
            ->method('getAfterLine')
            ->with($this->pathToLogFile, 0)
            ->willReturn($logs);

        $this->fileService
            ->expects($this->once())
            ->method('prepend')
            ->with($this->pathToSyncDetails, $expectedYesterdayFileSync)
            ->willReturn(true);

        $this->fileService
            ->expects($this->once())
            ->method('append')
            ->with($this->pathToSyncDetails, $expectedSynchronizedAt);

        $this->syncLogs->run();
    }

    /** @test */
    public function runYesterdayFileExistsShouldSyncIt(): void
    {
        $expectedFileSynchronized = '{"message":"LOG_ASSISTANT | Yesterday file has been synchronized successfully.","yesterdayFile":"\/var\/log\/php\/whitelotto\/tests\/1410\/12\/10.log"}';

        $this->removeTestFile();

        // add logs for both files
        Carbon::setTestNow($this->dayBefore);
        $this->realFileLoggerService->info('Add log to yesterday file');
        Carbon::setTestNow($this->now);
        $this->realFileLoggerService->info('add log to today file ');

        // check if logs were added properly
        $yesterdayLogs = $this->getLogsAsArray($this->pathToYesterdayFile);
        $this->assertCount(1, $yesterdayLogs);
        $todayLogs = $this->getLogsAsArray($this->pathToLogFile);
        $this->assertCount(1, $todayLogs);

        $this->fileLoggerService
            ->expects($this->atLeastOnce())
            ->method('setYesterdayLogFilePath');

        $this->fileService
            ->expects($this->once())
            ->method('exists')
            ->with($this->pathToLogFile)
            ->willReturn(true);

        $this->fileLoggerService
            ->expects($this->once())
            ->method('yesterdayFileExists')
            ->willReturn(true);

        $this->fileService
            ->expects($this->exactly(4))
            ->method('getFirstLine')
            ->with($this->pathToSyncDetails)
            ->willReturn('');

        $this->fileService
            ->expects($this->once())
            ->method('prepend')
            ->with($this->pathToSyncDetails, $expectedFileSynchronized)
            ->willReturn(true);

        $this->syncLogs->run();
    }

    /** @test */
    public function hasNotCheckedYesterdayFile_YesterdayFileExist_ShouldResetSyncDetailsLogAndPrependInfo(): void
    {
        $this->removeTestFile();
        $yesterdayFileSyncInfo = json_encode([
            'message' => Sync_Logs::LOG_ASSISTANT_MESSAGES['yesterdayFileSynchronized'],
            'yesterdayFile' => $this->pathToYesterdayFile
        ]);
        $this->fileLoggerService->logFilePath = $this->pathToYesterdayFile;

        // add logs for both files
        Carbon::setTestNow($this->dayBefore);
        $this->realFileLoggerService->info('Add log to yesterday file');
        Carbon::setTestNow($this->now);
        $this->realFileLoggerService->info('add log to today file ');

        $this->fileLoggerService
            ->expects($this->once())
            ->method('yesterdayFileExists')
            ->willReturn(true);

        $this->fileService
            ->expects($this->once())
            ->method('prepend')
            ->with($this->pathToSyncDetails, $yesterdayFileSyncInfo)
            ->willReturn(true);

        $this->syncLogs->run();
    }

    /** @test */
    public function hasNotCheckedYesterdayFile_YesterdayFileDoesNotExist_ShouldPrependInfo(): void
    {
        $this->removeTestFile($this->pathToYesterdayFile);
        $yesterdayFileSyncInfo = json_encode([
            'message' => Sync_Logs::LOG_ASSISTANT_MESSAGES['yesterdayFileSynchronized'],
            'yesterdayFile' => $this->pathToYesterdayFile
        ]);
        $this->fileLoggerService->logFilePath = $this->pathToYesterdayFile;

        $this->realFileLoggerService->info('add log to today file ');

        $this->fileLoggerService
            ->expects($this->once())
            ->method('yesterdayFileExists')
            ->willReturn(false);

        $this->fileService
            ->expects($this->once())
            ->method('prepend')
            ->with($this->pathToSyncDetails, $yesterdayFileSyncInfo)
            ->willReturn(true);

        $this->syncLogs->run();
    }

    /** @test */
    public function hasNotCheckedYesterdayFile_YesterdayFileExists_ShouldPrependInfoOnce(): void
    {
        $this->removeTestFile($this->pathToYesterdayFile);
        $yesterdayFileSyncInfo = json_encode([
            'message' => Sync_Logs::LOG_ASSISTANT_MESSAGES['yesterdayFileSynchronized'],
            'yesterdayFile' => $this->pathToYesterdayFile
        ]);

        $this->fileLoggerService->logFilePath = $this->pathToYesterdayFile;

        $this->fileService
            ->expects($this->atLeastOnce())
            ->method('getFirstLine')
            ->with($this->pathToSyncDetails)
            ->willReturn($yesterdayFileSyncInfo);

        // add logs for both files
        Carbon::setTestNow($this->dayBefore);
        $this->realFileLoggerService->info('Add log to yesterday file');
        Carbon::setTestNow($this->now);
        $this->realFileLoggerService->info('add log to today file ');

        $this->fileLoggerService
            ->expects($this->once())
            ->method('yesterdayFileExists')
            ->willReturn(true);

        $this->fileService
            ->expects($this->never())
            ->method('prepend');

        $this->syncLogs->run();
    }

    /** @skipped - currently not used due to changes in FileLoggerService */
    public function getUniqueLogsShouldHaveLatestUniqueLine(): void
    {
        // add logs
        Carbon::setTestNow($this->now);

        for ($i = 0; $i < 3; $i++) {
            $this->realFileLoggerService->info('test');
        }

        $logs = $this->getLogsAsArray($this->pathToLogFile);
        foreach ($logs as $line => $log) {
            $logJson = json_decode($log);
            $logs[$line] = new LogObject($logJson);
        }

        $this->fileService
            ->expects($this->once())
            ->method('getAfterLine')
            ->with($this->pathToLogFile, 0)
            ->willReturn($logs);

        $this->fileLoggerService
            ->expects($this->once())
            ->method('yesterdayFileExists')
            ->willReturn(false);

        $logs[2]->count = 3;

        $this->slackLoggerService
            ->expects($this->once())
            ->method('sendLogs')
            ->with([$logs[2]])
            ->willReturn(['success' => true, 'line' => null]);

        $this->syncLogs->run();
    }

    private function getLogsAsArray(string $pathToLogFile): array
    {
        $file = File::read($pathToLogFile, true);
        $logs = explode("\n", $file);
        return ArrayHelper::arraySpliceWithoutKeys($logs, 0);
    }

    /**
     * We create this anonymous class in order to have possibility to set mocks for this task.
     * I've used wrappers here to avoid changing methods ' access.
     */
    private function createTaskMock(
        FileLoggerService $fileLoggerService,
        SlackLoggerService $slackLoggerService,
        FileService $fileService
    ): Sync_Logs {
        return new class ($fileLoggerService, $slackLoggerService, $fileService) extends Sync_Logs
        {
            public function __construct(
                FileLoggerService $fileLoggerService,
                SlackLoggerService $slackLoggerService,
                FileService $fileService
            ) {
                parent::__construct();
                $this->fileLoggerService = $fileLoggerService;
                $this->slackLoggerService = $slackLoggerService;
                $this->fileService = $fileService;
            }
        };
    }

    private function removeTestFile(string $pathToLogFile = ''): void
    {
        $pathToLogFile = !empty($pathToLogFile) ? $pathToLogFile : $this->pathToLogFile;
        if (is_file($pathToLogFile)) {
            unlink($pathToLogFile);
        }

        if (is_file($this->pathToYesterdayFile)) {
            unlink($this->pathToYesterdayFile);
        }
    }
}
