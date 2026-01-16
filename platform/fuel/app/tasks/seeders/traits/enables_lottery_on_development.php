<?php

trait Enables_Lottery_On_Development
{
    use \Config_Lcs_Status;

    protected function rowsDevelopment(): array
    {
        $this->load_lcs_config();
        $rows = $this->rowsStaging();
        $rows['lottery'][0]['is_enabled'] = (int) $this->lcs_enabled;

        return $rows;
    }
}
