<?php

use GO\Job;
use GO\Scheduler;
use GO\Scheduler as PureScheduler;
use Services\Logs\FileLoggerService;

class Services_Scheduler
{
    /** @var Scheduler $scheduler */
    private $scheduler;
    private FileLoggerService $fileLoggerService;

    public function __construct()
    {
        $this->scheduler = new PureScheduler([
            'tempDir' => $_ENV['SCHEDULER_TEMPORARY_LOCK_FILE_PATH']
        ]);
        $this->fileLoggerService = Container::get(FileLoggerService::class);
    }

    // log not used, I haven't removed it for debug cases
    public function add_command(array $command, bool $log = true): Job
    {
        // Without this scheduler creates lock files only from command name e.g. wget instead of full command with args
        // Causing jobs not to run at all
        $id = md5(serialize($command));
        $mainCommand = $command[0];
        $args = $command[1];
        return $this->scheduler->raw($mainCommand, $args, $id);
    }

    public function add_task(string $task, array $args = [], bool $log = true): Job
    {
        return $this->add_command([
            "php8.0 {$_ENV['SCHEDULER_OIL_PATH']} r {$task}",
            $args
        ], $log);
    }

    public function add_wordpress_task(string $domain, string $task, array $args = [], bool $log = true): Job
    {
        return $this->add_command([
            "WORDPRESS_DOMAIN_IN_CLI=$domain php8.0 {$_ENV['SCHEDULER_OIL_PATH']} r {$task}",
            $args
        ], $log);
    }

    public function log(string $message): void
    {
        $log_is_enabled = $_ENV['SCHEDULER_FAILED_LOG_IS_ENABLED'] === 'true';

        if ($log_is_enabled) {
            $this->fileLoggerService->error(
                "Error during execution scheduled task. \n MESSAGE: ${message}"
            );
        }
    }

    public function check_failed_jobs(): void
    {
        foreach ($this->scheduler->getFailedJobs() as $failed_job) {
            $this->log($failed_job->getException());
        }
    }

    public function add_wget(string $url, bool $log = true): Job
    {
        return $this->add_command([
            '/usr/bin/wget',
            [
                '-4' => null,
                '-q' => null,
                '--spider' => null,
                $url => null
            ]
        ], $log);
    }

    public function add_wget_main(bool $log = true): Job
    {
        return $this->add_command([
            '/usr/bin/wget',
            [
                '-4' => null,
                '-q' => null,
                '--spider' => null,
                $_ENV['SCHEDULER_MAIN_URL'] => null
            ]
        ], $log);
    }

    public function add_wget_empire(string $task, bool $log = true): Job
    {
        return $this->add_wget($_ENV['SCHEDULER_EMPIRE_URL'] . $task, $log);
    }

    public function add_wget_empire_task(string $task, bool $log = true): Job
    {
        return $this->add_wget($_ENV['SCHEDULER_EMPIRE_URL'] . 'task/' . $task, $log);
    }

    public static function everyXHours(int $hours, int $minutes = 0): string
    {
        return "$minutes */{$hours} * * *";
    }

    public static function everyXDays(int $days): string
    {
        return "0 0 */{$days} * *";
    }

    public function run(): void
    {
        $isNotUnderDeploy = !file_exists(APPPATH . ".maintenance");

        if ($isNotUnderDeploy) {
            $this->scheduler->run();
        }
    }
}
