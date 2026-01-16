<?php

namespace Fuel\Tasks;

use Carbon\Carbon;
use Container;
use Fuel\Core\File;
use Throwable;
use Services\Logs\FileLoggerService;
use Traits\Logs\LogTrait;
use Wrappers\Decorators\ConfigContract;

final class Delete_Old_Logs
{
    use LogTrait;
    public const INTERVAL_IN_DAYS = 7;
    public Carbon $now;
    public Carbon $dateBeforeDayInterval;
    private FileLoggerService $fileLoggerService;
    private ConfigContract $configContract;

    public function __construct()
    {
        $this->now = new Carbon();
        $this->dateBeforeDayInterval = $this->now->subDays(self::INTERVAL_IN_DAYS);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->configContract = Container::get(ConfigContract::class);
    }

    public function run(): void
    {
        $successPaths = $failPaths = [];
        $dayNumberBeforeXDays = $this->dateBeforeDayInterval->format('d');

        $files = glob($this->getFullFilesPath() . "*");
        $noFilesToClear = $files === false;
        if ($noFilesToClear) {
            $this->fileLoggerService->assistant(
                'Cannot find any log files to clear!'
            );
            return;
        }

        foreach ($files as $file) {
            if (!is_file($file)) {
                continue;
            }

            $fileName = pathinfo($file)['filename'];
            $isOldLog = (int) $fileName <= $dayNumberBeforeXDays;
            $isNotOldLog = !$isOldLog;
            if ($isNotOldLog) {
                continue;
            }

            $isFileDeleted = $this->removeFile($file);
            if (is_null($isFileDeleted)) {
                continue;
            }
            if ($isFileDeleted) {
                $successPaths[] = $file;
            } else {
                $failPaths[] = $file;
            }
        }

        $successMessage = !empty($successPaths) ? 'Successfully deleted old log files ' . var_export($successPaths, true) . "\n" : '';
        $failMessage = !empty($failPaths) ? 'Cannot delete old log files ' . var_export($failPaths, true) . "\n" : '';

        $summary = $successMessage . $failMessage;
        if (!empty($summary)) {
            $this->fileLoggerService->assistant(
                $successMessage . $failMessage
            );
        }
    }

    public function getFullFilesPath(): string
    {
        $baseFilesPath = $this->getBaseFilePath();
        return $baseFilesPath . $this->dateBeforeDayInterval->format('Y/m/');
    }

    /** @return ?bool null if file doesn't exist */
    private function removeFile(string $pathToFile): ?bool
    {
        if (File::exists($pathToFile)) {
            try {
                return File::delete($pathToFile);
            } catch (Throwable $e) {
                $this->fileLoggerService->error($e->getMessage());
                return false;
            }
        }

        return null;
    }
}
