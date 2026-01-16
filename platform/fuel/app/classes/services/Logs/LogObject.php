<?php

namespace Services\Logs;

use stdClass;

final class LogObject
{
    public string $type;
    public string $source;
    public string $message;
    public string $date;
    public string $url;
    public string $file;
    public string $trace;
    public int $count;

    public function __construct(?stdClass $decodedJson = null)
    {
        if (!is_null($decodedJson)) {
            $this->set($decodedJson);
        }
    }

    public function set(stdClass $log): void
    {
        $this->type = $log->type;
        $this->source = $log->source;
        $this->message = $log->message;
        $this->date = $log->date;
        $this->url = $log->url;
        $this->file = $log->file;
        $this->trace = $log->trace;
        $this->count = $log->count ?? 1;
    }

    public function getLogColor(): string
    {
        return '';
    }

    /**
     * This function prepares proper formatting for slack log 
     * @see https://slack.com/help/articles/202288908-Format-your-messages 
     */
    public function getLogDetails(int $logCount = 1): string
    {
        $shouldAddLogCount = $logCount !== 1;
        $logCountInfo = $shouldAddLogCount ? '*Appeared:* ' . $logCount : '';

        return <<<LOG_DETAILS
        *Type:* {$this->type} $logCountInfo \n
        *Source:* {$this->source} \n
        *Message:* {$this->message} \n
        *Date:* {$this->date} \n
        *Url:* {$this->url} \n
        *File:* {$this->file} \n
        LOG_DETAILS;
    }

    public function __toString(): string
    {
        return json_encode($this) . "\n";
    }
}
