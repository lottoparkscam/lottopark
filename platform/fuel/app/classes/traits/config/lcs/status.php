<?php

trait Config_Lcs_Status
{
    protected $lcs_enabled = true;

    protected function load_lcs_config(): void
    {
        Config::load('lottery_central_server', true);
        if(empty(Config::get('lottery_central_server.url.base')))
        {
            $this->lcs_enabled = false;
        }
    }
}
