<?php

namespace Traits\Logs;

trait LogTrait
{
    public function getBaseFilePath(): string
    {
        return $this->configContract->get('log_path');
    }

    public function getPathToLogFileFolder(): string
    {
        return $this->getBaseFilePath() . $this->now->format('Y/m');
    }

    public function getLogFileName(): string
    {
        return $this->now->format('d') . '.log';
    }

    public function getPathToLogFile(): string
    {
        return $this->getPathToLogFileFolder() . '/' . $this->getLogFileName();
    }
}
