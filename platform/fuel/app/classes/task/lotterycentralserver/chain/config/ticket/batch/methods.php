<?php

use Task_Lotterycentralserver_Chain_Config_Ticket_Batch as Batch_Config;

trait Task_Lotterycentralserver_Chain_Config_Ticket_Batch_Methods
{   
    private function get_ticket_count(int $lottery_id): int
    {
        return Batch_Config::PER_LOTTERY[$lottery_id]['tickets_count'] ?? Batch_Config::TICKET_COUNT_DEFAULT;
    }

    private function get_batch_size(int $lottery_id): int
    {
        return Batch_Config::PER_LOTTERY[$lottery_id]['batch_size'] ?? Batch_Config::BATCH_SIZE_DEFAULT;
    }
    
    private function get_lcs_ticket_retries(int $lottery_id): int
    {
        return Batch_Config::PER_LOTTERY[$lottery_id]['lcs_ticket_retries'] ?? Batch_Config::LCS_TICKET_RETRIES_DEFAULT;
    }

    private function get_max_iterations(int $lottery_id): int
    {
        return Batch_Config::PER_LOTTERY[$lottery_id]['max_iteration'] ?? Batch_Config::MAX_ITERATION_DEFAULT;
    }
}
