<?php

/**
 * Abstract parent of lottery central server database tasks, which uses lottery model.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-11
 * Time: 16:28:25
 */
abstract class Task_Lotterycentralserver_Database_Lottery extends Task_Lotterycentralserver_Database_Task
{

    use Task_Lotterycentralserver_Traits_Has_Lottery;

    /**
     * Create this task, in most cases should be called via static execute.
     *
     * @param Model_Lottery $lottery              model of the lottery.
     * @param Task_Result   $previous_task_result result of Fetch task
     */
    public function __construct(Model_Lottery $lottery, Task_Result $previous_task_result)
    {
        parent::__construct($previous_task_result);
        $this->lottery = $lottery;
    }

}
