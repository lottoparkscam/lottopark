<?php

namespace Task\Alert;

use Container;
use Fuel\Core\Database_Result;
use Wrappers\Db;

/**
 * We exclude here tickets that were paid after closing lottery time and before download draw
 * These tickets would process successfully, but it's an issue, and ticket's draw should be moved to the next draw
 * When payment confirmation got to us after closing time there is no chance to buy it in ltech
 * So the ticket left unpurchased, but we should receive log from purchaseticket task
 * That ticket tried to buy after closing time and has not an is_ltech_insufficient_balance flag
 *
 * Purchasing tickets task needs a refactor
 * For example we also won't get information that balance on ltech is enough
 * But when ticket was bought when it wasn't, so now is enough time to buy ticket
 * But we won't receive information if this ticket was bought successfully
 * Because it has set is_ltech_insufficient_balance flag so we won't receive any log
 */
class UnpurchasedTicketsListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_UNPURCHASED_TICKETS;
    private Db $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Container::get(Db::class);
    }

    public function shouldSendAlert(): bool
    {
        $query = <<<QUERY
SELECT MIN(w.name), MIN(wut.date), MIN(wut.whitelabel_id), MIN(wut.draw_date),
MIN(l.name) AS lottery_name, MIN(l.timezone), wut.id AS ticket_id, MIN(wut.token)
FROM whitelabel_user_ticket wut
LEFT JOIN whitelabel_lottery wl
ON wl.lottery_id = wut.lottery_id
LEFT JOIN whitelabel w ON w.id = wut.whitelabel_id
LEFT JOIN lottery_provider lp ON lp.id = wl.lottery_provider_id
LEFT JOIN lottery l ON wut.lottery_id = l.id
LEFT JOIN whitelabel_user_ticket_slip wuts
ON wuts.whitelabel_user_ticket_id = wut.id
LEFT JOIN whitelabel_transaction wt
ON wut.whitelabel_transaction_id = wt.id
LEFT JOIN lottorisq_ticket lt
ON lt.whitelabel_user_ticket_slip_id = wuts.id
WHERE wl.whitelabel_id = wut.whitelabel_id
AND wut.paid = 1
AND wt.date_confirmed < CONVERT_TZ(draw_date, CONCAT(CASE WHEN TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) >= 0 THEN CONCAT('+', TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local)) ELSE TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) END, ':00'), '+00:00')
AND wut.is_ltech_insufficient_balance = 0
AND wut.date > '2023-01-01 0:00:00'
AND wut.date >= (NOW() - INTERVAL 30 DAY)
AND lp.provider <> 3
AND lt.id IS NULL
AND CONVERT_TZ(draw_date, CONCAT(CASE WHEN TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) >= 0 THEN CONCAT('+', TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local)) ELSE TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) END, ':00'), '+00:00') <= (NOW() - INTERVAL 2 HOUR)
GROUP BY wut.id
QUERY;

        /** @var Database_Result $unpurchasedTicketsCountQuery */
        $unpurchasedTicketsCountQuery = $this->db->query("SELECT count(*) AS count FROM ($query) AS unpurchased_tickets")
            ->execute();
        $unpurchasedTicketsCount = $unpurchasedTicketsCountQuery->as_array()[0]['count'];
        $thereAreSomeUnpurchasedTickets = $unpurchasedTicketsCount > 0;
        if ($thereAreSomeUnpurchasedTickets) {
            $this->setMessage(
                "There are some unpurchased tickets in Ltech.\n\r Count: $unpurchasedTicketsCount \n\r Check it with query: \n\r
                $query"
            );
            return true;
        }

        return false;
    }
}
