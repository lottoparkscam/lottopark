<?php

/**
 * Archetype of fetch tasks.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 2019-06-11
 * Time: 11:06:56
 */
abstract class Task_Lotterycentralserver_Fetch_Task extends Task_Lotterycentralserver_Task
{

    use Task_Lotterycentralserver_Traits_Has_Lottery;

    /**
     * @param Model_Lottery $lottery model of the lottery.
     */
    public function set_lottery(Model_Lottery $lottery)
    {
        $this->lottery = $lottery;
    }

    abstract public function fetch(): Response_Interface;
}
