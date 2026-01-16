<?php


namespace Fuel\Tasks;


use Carbon\Carbon;
use Fuel\Core\Cli;
use Fuel\Core\Database_Result;
use Helpers_Lottery;
use Model_Model;
use Model_Whitelabel_User_Ticket;
use Task_Dev_Task;
use Wrappers\Db;

class RepairTicketDrawDates extends Task_Dev_Task
{

    protected static string $promptText = "This task will irreversibly update tickets' draw dates. Are you sure you want to continue?";

    const BATCH_SIZE = 10000;
    const LOTTERY_DRAW_HOURS_MAP = [
        1 => '22:59:00',
        2 => '23:00:00',
        3 => '21:00:00',
        4 => '20:00:00',
        5 => '19:30:00',
        6 => '20:30:00',
        7 => '21:40:00',
        8 => '21:30:00',
        9 => '21:30:00',
        10 => '20:30:00',
        11 => '20:30:00',
        12 => '20:30:00',
        13 => '20:30:00',
        14 => '21:30:00',
        15 => '20:35:00',
        16 => '21:00:00',
        17 => '20:45:00',
        18 => '22:00:00',
        19 => '20:45:00',
        20 => '20:45:00',
        21 => '23:15:00',
        22 => '20:00:00',
        23 => '20:00:00',
        24 => '18:45:00',
        25 => '16:00:00',
        26 => '20:00:00',
        27 => '20:15:00',
        28 => '23:00:00',
        29 => '19:15:00',
        30 => '19:25:00',
    ];

    protected Db $database;

    protected function __construct()
    {
        parent::__construct();
        $this->database = \Container::get(Db::class);
    }

    public function run(): void
    {
        if (Cli::prompt(static::$promptText, ['y', 'n']) !== 'y') {
            exit("Aborted by the user.");
        }

        $lotteries = \Model_Lottery::find_all();
        $this->database->start_transaction();
        try {
            foreach ($lotteries as $lottery) {
                if (!isset(self::LOTTERY_DRAW_HOURS_MAP[$lottery['id']])) {
                    continue;
                }
                while (1) {
                    if ($this->updateTickets($lottery['id'])) {
                        break;
                    }
                }

            }
            $this->database->commit_transaction();
        } catch (\Exception $e) {
            $this->database->rollback_transaction();
            throw $e;
        }
    }

    /**
     * @param int $lottery_id
     *
     * @return bool returns true when there are no tickets left to update
     */
    protected function updateTickets(int $lottery_id): bool
    {
        $newDrawTime = self::LOTTERY_DRAW_HOURS_MAP[$lottery_id];
        $drawTimeExpression = $this->database->expr("TIME(draw_date)");
        $this->database->update(Model_Whitelabel_User_Ticket::getTableName())
            ->set([
                'draw_date' => $this->database->expr("CONCAT(DATE(draw_date), ' " . $newDrawTime . "')"),
                'valid_to_draw' => $this->database->expr("CONCAT(DATE(valid_to_draw), ' " . $newDrawTime . "')"),
            ])
            ->where('lottery_id', $lottery_id)
            ->where($drawTimeExpression, "=", "00:00:00")
            ->limit(self::BATCH_SIZE)
            ->execute();

        $selectUnmodifiedTicketsResult = $this->database->select([$this->database->expr('COUNT(id)'), 'count'])
            ->from(Model_Whitelabel_User_Ticket::getTableName())
            ->where('lottery_id', $lottery_id)
            ->where($drawTimeExpression, "=", "00:00:00")
            ->execute();

        return !$selectUnmodifiedTicketsResult[0]['count'];
    }
}