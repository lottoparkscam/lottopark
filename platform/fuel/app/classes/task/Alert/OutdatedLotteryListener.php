<?php

namespace Task\Alert;

use Container;
use Fuel\Core\Database_Result;
use Wrappers\Db;

/**
 * It checks if lottery has set next_draw field on too old date
 */
class OutdatedLotteryListener extends AbstractAlertListener
{
    protected string $message;
    protected string $type = self::TYPE_OUTDATED_LOTTERY;
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
SELECT COUNT(*) AS count FROM lottery
WHERE next_date_utc < (NOW() - INTERVAL CASE  WHEN lottery.shortname = '6AUS49' THEN 48 ELSE 4 END HOUR)
and is_enabled = 1;
QUERY;

        /** @var Database_Result $outdatedLotteriesQuery */
        $outdatedLotteriesQuery = $this->db->query($query)->execute();
        $outdatedLotteriesCount = $outdatedLotteriesQuery->as_array()[0]['count'];
        $thereAreSomeOutdatedLotteries = $outdatedLotteriesCount > 0;
        if ($thereAreSomeOutdatedLotteries) {
            $this->setMessage(
                "There are some outdated lotteries.\n\r Count: $thereAreSomeOutdatedLotteries \n\r Check it with query: \n\r
                $query"
            );
            return true;
        }

        return false;
    }
}
