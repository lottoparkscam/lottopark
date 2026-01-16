<?php

namespace Tests\Feature\Classes\Services\Logs;

use Services\Logs\FileLoggerService;
use Test_Feature;
use Fuel\Core\File;
use Models\Whitelabel;
use Model_Whitelabel;
use Services\Logs\LogObject;

final class FileLoggerServiceTest extends Test_Feature
{
    private FileLoggerService $fileLoggerService;
    private string $pathToLogFile;
    private LogObject $log;

    public function setUp(): void
    {
        parent::setUp();
        $this->log = $this->container->get(LogObject::class);
        $this->fileLoggerService = $this->container->get(FileLoggerService::class);
        $this->fileLoggerService->configure();
        $this->pathToLogFile = $this->fileLoggerService->getPathToLogFile();
    }

    /**
     * We don't need to test all log types, they have the same functions for adding logs
     * @test
     */
    public function addErrorLog_shouldAddLogToFile(): void
    {
        $message = 'Test message';
        $this->fileLoggerService->error($message);

        $lastLog = $this->getLastLog();

        $this->assertSame($message, $lastLog->message);
        $this->assertStringContainsString(__FILE__, $lastLog->trace);
        $this->assertStringContainsString(__CLASS__, $lastLog->trace);
    }

    /** @test */
    public function getBackTraceAsStringShouldHideSensitiveDataFromAbstractOrmModel(): void
    {
        $whitelabel = $this->container->get('whitelabel');
        unset($whitelabel->domain);

        $triggerError = function (Whitelabel $whitelabel) {
            if (empty($whitelabel->domain)) {
                $this->fileLoggerService->error('Domain is empty.');
            }
        };

        $triggerError($whitelabel);

        $lastLog = $this->getLastLog();
        $expected = 'Found sensitive data from model: Models\Whitelabel Provided model with id: 1),';

        $this->assertStringContainsString($expected, $lastLog->trace);
    }

    /** @test */
    public function getBackTraceAsStringShouldHideSensitiveDataFromNotOrmModel(): void
    {
        $whitelabel = $this->container->get(Model_Whitelabel::class);
        $wl = $whitelabel->find_by_pk(1);
        unset($wl->domain);

        $triggerError = function (Model_Whitelabel $whitelabel) {
            if (empty($whitelabel->domain)) {
                $this->fileLoggerService->error('Domain is empty.');
            }
        };

        $triggerError($wl);

        $lastLog = $this->getLastLog();
        $expected = 'Found sensitive data from model: Model_Whitelabel Provided model with id: 1';

        $this->assertStringContainsString($expected, $lastLog->trace);
    }

    /** @test */
    public function getBackTraceAsStringShouldHideSensitiveDataFromArray(): void
    {
        $whitelabel = $this->container->get('whitelabel')->to_array();
        unset($whitelabel['domain']);

        $triggerError = function (array $whitelabel) {
            if (empty($whitelabel['domain'])) {
                $this->fileLoggerService->error('Domain is empty.');
            }
        };

        $triggerError($whitelabel);

        $lastLog = $this->getLastLog();
        $expected = 'Found sensitive data from model. Provided model with id: 1';

        $this->assertStringContainsString($expected, $lastLog->trace);
    }

    /** @test */
    public function getBackTraceAsStringModelCalledFromOtherFunctionShouldHideSensitiveData(): void
    {
        $whitelabel = $this->container->get('whitelabel');
        $this->sendLogFromOtherFunction($whitelabel);

        $lastLog = $this->getLastLog();
        $expected = 'Found sensitive data from model: Models\Whitelabel Provided model with id: 1';
        $this->assertStringContainsString($expected, $lastLog->trace);
    }

    private function sendLogFromOtherFunction(Whitelabel $whitelabel): Whitelabel
    {
        $this->fileLoggerService->error('nice test');

        return $whitelabel;
    }

    private function getLogLines(): array
    {
        $lines = File::read($this->pathToLogFile, true);
        return explode("\n", $lines);
    }

    private function getLastLog(): ?LogObject
    {
        $lines = $this->getLogLines();
        // count starts from 1 (not 0) and the last line is empty after append.
        $count = count($lines) - 2;
        $log = json_decode($lines[$count]);

        $this->log->set($log);
        return $this->log;
    }

    /** @test */
    public function appendLogToFile_LogExist_ShouldNotRepeat(): void
    {
        $expectedCount = 2; // last line is empty

        $this->fileLoggerService->appendLogToFile('testing', 'info', 'test');
        $this->fileLoggerService->appendLogToFile('testing', 'info', 'test');
        $this->fileLoggerService->appendLogToFile('testing', 'info', 'test');
        $logs = $this->getLogLines();
        $this->assertCount($expectedCount, $logs);
        $this->assertEmpty($logs[1]);
        $this->assertStringContainsString('testing', $logs[0]);
    }

    /** @test */
    public function appendLogToFile_ShouldBeAbleToAddUniqueLogs(): void
    {
        $expectedCount = 4; // last line is empty

        $this->assertTrue(
            $this->fileLoggerService->info('File log') && // 1
            $this->fileLoggerService->info('Some error') && // 2
            $this->fileLoggerService->warning('some info') // 3
        );


        $logs = $this->getLogLines();

        $this->assertCount($expectedCount, $logs);
        $this->assertEmpty($logs[$expectedCount - 1]);
    }
}
