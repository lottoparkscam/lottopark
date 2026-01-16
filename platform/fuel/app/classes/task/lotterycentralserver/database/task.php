<?php

/**
 * Abstract parent of lottery central server database tasks, which base on result of other task (most likely fetch).
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-11
 * Time: 16:28:25
 */
abstract class Task_Lotterycentralserver_Database_Task extends Task_Task
{

    /**
     *  result of Fetch task
     *
     * @var Task_Result
     */
    protected $previous_task_result;

    /**
     * @var Fuel\Core\Database_Query_Builder
     */
    protected $query;

    /**
     * @return \Fuel\Core\Database_Query_Builder
     */
    public function get_query(): \Fuel\Core\Database_Query_Builder
    {
        return $this->query;
    }

    /**
     * Create this task, in most cases should be called via static execute.
     *
     * @param Task_Result $previous_task_result result of Fetch task
     */
    public function __construct(Task_Result $previous_task_result)
    {
        parent::__construct();
        $this->previous_task_result = $previous_task_result;
    }

    public function get_previous_task_result()
    {
        return $this->previous_task_result;
    }

    /**
     * @param Task_Interface_Result $previous_task_result
     */
    public function set_previous_task_result(Task_Interface_Result $previous_task_result): void
    {
        $this->previous_task_result = $previous_task_result;
    }
}
