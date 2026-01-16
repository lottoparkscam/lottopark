<?php

namespace Fuel\Tasks;

use Task_Lotterycentralserver_Chain_Synchronize_Tickets;
use Task_Lotterycentralserver_Chain_Update_Draw;
use Task_Lotterycentralserver_Chain_Update_Tickets;
use Task_Trait_Cli_Evaluator;

final class Lcs
{
    use Task_Trait_Cli_Evaluator;

    public function help(): void
    {
        echo "Use this task to synchronize lottery data with Lottery Central Server\r\n" .
            "Commands in examples:\r\n" .
            "1. php oil r lcs:update_draw_data gg-world\r\n" .
            "2. php oil r lcs:synchronize_tickets gg-world\r\n" .
            "3. php oil r lcs:update_tickets_prizes gg-world\r\n";
    }

    public function update_draw_data(string $lottery_slug): void
    {
        $task = Task_Lotterycentralserver_Chain_Update_Draw::execute_task($lottery_slug);

        $this->show_result($task, "Successfully updated draw data for $lottery_slug\r\n");
    }

    public function synchronize_tickets(string $lottery_slug): void
    {
        $task = Task_Lotterycentralserver_Chain_Synchronize_Tickets::execute_task($lottery_slug);

        $this->show_result($task, "Successfully synchronized tickets for $lottery_slug\r\n");
    }

    public function update_tickets_prizes(string $lottery_slug): void
    {
        $task = Task_Lotterycentralserver_Chain_Update_Tickets::execute_task($lottery_slug);

        $this->show_result($task, "Successfully updated prizes for tickets, lottery = $lottery_slug\r\n");
    }
}
