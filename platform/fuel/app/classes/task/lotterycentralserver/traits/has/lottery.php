<?php


trait Task_Lotterycentralserver_Traits_Has_Lottery
{
    /**
     * Model of the lottery.
     *
     * @var Model_Lottery
     */
    protected $lottery;

    /**
     * @return Model_Lottery
     */
    public function get_lottery(): Model_Lottery
    {
        return $this->lottery;
    }
}