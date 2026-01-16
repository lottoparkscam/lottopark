<?php

namespace Services\Logs;

use Carbon\Carbon;
use Container;
use Fuel\Core\CacheNotFoundException;
use Helpers_Time;
use Services\CacheService;
use Wrappers\Decorators\ConfigContract;
use Core\App;
use Exception;
use Exceptions\Files\FileNotFoundException;
use Services\Files\FileService;
use Interfaces\Logs\LogsInterface;
use Throwable;
use Traits\Logs\LogTrait;

/**
 * NOTICE: We use separate methods for info/error etc. in order to have better backtrace
 */
class FileLoggerService extends AbstractLoggerService implements LogsInterface
{
    use LogTrait;

    protected App $app;
    protected FileService $fileService;

    protected CacheService $cacheService;

    public string $logFilePath = '';
    public string $logFileFolder = '';
    public string $logFileName = '';
    public const MAX_FILE_SIZE_IN_GB = 1;
    public const LOG_TYPE_ERROR = 'error';
    public const LOG_TYPE_WARNING = 'warning';
    public const LOG_TYPE_INFO = 'info';

    public function __construct(
        ConfigContract $configContract,
        App $app,
        FileService $fileService,
        LogObject $log,
        CacheService $cacheService,
    ) {
        parent::__construct($configContract, $log);
        $this->app = $app;
        $this->fileService = $fileService;
        $this->cacheService = $cacheService;
    }

    public function configure(array $debugBackTrace = [], bool $getDayBefore = false): void
    {
        $this->setLogFilePath($getDayBefore);
        parent::configure($debugBackTrace);
    }

    public function yesterdayFileExists(): bool
    {
        try {
            $this->fileService->exists($this->logFilePath);
            return true;
        } catch (FileNotFoundException $e) {
            return false;
        }
    }

    /**
     * @param string $source determines where log was triggered.
     * 
     * Available sources:
     * 
     * platform - logs from our php functions. Logs from try()catch{}, custom logs.
     * platform wordpress - logs from our wp functions. Logs from try()catch{}, custom logs.
     * wordpress - built-in wordpress logs. Mainly it should be errors from wp-include. 
     * fuel - built-in fuel logs. Mainly it should be syntax errors, invalid strict types. 
     */
    public function appendLogToFile(string $message, string $type, string $source): array
    {
        $isFileNotCreatedSuccessfully = !$this->fileService->createIfNotExists($this->logFilePath, true);
        if ($isFileNotCreatedSuccessfully) {
            return [
                'success' => false,
                'isFileError' => true,
                'message' => $message . ' ' . $type . ' ' . $source,
                'exception' => 'Cannot create log file'
            ];
        }

        // do not save log to file if file size limit is reached
        $isFileSizeAboveLimit = $this->fileService->convertSizeFromBytes(filesize($this->logFilePath), 'GB', 10) > self::MAX_FILE_SIZE_IN_GB;
        if ($isFileSizeAboveLimit) {
            return [
                'success' => false,
                'isFileError' => true,
                'message' => 'Log file has reached limit of ' . self::MAX_FILE_SIZE_IN_GB . ' GB',
                'exception' => 'Logs will not be appended to file.'
            ];
        };

        $logLine = $this->prepareLogLine($message, $type, $source);
        $logLineToCheckUniqueness = [
            $logLine->message,
            $logLine->url,
            $logLine->file
        ];

        $logWasAddedBefore = $this->fileService->fileContains($this->logFilePath, $logLineToCheckUniqueness);
        if ($logWasAddedBefore) {
            // this log has been added to file before,
            // do not add it again in order to avoid huge log file size 
            return [
                'success' => true,
                'isFileError' => false
            ];
        }

        try {
            $isSuccess = error_log($logLine, 3, $this->logFilePath);
        } catch (Throwable $e) {
            return [
                'success' => false,
                'isFileError' => true,
                'message' => $message . ' ' . $type . ' ' . $source,
                'exception' => $e->getMessage()
            ];
        }

        return ['success' => $isSuccess, 'isFileError' => false];
    }

    public function setLogFilePath(bool $getDayBefore = false): void
    {
        $this->now = $getDayBefore ? Carbon::now()->subDay() : Carbon::now();

        $this->logFileFolder = $this->getPathToLogFileFolder();
        $this->logFileName = $this->getLogFileName();
        $this->logFilePath = $this->getPathToLogFile();
    }

    public function setYesterdayLogFilePath(): void
    {
        $this->setLogFilePath(true);
    }

    public function error(string $message, bool $dontSendToSlack = false): bool
    {
        $trace = (new Exception())->getTrace();

        return $this->appendLogicWrapper(
            $trace,
            $message,
            self::TYPE_ERROR,
            self::SOURCE_PLATFORM,
            $dontSendToSlack
        );
    }

    public function warning(string $message): bool
    {
        $trace = (new Exception())->getTrace();

        return $this->appendLogicWrapper(
            $trace,
            $message,
            self::TYPE_WARNING
        );
    }

    public function info(string $message): bool
    {
        $trace = (new Exception())->getTrace();

        return $this->appendLogicWrapper(
            $trace,
            $message,
            self::TYPE_INFO
        );
    }

    public function assistant(string $message): bool
    {
        $trace = (new Exception())->getTrace();

        return $this->appendLogicWrapper(
            $trace,
            $message,
            self::TYPE_ASSISTANT
        );
    }

    public function appendLogicWrapper(
        array $trace,
        string $message,
        string $type,
        string $source = self::SOURCE_PLATFORM,
        bool $dontSendToSlack = false
    ): bool {
        /** @var SlackLoggerService $slackLoggerService */
        $slackLoggerService = Container::get(SlackLoggerService::class);

        $this->configure($trace);
        $appendResult = $this->appendLogToFile($message, $type, $source);
        $isSlackConfiguredProperly = $slackLoggerService->slackService->isSlackConfiguredProperly();

        $shouldSendMessageToSlack = $appendResult['isFileError'] && $appendResult['success'] && $isSlackConfiguredProperly;
        if ($shouldSendMessageToSlack && !$dontSendToSlack) {
            $slackLoggerService->slackService->setIsFileError();

            $slackLoggerService->slackService->error(
                'Error appeared during appending log to file: ' . "\n\n" .
                    '*Received exception:* ' . $appendResult['exception'] . "\n\n" .
                    '*Received log:* ' . $this->log->getLogDetails()
            );
            return $appendResult['isFileError'];
        }

        $isSyncLogsTaskDisabled = !$this->configContract->get('slack.syncLogs') && $isSlackConfiguredProperly;
        $isLocEnv = $this->app->isDevelopment() && $isSlackConfiguredProperly;
        $shouldSendRapidLog = $isSyncLogsTaskDisabled || $isLocEnv;
        if ($shouldSendRapidLog) {
            $slackLoggerService->setSource($this->source);
            $slackLoggerService->sendLog($this->log);
        }

        return $appendResult['success'];
    }

    public function shouldSendLogWhenProblemExistsAfterGivenTime(
        int $timeInHours,
        string $exceptionMessage,
        string $cacheKey,
        string $logType,
    ): void {
        $cacheTimeInSecond = ($timeInHours * Helpers_Time::HOUR_IN_SECONDS) + Helpers_Time::HOUR_IN_SECONDS;
        try {
            $lastUpdateAttempt = $this->cacheService->getGlobalCache($cacheKey);
        } catch (CacheNotFoundException) {
            $lastUpdateAttempt = Carbon::now()->format(Helpers_Time::DATETIME_FORMAT);
            $this->cacheService->setGlobalCache($cacheKey, $lastUpdateAttempt, $cacheTimeInSecond);
        }

        $shouldSendLog = Carbon::parse($lastUpdateAttempt)->diffInHours(Carbon::now()) >= $timeInHours;

        if ($shouldSendLog) {
            switch ($logType) {
                case self::LOG_TYPE_ERROR:
                    $this->error($exceptionMessage);
                    break;
                case self::LOG_TYPE_INFO:
                    $this->info($exceptionMessage);
                    break;
                case self::LOG_TYPE_WARNING:
                    $this->warning($exceptionMessage);
                    break;
                default:
                    throw new \InvalidArgumentException("Unknown log type: {$logType}");
            }
        }
    }
}
