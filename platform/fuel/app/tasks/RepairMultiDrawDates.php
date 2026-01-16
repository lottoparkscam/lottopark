<?php


namespace Fuel\Tasks;


use Carbon\Carbon;
use Fuel\Core\Cli;
use Fuel\Core\Database_Result;
use Helpers_Lottery;
use Model_Model;
use Model_Multidraw;
use Model_Whitelabel_User_Ticket;
use Task_Dev_Task;
use Wrappers\Db;

class RepairMultiDrawDates extends RepairTicketDrawDates
{

    protected static string $promptText = "This task will irreversibly update multi_draw's first_draw, valid_to_draw and current_draw. Are you sure you want to continue?";

    protected function updateTickets(int $lottery_id): bool
    {
        $newDrawTime = self::LOTTERY_DRAW_HOURS_MAP[$lottery_id];
        $drawTimeExpression = $this->database->expr("TIME(first_draw)");
        $this->database->update(Model_Multidraw::getTableName())
            ->set([
                'first_draw' => $this->database->expr("CONCAT(DATE(first_draw), ' " . $newDrawTime . "')"),
                'valid_to_draw' => $this->database->expr("CONCAT(DATE(valid_to_draw), ' " . $newDrawTime . "')"),
                'current_draw' => $this->database->expr("CONCAT(DATE(current_draw), ' " . $newDrawTime . "')"),
            ])
            ->where('lottery_id', $lottery_id)
            ->where($drawTimeExpression, "=", "00:00:00")
            ->limit(self::BATCH_SIZE)
            ->execute();

        $selectUnmodifiedTicketsResult = $this->database->select([$this->database->expr('COUNT(id)'), 'count'])
            ->from(Model_Multidraw::getTableName())
            ->where('lottery_id', $lottery_id)
            ->where($drawTimeExpression, "=", "00:00:00")
            ->execute();

        return !$selectUnmodifiedTicketsResult[0]['count'];
    }
}