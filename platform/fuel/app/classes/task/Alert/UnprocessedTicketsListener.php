<?php

namespace Task\Alert;

use Container;
use Fuel\Core\Database_Result;
use Wrappers\Db;

/**
 * It means that ticket is not set as paid or won, it is still pending
 * It could be caused by missed draw or other issue
 */
class UnprocessedTicketsListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_UNPROCESSED_TICKETS;
    private Db $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Container::get(Db::class);
    }

    public function shouldSendAlert(): bool
    {
        // Lottery 6ASU49 can download draws even well beyond the draw date
        $query = <<<QUERY
SELECT l.name AS lottery_name, COUNT(DISTINCT wut.id) AS ticket_count
FROM whitelabel_user_ticket wut
LEFT JOIN lottery l
ON l.id = wut.lottery_id
WHERE paid = 1
AND status = 0
AND CONVERT_TZ(
        draw_date, 
        CONCAT(
            CASE 
                WHEN TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) >= 0 
                THEN CONCAT('+', TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local)) 
                ELSE TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) 
            END, 
        ':00'), 
    '+00:00') <= (NOW() - INTERVAL CASE  WHEN l.shortname = '6AUS49' THEN 48 ELSE 4 END HOUR) 
AND date >= "2024-01-01"
AND date >= (NOW() - INTERVAL 30 DAY)
GROUP BY l.name
QUERY;

        /** @var Database_Result $unprocessedTicketsCountQuery */
        $unprocessedTicketsCountQuery = $this->db->query($query)->execute();
        $unprocessedLotteryTickets = $unprocessedTicketsCountQuery->as_array();
        $areUnprocessedTickets = count($unprocessedLotteryTickets) > 0;
        if ($areUnprocessedTickets) {
            $unprocessedTicketsString = '';
            foreach ($unprocessedLotteryTickets as $datum) {
                $unprocessedTicketsString .= $datum['lottery_name'] . ": " . $datum['ticket_count'] . "\n\r";
            }
            $this->setMessage(
                "Unprocessed tickets:\n\r $unprocessedTicketsString"
            );
            return true;
        }

        return false;
    }
}
