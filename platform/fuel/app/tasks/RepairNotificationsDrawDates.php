<?php

namespace Fuel\Tasks;
class RepairNotificationsDrawDates extends RepairTicketDrawDates
{

    protected static string $promptText = "This task will irreversibly update draw notification's lottery_draw_date. Are you sure you want to continue?";

    protected function updateTickets(int $lottery_id): bool
    {
        $newDrawTime = self::LOTTERY_DRAW_HOURS_MAP[$lottery_id];
        $drawTimeExpression = $this->database->expr("TIME(lottery_draw_date)");
        $this->database->update('user_draw_notification')
            ->set([
                'lottery_draw_date' => $this->database->expr("CONCAT(DATE(lottery_draw_date), ' " . $newDrawTime . "')"),
            ])
            ->where('lottery_id', $lottery_id)
            ->where($drawTimeExpression, "=", "00:00:00")
            ->limit(self::BATCH_SIZE)
            ->execute();

        $selectUnmodifiedTicketsResult = $this->database->select([$this->database->expr('COUNT(id)'), 'count'])
            ->from('user_draw_notification')
            ->where('lottery_id', $lottery_id)
            ->where($drawTimeExpression, "=", "00:00:00")
            ->execute();

        return !$selectUnmodifiedTicketsResult[0]['count'];
    }
}