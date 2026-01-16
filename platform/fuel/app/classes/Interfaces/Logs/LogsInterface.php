<?php

namespace Interfaces\Logs;

interface LogsInterface
{
    public const SLACK_CHANNEL_PREFIX = 'logs-whitelotto-';

    public const SOURCE_DEFAULT = 'default';
    public const SOURCE_PLATFORM = 'platform';

    public const TYPE_ERROR = 'ERROR';
    public const TYPE_INFO = 'INFO';
    public const TYPE_WARNING = 'WARNING';
    public const TYPE_ASSISTANT = 'LOG_ASSISTANT';
    public const TYPE_UNKNOWN = 'UNKNOWN';

    public const ASSISTANT_CHANNEL = 'assistant';

    public const LOG_STYLES = [
        self::TYPE_ERROR => [
            'icon' => ':fire:',
            'color' => '#dc3545'
        ],
        self::TYPE_WARNING => [
            'icon' => ':warning:',
            'color' => '#ffc107'
        ],
        self::TYPE_INFO => [
            'icon' => ':information_source:',
            'color' => '#17a2b8'
        ],
        self::TYPE_ASSISTANT => [
            'icon' => ':construction_worker:',
            'color' => '#593d73'
        ],
        self::TYPE_UNKNOWN => [
            'icon' => ':dove_of_peace:',
            'color' => '#fff'
        ],

    ];

    public function error(string $message): bool;
    public function info(string $message): bool;
    public function assistant(string $message): bool;
    public function warning(string $message): bool;
}
