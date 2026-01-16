<?php

namespace Task\Alert;

use Container;
use Fuel\Core\Database_Result;
use Wrappers\Db;

/**
 * It means that ticket can be processed, but is not paid out
 * It could be caused by jackpot win or some issue
 */
class UnpaidoutTicketsListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_UNPAIDOUT_TICKETS;
    private Db $db;

    public function __construct()
    {
        parent::__construct();
        $this->db = Container::get(Db::class);
    }

    public function shouldSendAlert(): bool
    {
        $query = <<<QUERY
SELECT COUNT(*) AS count 
FROM whitelabel_user_ticket wut
LEFT JOIN lottery l
ON l.id = wut.lottery_id
WHERE payout = 0
AND status = 1
AND paid = 1
AND CONVERT_TZ(draw_date, CONCAT(CASE WHEN TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) >= 0 THEN CONCAT('+', TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local)) ELSE TIMESTAMPDIFF(HOUR, l.next_date_utc, l.next_date_local) END, ':00'), '+00:00') <= (NOW() - INTERVAL 4 HOUR)
AND NOT (prize_quickpick <> 0 and l.id = 5) -- NOTE: when there is no money on ltech and for UK Lottery user win free quick pick ticket, Peter manually gives this ticket to user
AND date >= '2023-01-01' -- NOTE: ignore some old tickets
AND date >= (NOW() - INTERVAL 30 DAY)
QUERY;

        /** @var Database_Result $unpaidoutTicketsQuery */
        $unpaidoutTicketsQuery = $this->db->query($query)->execute();
        $unpaidoutTicketsCount = $unpaidoutTicketsQuery->as_array()[0]['count'];
        $thereAreSomeUnpaidoutTickets = $unpaidoutTicketsCount > 0;
        if ($thereAreSomeUnpaidoutTickets) {
            $this->setMessage(
                "There are some unpaid out tickets.\n\r Count: $thereAreSomeUnpaidoutTickets \n\r Check it with query: \n\r
                $query"
            );
            return true;
        }

        return false;
    }
}
