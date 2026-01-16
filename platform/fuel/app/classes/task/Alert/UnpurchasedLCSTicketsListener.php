<?php

namespace Task\Alert;

use Container;
use Fuel\Core\Database_Result;
use Wrappers\Db;

class UnpurchasedLCSTicketsListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_UNPURCHASED_LCS_TICKETS;
    private Db $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Container::get(Db::class);
    }

    public function shouldSendAlert(): bool
    {
        $query = <<<QUERY
SELECT w.name, wut.date, wut.whitelabel_id, wut.draw_date,
l.name AS lottery_name, l.timezone, wut.id AS ticket_id, wut.token
FROM whitelabel_user_ticket wut
LEFT JOIN whitelabel_lottery wl
ON wl.lottery_id = wut.lottery_id
LEFT JOIN whitelabel w 
ON w.id = wut.whitelabel_id
LEFT JOIN lottery_provider lp 
ON lp.id = wl.lottery_provider_id
LEFT JOIN lottery l 
ON wut.lottery_id = l.id
WHERE wl.whitelabel_id = wut.whitelabel_id
AND wut.paid = 1
AND wut.is_synchronized = 0
AND wut.date > '2023-01-01 00:00:00'
AND wut.date <= (NOW() - INTERVAL 2 HOUR)
AND wut.date >= (NOW() - INTERVAL 30 DAY)
AND draw_date <= l.next_date_local 
AND lp.provider = 3 
ORDER BY date DESC
QUERY;

        /** @var Database_Result $unpurchasedTicketsCountQuery */
        $unpurchasedTicketsCountQuery = $this->db->query("SELECT count(*) AS count FROM ($query) AS unpurchased_tickets")
            ->execute();
        $unpurchasedTicketsCount = $unpurchasedTicketsCountQuery->as_array()[0]['count'];
        $thereAreSomeUnpurchasedTickets = $unpurchasedTicketsCount > 0;
        if ($thereAreSomeUnpurchasedTickets) {
            $this->setMessage(
                "There are some unpurchased tickets in LCS.\n\r Count: $unpurchasedTicketsCount \n\r Check it with query: \n\r
                $query"
            );
            return true;
        }

        return false;
    }
}
