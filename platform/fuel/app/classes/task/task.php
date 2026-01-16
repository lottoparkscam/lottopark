<?php

use Fuel\Core\DB;
use Fuel\Core\Fuel;
use Services\Logs\FileLoggerService;

/** Archetype of task. */
abstract class Task_Task
{
    /**
     * Task result.
     *
     * @var Task_Result
     */
    protected $result;

    /**
     * True if this task should be executed in database transaction.
     * Default false.
     *
     * @var boolean
     */
    protected $in_transaction = false;

    /**
     * Mails where notification about task failure will be sent.
     * @var array
     */
    protected $failure_mails = [];

    /**
     * Message for the last error encountered by instance of the task.
     * @var string|null
     */
    private $last_error_message;
    protected FileLoggerService $fileLoggerService;

    /**
     * Create new instance of task.
     * IMPORTANT: all children who define their own constructor should call parent constructor.
     */
    protected function __construct()
    {
        $this->fileLoggerService = Container::get(FileLoggerService::class);
        $this->set_result(new Task_Result());
    }

    /**
     * Run task.
     * NOTE: called directly is not error safe.
     * @return void
     */
    abstract public function run(): void;

    /**
     * Build and execute task with error handling.
     * @param mixed $args args for object constructor.
     * @return Task_Result result of the task execution.
     */
    public static function execute(...$args): Task_Interface_Result
    {
        return static::execute_task(...$args)->get_result();
    }

    /**
     * Build and execute task with error handling.
     * @param mixed $args args for object constructor.
     * @return self executed task instance.
     */ // TODO: {Vordis 2019-11-05 13:28:13} this should be main method
    public static function execute_task(...$args): self
    {
        $task = new static(...$args);
        $success = false;
        $throwable = null;
        try {
            if ($task->in_transaction) {
                DB::start_transaction();
                $task->run();
                DB::commit_transaction();
            } else {
                $task->run();
            }
            $success = true;
        } catch (\Throwable $throwable) {
            $throwable = $throwable;
            $task->on_execution_failure($throwable);
            $task->get_result()->mark_as_failed();
        } finally {
            $fatal_error_occurred = $throwable === null && $success === false;
            if ($fatal_error_occurred) {
                $task->on_execution_failure(new \Exception('fatal error occurred in ' . static::class));
                $task->get_result()->mark_as_failed();
            }
        }

        return $task;
    }

    /**
     * Called on task failure.
     * Override if default handling is insufficient.
     *
     * @param \Throwable $throwable cause
     * @return void
     */
    protected function on_execution_failure(\Throwable $throwable, bool $shouldLogError = true): void
    {
        $child_name = static::class;
        $this->last_error_message =
            "$child_name:" .
            "\r\n'name' => TASK FAILURE" .
            "\r\n'code' => {$throwable->getCode()}," .
            "\r\n'message' => {$throwable->getMessage()}";

        if ($this->in_transaction) {
            DB::rollback_transaction();
        }

        if ($shouldLogError) {
            $this->fileLoggerService->setSource('api');
            $this->fileLoggerService->error(
                $this->last_error_message
            );
        }

        // send mail if specified by child task
        if (!empty($this->failure_mails)) {
            mail(
                implode(',', $this->failure_mails),
                'Whitelotto ' . Fuel::$env . " $child_name failure",
                $this->last_error_message
            );
        }
    }

    /**
     * Get message for the last error encountered by instance of the task.
     *
     * @return  string|null
     */
    public function get_last_error_message(): string
    {
        return $this->last_error_message;
    }

    /** @return Task_Result */
    public function &get_result(): Task_Interface_Result
    {
        return $this->result;
    }

    public function set_result(Task_Interface_Result $result): void
    {
        $this->result = &$result;
    }
}
