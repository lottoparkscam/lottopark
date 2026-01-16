<?php

use Services\Logs\FileLoggerService;

class Log
{
    private const LOW_LEVEL_LOG_LEVEL = 200;

    private const LEVELS = [
        100 => 'DEBUG',
        200 => 'INFO',
        250 => 'NOTICE',
        300 => 'WARNING',
        400 => 'ERROR',
        500 => 'CRITICAL',
        550 => 'ALERT',
        600 => 'EMERGENCY',
    ];

    private static function trimStackTrace($message)
    {
        $hash_index = strpos($message, '#');
        if ($hash_index === false) {
            return $message;
        }

        return substr($message, 0, $hash_index);
    }

    private static function isWordpressLog($message): bool
    {
        $message = self::trimStackTrace($message);
        return strpos($message, '/wordpress/') !== false;
    }

    public static function isPlatformWordpressLog(string $message): bool
    {
        $message = self::trimStackTrace($message);

        if (str_contains($message, '/wp-content/')) {
            return true;
        }

        if (str_contains($message, '/lotto-platform/')) {
            return true;
        }

        if (str_contains($message, '/themes/')) {
            return true;
        }

        if (str_contains($message, '/forms/wordpress/')) {
            return true;
        }

        return false;
    }

    public static function getLogType(int $level): string
    {
        $logType = self::LEVELS[$level] ?? 'unknown';

        // set notices as warnings
        if ($level === 250) {
            $logType = 'warning';
        }

        // set higher level than 400 as error in order to avoid creating a lot of channels 
        if ($level > 400) {
            $logType = 'error';
        }

        return $logType;
    }

    /** @param int|string $level fuel core pass it in mixed type */
    public static function write(int|string $level, string $message): bool
    {
        if (is_string($level)) {
            $level = array_search($level, self::LEVELS) ?? 400;
        }

        $isInfoOrDebugLog = $level <= self::LOW_LEVEL_LOG_LEVEL;
        if ($isInfoOrDebugLog) {
            return false;
        }

        $isWordpressLog = self::isWordpressLog($message);
        $isPlatformWordpressLog = $isWordpressLog && self::isPlatformWordpressLog($message);

        if ($isPlatformWordpressLog) {
            $source = 'platform wordpress';
        } else {
            $source = $isWordpressLog ? 'wordpress' : 'fuel';
        }

        $trace = (new Exception())->getTrace();
        $fileLoggerService = Container::get(FileLoggerService::class);
        if ($isWordpressLog) {
            $fileLoggerService->setSource('wordpress');
        }

        $logType = self::getLogType($level);

        $fileLoggerService->appendLogicWrapper($trace, $message, $logType, $source);

        return true;
    }
}
