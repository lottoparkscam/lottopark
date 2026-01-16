<?php

namespace Fuel\Tasks;

use Throwable;
use Carbon\Carbon;
use Helpers\StringHelper;
use Services\Logs\SlackLoggerService;
use Container;
use Exceptions\Files\FileNotFoundException;
use Fuel\Core\FileAccessException;
use Fuel\Core\OutsideAreaException;
use Helpers\ArrayHelper;
use Services\Logs\FileLoggerService;
use Helpers_Time;
use Services\Files\FileService;
use Traits\Logs\LogTrait;
use Wrappers\Decorators\ConfigContract;
use Services\Logs\LogObject;

class Sync_Logs
{
    use LogTrait;

    private const LOG_ASSISTANT_PREFIX = 'LOG_ASSISTANT | ';
    private array $logCountFrequency;
    public array $allLogsAsArray;
    public float $timeStart;
    public float $executionTimeInSeconds;
    protected string $pathToSyncDetails;

    protected SlackLoggerService $slackLoggerService;
    protected FileLoggerService $fileLoggerService;
    protected FileService $fileService;
    protected ConfigContract $configContract;
    private LogObject $log;

    public const LOG_ASSISTANT_MESSAGES = [
        'yesterdayFileChecked' => self::LOG_ASSISTANT_PREFIX . 'Yesterday file has been checked successfully.',
        'yesterdayFileSynchronized' => self::LOG_ASSISTANT_PREFIX . 'Yesterday file has been synchronized successfully.',
        'synchronizingYesterdayFile' => self::LOG_ASSISTANT_PREFIX . 'Synchronizing yesterday file.',
        'nothingToSync' => self::LOG_ASSISTANT_PREFIX . "Nothing to sync :dove_of_peace: \n Log file does not have new logs.",
        'synchronizedAt' => self::LOG_ASSISTANT_PREFIX . 'Synchronized at: ',
        'executionTime' => self::LOG_ASSISTANT_PREFIX . 'Execution time in seconds: ',
        'yesterdayFileNotExists' => self::LOG_ASSISTANT_PREFIX . 'Yesterday log file does not exist. ',
        'logFileNotCreated' => self::LOG_ASSISTANT_PREFIX . 'Log file has not been created yet. ',
        'wrongFilePermissions' => self::LOG_ASSISTANT_PREFIX . 'Wrong permissions to open log file: ',
    ];

    public function __construct()
    {
        $this->slackLoggerService = Container::get(SlackLoggerService::class);
        $this->slackLoggerService->configure();
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->fileLoggerService->configure();
        $this->fileService = Container::get(FileService::class);
        $this->log = Container::get(LogObject::class);
        $this->configContract = Container::get(ConfigContract::class);

        $this->timeStart = microtime(true);
    }

    /** If task doesn't pass this validation it stops the task. */
    public function validate(): bool
    {
        try {
            $this->fileService->exists($this->fileLoggerService->logFilePath);
        } catch (FileNotFoundException $e) {
            $this->slackLoggerService->slackService->info(
                self::LOG_ASSISTANT_MESSAGES['logFileNotCreated'] . $e->getMessage()
            );
            return false;
        }

        $pathToSyncDetails = $this->getBaseFilePath() . 'SyncDetails.log';
        $isSyncDetailsFileNotCreated = !$this->fileService->createIfNotExists($pathToSyncDetails, true);
        if ($isSyncDetailsFileNotCreated) {
            $this->slackLoggerService->slackService->error('Cannot create SyncDetails.log file.');
            return false;
        }

        $this->pathToSyncDetails = $pathToSyncDetails;

        return true;
    }

    /**
     * Steps:
     * 1. Check if today file exists
     * 2. Check if yesterday file was synchronized
     * 3. If wasn't and yesterday file exists synchronize it and add info to today file
     */
    public function run(): void
    {
        $isNotValid = !$this->validate();
        if ($isNotValid) {
            return;
        }

        $this->allLogsAsArray = $this->getAllLogsAsArray();
        $notSynchronizedLogs = $this->getNotSynchronizedLogs();
        if (empty($notSynchronizedLogs)) {
            $this->slackLoggerService->slackService->assistant(self::LOG_ASSISTANT_MESSAGES['nothingToSync']);
        }

        $this->fileLoggerService->setYesterdayLogFilePath();
        if ($this->hasNotCheckedYesterdayFile()) {
            if ($this->shouldResetSyncDetails()) {
                try {
                    $lastLine = $this->fileService->getLastLines($this->pathToSyncDetails);
                    $nextLineToSync = json_decode($lastLine)->nextLineToSynchronize ?? 0;
                } catch (FileNotFoundException $e) {
                    $nextLineToSync = 0;
                }

                try {
                    $this->fileService->update($this->pathToSyncDetails, '');
                } catch (Throwable $e) {
                    $this->slackLoggerService->slackService->error('Cannot reset SyncDetails.log. Path to file: ' . $this->pathToSyncDetails . $e->getMessage());
                    return;
                }
            }
            $this->synchronizeYesterdayFile($nextLineToSync);
            $this->executionTimeInSeconds = round(((microtime(true) - $this->timeStart)), 2);
            $this->slackLoggerService->slackService->assistant(self::LOG_ASSISTANT_MESSAGES['executionTime'] . $this->executionTimeInSeconds);
            return;
        }

        $this->fileLoggerService->setLogFilePath();
        $this->synchronizeUniqueLogs($notSynchronizedLogs);
        $this->executionTimeInSeconds = round(((microtime(true) - $this->timeStart)), 2);
        $this->slackLoggerService->slackService->assistant(self::LOG_ASSISTANT_MESSAGES['executionTime'] . $this->executionTimeInSeconds);
    }

    public function getLogCount(LogObject $log): int
    {
        $logMessage = $log->type . $log->message . $log->url . $log->file;
        $logCount = $this->logCountFrequency[$logMessage] ?? 1;
        return $logCount;
    }

    /**
     * if success appends synchronized at info
     * if fail adds synchronized at info before error log
     * We can get timeout so its enough to send it in next sync; we don't have to handle it better
     * @param LogObject[] $logs
     */
    private function synchronizeUniqueLogs(array $logs): bool
    {
        if (empty($logs)) {
            return true;
        }

        foreach ($logs as $lineNumber => $log) {
            $logs[$lineNumber]->count = $this->getLogCount($log);
        }

        $sendResult = $this->slackLoggerService->sendLogs($logs);
        $isSuccess = $sendResult['success'];
        if (!$isSuccess) {
            $this->addSynchronizedAtLineToFile($sendResult['line']);
            return $isSuccess;
        }

        $lastLine = array_key_last($this->allLogsAsArray);
        $this->addSynchronizedAtLineToFile($lastLine);

        return $isSuccess;
    }

    private function appendToSyncDetails(string $message): bool
    {
        $lastSyncLog = json_decode($this->fileService->getLastLines($this->pathToSyncDetails), true);
        $messageObject = json_decode($message, true);
        unset($lastSyncLog['synchronizedAt']);
        unset($messageObject['synchronizedAt']);

        $shouldNotAppendLog = $lastSyncLog === $message;
        if ($shouldNotAppendLog) {
            return true;
        }

        $isNotSuccess = !$this->fileService->append($this->pathToSyncDetails, $message);
        if ($isNotSuccess) {
            $this->slackLoggerService->slackService->error('Cannot append log to: ' . $this->pathToSyncDetails);
            return false;
        }

        return true;
    }

    private function addSynchronizedAtLineToFile(int $logLine): bool
    {
        $now = Carbon::now()->format(Helpers_Time::DATETIME_FORMAT);
        $logJson = json_encode([
            'message' => self::LOG_ASSISTANT_MESSAGES['synchronizedAt'],
            'synchronizedAt' => $now,
            'file' => $this->fileLoggerService->logFilePath,
            'lastSynchronizedLine' => $logLine,
            'nextLineToSynchronize' => ++$logLine
        ]);
        return $this->appendToSyncDetails($logJson);
    }

    private function getYesterdayFileSynchronizedMessage(): string
    {
        return json_encode([
            'message' => self::LOG_ASSISTANT_MESSAGES['yesterdayFileSynchronized'],
            'yesterdayFile' => $this->fileLoggerService->logFilePath
        ]);
    }

    /**
     * @throws FileAccessException
     * @throws OutsideAreaException
     */
    protected function getNotSynchronizedLogs(): array
    {
        $lineWithLastSync = $this->getLineForStartSync();
        $this->prepareCountLogFrequency($lineWithLastSync);
        $notSynchronizedLogs = $this->getUniqueLogs($lineWithLastSync);

        return $notSynchronizedLogs;
    }

    /** @return int 0 if file hasn't been synchronized yet */
    private function getLineForStartSync(): int
    {
        $lastSyncLog = json_decode($this->fileService->getLastLines($this->pathToSyncDetails));

        $lastSyncLogFile = $lastSyncLog->file ?? '';
        $lastSyncWasForYesterdayFile = $lastSyncLogFile !== $this->fileLoggerService->logFilePath;
        if ($lastSyncWasForYesterdayFile) {
            return 0;
        }

        return $lastSyncLog->nextLineToSynchronize ?? 0;
    }

    private function prepareCountLogFrequency(int $logsAfterLine = 0): void
    {
        $allLogs = ArrayHelper::arraySpliceWithoutKeys($this->allLogsAsArray, $logsAfterLine);

        // returns message with url; one error can appear in many places
        $allLogsMessages = array_map(function ($line) {
            return $line->type . $line->message . $line->url . $line->file;
        }, $allLogs);

        $this->logCountFrequency = array_count_values($allLogsMessages);
    }

    private function getUniqueLogs(int $logsAfterLine = 0): array
    {
        $uniqueLogs = $uniqueLogMessages = [];

        $logLines = ArrayHelper::arraySpliceWithoutKeys($this->allLogsAsArray, $logsAfterLine);
        foreach ($logLines as $line => $log) {
            $logContent = $log->url . $log->message . $log->file;
            $isUnique = !in_array($logContent, $uniqueLogMessages) && !empty($logContent);
            $isNotUnique = in_array($logContent, $uniqueLogMessages) && !empty($logContent);

            if ($isUnique) {
                $uniqueLogs[$line] = $log;
                $uniqueLogMessages[] = $logContent;
                continue;
            }

            if ($isNotUnique) {
                $previousIndex = array_search($log, $uniqueLogs);
                if (!$previousIndex) {
                    continue;
                }
                unset($uniqueLogs[$previousIndex]);
                $uniqueLogs[$line] = $log;
            }
        }

        return array_filter($uniqueLogs);
    }

    protected function getAllLogsAsArray(): array
    {
        try {
            $getFromLine = $this->getLineForStartSync();
            $lines = $this->fileService->getAfterLine($this->fileLoggerService->logFilePath, $getFromLine);
            $lines = array_filter($lines);
            $lines = array_map(function ($line) {
                $log = new $this->log(json_decode($line));
                return $log;
            }, $lines);
        } catch (Throwable $e) {
            $lines = [];
            $this->slackLoggerService->slackService->error('Error during sync_logs task. Cannot open file.' . $e->getMessage());
            echo 'Error during sync_logs task. Cannot open file.' . $e->getMessage();
            die;
        }

        return $lines;
    }

    private function shouldResetSyncDetails(): bool
    {
        $yesterdayFileSyncInfo = $this->fileService->getFirstLine($this->pathToSyncDetails);
        $yesterdayFileFromSyncDetails = json_decode($yesterdayFileSyncInfo)->yesterdayFile ?? '';
        return $yesterdayFileFromSyncDetails !== $this->fileLoggerService->logFilePath;
    }

    private function hasNotCheckedYesterdayFile(): bool
    {
        $yesterdayFileDoesNotExist = !$this->fileLoggerService->yesterdayFileExists();
        $wasNotPrependYesterdaySyncInfoBefore = $this->fileService->getFirstLine($this->pathToSyncDetails) !== $this->getYesterdayFileSynchronizedMessage();

        $shouldPrependYesterdaySyncInfoLog = $yesterdayFileDoesNotExist && $wasNotPrependYesterdaySyncInfoBefore;
        if ($shouldPrependYesterdaySyncInfoLog) {
            $this->fileService->prepend($this->pathToSyncDetails, $this->getYesterdayFileSynchronizedMessage());
            return false;
        }

        $yesterdayFileSyncInfo = $this->fileService->getFirstLine($this->pathToSyncDetails);

        if ($this->shouldResetSyncDetails()) {
            return true;
        }

        $isNotSynchronized = $yesterdayFileSyncInfo !== $this->getYesterdayFileSynchronizedMessage();

        return $isNotSynchronized;
    }

    private function synchronizeYesterdayFile(int $nextLineToSync = 0): void
    {
        // set all logs from yesterday file
        $this->allLogsAsArray = $this->getAllLogsAsArray();

        $this->slackLoggerService->slackService->assistant(self::LOG_ASSISTANT_MESSAGES['synchronizingYesterdayFile']);
        $uniqueLogs = $this->getUniqueLogs($nextLineToSync);
        $isSuccess = $this->synchronizeUniqueLogs($uniqueLogs);

        if ($isSuccess) {
            $this->fileService->prepend($this->pathToSyncDetails, $this->getYesterdayFileSynchronizedMessage());
            $this->slackLoggerService->slackService->assistant(self::LOG_ASSISTANT_MESSAGES['yesterdayFileSynchronized']);
        }

        unset($this->allLogsAsArray);
    }
}
