<?php

declare(strict_types=1);

namespace Services\Plugin;

use Exception;
use Helpers_Time;

class MauticPluginImportService extends MauticPluginService
{
    public const LOCK_TIMEOUT = 1800;
    public const SCHEDULER_INTERVAL = 600;
    public const WHITELABEL_USERS_LIMIT = 50;
    public const STATUS_PENDING = 'pending';
    public const STATUS_QUEUED = 'queued';
    public const STATUS_COMPLETED = 'completed';
    // e.g. when no users were found
    public const STATUS_SKIPPED = 'skipped';
    public int $startTimeInSeconds = 0;
    private string $importFilename = APPPATH . '/mautic-import.json';
    private array $importSettings = [];

    public static function getSchedulerIntervalInMinutes(): int
    {
        return (int)(self::SCHEDULER_INTERVAL / Helpers_Time::MINUTE_IN_SECONDS);
    }

    /**
     * Scheduler interval is set to 300s in Production, so 290s is a safe limit to terminate a task
     * before starting the next one and eliminate overlapping effects.
     */
    public static function getTaskTimeout(): int
    {
        return self::SCHEDULER_INTERVAL - 10;
    }

    public function loadSettings(): void
    {
        if (file_exists($this->importFilename)) {
            $import = json_decode(file_get_contents($this->importFilename), true);

            if (is_array($import)) {
                $this->importSettings = $import;
            }
        }
    }

    public function start(): bool
    {
        $this->importSettings = [];

        if ($this->startTimeInSeconds === 0) {
            $this->startTimeInSeconds = time();
        }

        set_time_limit(self::getTaskTimeout());

        $this->loadSettings();

        if ($this->isLocked(false)) {
            return false;
        }

        $this->setImport([
            'status' => self::STATUS_PENDING,
            'usersLimit' => self::WHITELABEL_USERS_LIMIT,
            'totalTime' => '0s',
            'datetimeStarted' => date('Y-m-d H:i:s', $this->startTimeInSeconds),
            'datetimeEnded' => null
        ]);

        $this->saveImport();

        return true;
    }

    public function finish(): void
    {
        $this->setImportStatus(self::STATUS_COMPLETED);
        $this->setImport([
            'totalTime' => $this->getExecutionTimeInSeconds() . 's',
            'datetimeEnded' => date('Y-m-d H:i:s')
        ]);

        $this->saveImport();

        $this->startTimeInSeconds = 0;
    }

    public function setTotalTime(): void
    {
        $this->importSettings['totalTime'] = $this->getExecutionTimeInSeconds() . 's';
    }

    public function setImportFilename(string $filename): void
    {
        $this->importFilename = $filename;
    }

    public function setImportStatus(string $status, ?int $whitelabelId = null): void
    {
        $this->setImport(['status' => $status], $whitelabelId);
    }

    public function setImport(array $settings, ?int $whitelabelId = null): void
    {
        if ($whitelabelId !== null) {
            if (!isset($this->importSettings['whitelabel'][$whitelabelId])) {
                $this->importSettings['whitelabel'][$whitelabelId] = [];
            }

            $this->importSettings['whitelabel'][$whitelabelId] = array_merge($this->importSettings['whitelabel'][$whitelabelId], $settings);
        } else {
            $this->importSettings = array_merge($this->importSettings, $settings);
        }
    }

    public function setWhitelabelQueue(array $whitelabelQueue): void
    {
        foreach ($whitelabelQueue as $whitelabelId => $whitelabel) {
            if (!isset($this->importSettings['whitelabel'][$whitelabelId])) {
                $this->importSettings['whitelabel'][$whitelabelId] = [];
            }

            $this->importSettings['whitelabel'][$whitelabelId] =
                array_merge($this->importSettings['whitelabel'][$whitelabelId], $whitelabel);
        }
    }

    /**
     * The task is locked when it has 'pending' status.
     * However, it is possible that for some reason the import got stuck
     * for a long time - e.g. > 30 minutes so a new import can be started.
     */
    public function isLocked(bool $reloadSettings = true): bool
    {
        if ($reloadSettings) {
            $this->loadSettings();
        }

        $isPending = $this->getImportStatus() === self:: STATUS_PENDING;

        return !$this->isLockTimeoutExceeded() && $isPending;
    }

    public function getImportStatus(?int $whitelabelId = null): ?string
    {
        return $this->getImportField('status', $whitelabelId);
    }

    public function getImportField(string $name, ?int $whitelabelId = null): mixed
    {
        if ($whitelabelId !== null && isset($this->importSettings['whitelabel'][$whitelabelId][$name])) {
            return (string) $this->importSettings['whitelabel'][$whitelabelId][$name];
        }

        return isset($this->importSettings[$name]) ? (is_array($this->importSettings[$name]) ? $this->importSettings[$name] : (string) $this->importSettings[$name]) : null;
    }

    private function isLockTimeoutExceeded(): bool
    {
        $datetimeStarted = $this->getImportField('datetimeStarted');

        if (empty($datetimeStarted) || !strtotime($datetimeStarted)) {
            return false;
        }

        return strtotime($datetimeStarted) + self::LOCK_TIMEOUT <= time();
    }

    public function isTimeoutExceeded(): bool
    {
        return $this->getExecutionTimeInSeconds() >= self::getTaskTimeout();
    }

    private function getExecutionTimeInSeconds(): int
    {
        return (int) round(time() - $this->startTimeInSeconds);
    }

    public function saveImport(): void
    {
        try {
            file_put_contents($this->importFilename, json_encode($this->importSettings, JSON_PRETTY_PRINT));
        } catch (Exception $exception) {
            $this->fileLoggerService->error("Problem while saving file during import to Mautic. {$exception->getMessage()}");
        }
    }

    public function prepareWhitelabelQueueFromMauticPlugins(array $whitelabelEnabledMauticPlugins, ?array $whitelabelList): array
    {
        $whitelabelQueue = [];

        foreach ($whitelabelEnabledMauticPlugins as $mauticPlugin) {
            $whitelabelId = $mauticPlugin->whitelabel->id;

            if (isset($whitelabelQueue[$whitelabelId])) {
                continue;
            }

            $whitelabelQueue[$whitelabelId] = [
                'status' => self::STATUS_QUEUED,
                'sinceUserId' => 0,
            ];

            if ($whitelabelList && isset($whitelabelList[$whitelabelId])) {
                $whitelabelQueue[$whitelabelId] = array_merge(
                    $whitelabelQueue[$whitelabelId],
                    $whitelabelList[$whitelabelId]
                );
            }
        }

        return $whitelabelQueue;
    }
}
