<?php

namespace Abstracts\Tasks;

use Throwable;
use Helpers_Time;
use Services\Logs\FileLoggerService;
use Container;

abstract class AbstractClearLogs
{
    /** @param mixed $repo */
    protected function clearLogsByDays($repo, int $removeOlderThanXDays, string $dateColumn = 'date'): void
    {
        try {
            $repo->deleteRecordsOlderThanXDays($removeOlderThanXDays, $dateColumn);
        } catch (Throwable $e) {
            $this->addSlackLogIfSomethingWentWrong($e);
        }
    }

    private function addSlackLogIfSomethingWentWrong(Throwable $e): void
    {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $fileLoggerService->error(
            $e->getMessage()
        );
    }

    protected function clearLogsByMinutes($repo, int $minutes, string $dateColumn = 'date'): void
    {
        $removeBeforeTimestamp = Helpers_Time::getTimestampBeforeXMinutes($minutes);

        try {
            $repo->deleteLogsBeforeTimestamp($removeBeforeTimestamp, $dateColumn);
        } catch (Throwable $e) {
            $this->addSlackLogIfSomethingWentWrong($e);
        }
    }
}
