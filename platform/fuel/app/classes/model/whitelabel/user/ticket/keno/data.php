<?php

use Fuel\Core\Database_Query;
use Fuel\Core\Database_Result;
use Fuel\Core\DB;
use Services\Logs\FileLoggerService;

class Model_Whitelabel_User_Ticket_Keno_Data extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_ticket_keno_data';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    public static function by_ticket_id($ticket_id): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        $query = DB::select_array([
            'w.id',
            'w.numbers_per_line',
            'm.multiplier'
        ])
            ->from(['whitelabel_user_ticket_keno_data', 'w'])
            ->join(['lottery_type_multiplier', 'm'], 'left')
            ->on('m.id', '=', 'w.lottery_type_multiplier_id')
            ->where('w.whitelabel_user_ticket_id', '=', $ticket_id);

        try {
            /** @var object $query */
            $result = $query->execute()->as_array();
        } catch (Throwable $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result[0] ?? null;
    }

    /**
     * @throws Exception if unable to fetch results.
     */
    public static function byTicketIds(array $ticketIds): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        /** @var Database_Query $query */
        $query = DB::select_array([
            'whitelabel_user_ticket_id',
            'numbers_per_line',
            'multiplier',
        ])
            ->from(['whitelabel_user_ticket_keno_data', 'data'])
            ->join(['lottery_type_multiplier', 'multiplier'], 'left')
            ->on('multiplier.id', '=', 'data.lottery_type_multiplier_id')
            ->where('data.whitelabel_user_ticket_id', 'in', $ticketIds);

        try {
            /** @var Database_Result $databaseResult */
            $databaseResult = $query->execute();
            $result = $databaseResult->as_array();
        } catch (Throwable $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result ?? null;
    }
}
