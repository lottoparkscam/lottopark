<?php

/**
 * Task runner - allow to chain tasks in easy way.
 * SIMPLE, more sophisticated would allow free manipulation of previous task result
 * and maybe attaching closures as pseudo tasks.
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-07-02
 * Time: 19:06:19
 */
final class Task_Runner extends Task_Task
{
    /**
     * tasks.
     * @var array
     */
    private $tasks;

    /**
     * True if from second task onwards previous task result should be put as first parameter.
     * @var bool
     */
    private $result_as_first_parameter;

    /**
     * True if chain should stop on failure of any of the task.
     * @var bool
     */
    private $break_on_failure;

    /**
     * Create new runner
     *
     * @param array $tasks tasks to run in chain, format: class name => constructor parameters
     * @param bool $result_as_first_parameter True if from second task onwards previous task result should be put as first parameter.
     * @param bool $in_transaction True if chain should be run in transaction, true for most cases.
     * @param bool $break_on_failure True if chain should stop on failure of any of the task.
     */
    public function __construct(array $tasks, bool $result_as_first_parameter = true, bool $in_transaction = true, bool $break_on_failure = true)
    {
        parent::__construct();
        $this->in_transaction = $in_transaction;
        $this->tasks = $tasks;
        $this->result_as_first_parameter = $result_as_first_parameter;
        $this->break_on_failure = $break_on_failure;
    }

    public function run(): void
    {
        // establish how task should be executed
        $execute = $this->result_as_first_parameter ?
            function (string $class, array $parameters, array &$results, int $task_index): void {
                $results[$task_index] = $class::execute($results[$task_index - 1], ...$parameters);
            }
            : function (string $class, array $parameters, array &$results): void {
                $results[] = $class::execute(...$parameters);
            };
        $results = [];
        // first task without result as first parameter.
        /** @var object $class */
        $class = key($this->tasks);
        $parameters = current($this->tasks);
        $results[] = $class::execute(...$parameters);
        if ($this->break_on_failure && $results[0]->is_failed()) {
            $this->get_result()->mark_as_failed();
            $this->get_result()->put_data_item('failed_task_class', $class);
            return; // exit on failure of task
        }
        // rest of the tasks with possible injection of first parameter.
        $task_index = 0;
        while (($parameters = next($this->tasks)) !== false) {
            $execute($class = key($this->tasks), $parameters, $results, ++$task_index);
            if ($this->break_on_failure && $results[$task_index]->is_failed()) {
                $this->get_result()->mark_as_failed();
                $this->get_result()->put_data_item('failed_task_class', $class);
                return; // exit on failure of task
            }
        }
        $this->get_result()->set_data($results);
    }
}
