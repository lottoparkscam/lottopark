<?php

trait Model_Traits_Get_Lottery
{
    
    /**
     * Get lottery that current model belongs to.
     *
     * @return Model_Lottery
     */
    public function get_lottery(): Model_Lottery
    {
        return Model_Lottery::find_by_pk($this->lottery_id); /** @phpstan-ignore-line */
    }
}
