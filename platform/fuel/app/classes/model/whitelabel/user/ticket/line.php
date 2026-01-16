<?php

use Fuel\Core\Database_Query_Builder_Update;
use Fuel\Core\Database_Result;
use Fuel\Core\DB;
use Services\Logs\FileLoggerService;

/**
 * @method static Model_Whitelabel_User_Ticket_Line[]|null find_by_whitelabel_user_ticket_id(int $whitelabel_user_ticket_id)
 */
class Model_Whitelabel_User_Ticket_Line extends Model_Model
{
    use Model_Traits_Set_Prizes;

    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_ticket_line';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     *
     * @param array $user
     *
     * @return null|array
     */
    public static function get_full_data_for_user_rodo($user): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            CONCAT(whitelabel.prefix, 'T', whitelabel_user_ticket.token) AS ticket_prefix_token,
            currency.code AS c_code,
            whitelabel_user_ticket_line.* 
        FROM whitelabel_user_ticket_line 
        INNER JOIN whitelabel_user_ticket 
            ON whitelabel_user_ticket_line.whitelabel_user_ticket_id = whitelabel_user_ticket.id
        INNER JOIN whitelabel 
            ON whitelabel_user_ticket.whitelabel_id = whitelabel.id 
        INNER JOIN whitelabel_transaction 
            ON whitelabel_user_ticket.whitelabel_transaction_id = whitelabel_transaction.id 
        LEFT JOIN currency 
            ON whitelabel_transaction.currency_id = currency.id
        WHERE 1=1 ";

        if (!empty($user) && !empty($user["id"])) {
            $query .= " AND whitelabel_user_ticket.whitelabel_user_id = :user_id ";
        }

        $query .= " ORDER BY whitelabel_user_ticket_line.id";

        try {
            $db = DB::query($query);

            if (!empty($user) && !empty($user["id"])) {
                $db->param(":user_id", $user["id"]);
            }

            /** @var object $db */
            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param int $ticket_id
     *
     * @return array
     */
    public static function get_with_slip_by_ticket_id(int $ticket_id): array
    {
        // add non global params
        $params = [];
        $params[] = [":ticket_id", $ticket_id];

        $query_string = "SELECT 
            whitelabel_user_ticket_line.*, 
            match_n, 
            match_b, 
            is_jackpot, 
            type, 
            whitelabel_user_ticket_slip.additional_data 
        FROM whitelabel_user_ticket_line 
        LEFT JOIN lottery_type_data ON lottery_type_data.id = lottery_type_data_id 
        LEFT JOIN whitelabel_user_ticket_slip ON whitelabel_user_ticket_slip.id = whitelabel_user_ticket_line.whitelabel_user_ticket_slip_id 
        WHERE whitelabel_user_ticket_line.whitelabel_user_ticket_id = :ticket_id 
        ORDER BY id";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * @param Object|null $ticket_id
     * @return null|array
     */
    public static function count_pending_by_ticket_id($ticket_id): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $res = null;
        $result = null;
        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_ticket_line 
        WHERE 1=1 ";

        if (!empty($ticket_id)) {
            $query .= " AND whitelabel_user_ticket_id = :ticket_id ";
        }

        $query .= " AND payout = " . Helpers_General::TICKET_PAYOUT_PENDING;

        try {
            $db = DB::query($query);

            if (!empty($ticket_id)) {
                $db->param(":ticket_id", $ticket_id);
            }

            /** @var object $db */
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || is_null($res[0])) {
            return $res;
        }

        $result = $res[0];

        return $result;
    }

    /**
     *
     * @param int      $token
     * @param int|null $offset
     *
     * @return array
     */
    public static function get_single_by_ticket_token(
        int $token,
        int $offset = null
    ): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            whitelabel_user_ticket_line.* 
        FROM whitelabel_user_ticket_line
        LEFT JOIN whitelabel_user_ticket ON whitelabel_user_ticket.id = whitelabel_user_ticket_line.whitelabel_user_ticket_id
        WHERE 1=1 ";

        if (!empty($token)) {
            $query .= " AND whitelabel_user_ticket.token = :token ";
        }

        $query .= " ORDER BY whitelabel_user_ticket_line.id ";
        $query .= " LIMIT 1 ";

        if (!is_null($offset)) {
            $query .= " OFFSET :offset";
        }

        try {
            $db = DB::query($query);

            if (!empty($token)) {
                $db->param(":token", $token);
            }

            if (!is_null($offset)) {
                $db->param(":offset", DB::expr(intval($offset)));
            }

            /** @var object $db */
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || empty($res[0])) {
            return $result;
        }

        $result = $res[0];

        return $result;
    }

    /**
     *
     * @param int $ticket_id
     *
     * @return mixed
     */
    public static function update_payout_value($ticket_id)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        $query = "UPDATE 
            whitelabel_user_ticket_line 
        LEFT JOIN lottery_type_data ON lottery_type_data.id = lottery_type_data_id 
        SET payout = " . Helpers_General::TICKET_PAYOUT_PAIDOUT;

        $query .= " WHERE 1=1 ";

        if (!empty($ticket_id)) {
            $query .= " AND whitelabel_user_ticket_id = :ticket_id ";
        }

        $query .= " AND lottery_type_data.type != " .
            Helpers_General::LOTTERY_TYPE_DATA_QUICK_PICK;

        try {
            $db = DB::query($query);

            if (!empty($ticket_id)) {
                $db->param(":ticket_id", $ticket_id);
            }

            $result = $db->execute();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param int $ticket_id
     *
     * @return null|array
     */
    public static function get_lines_by_ticket_id($ticket_id): ?array
    {
        // add non global params
        $params = [];
        $params[] = [":ticket_id", $ticket_id];

        $query_string = "SELECT 
            whitelabel_user_ticket_line.*, 
            match_n, 
            match_b, 
            is_jackpot, 
            type,
            whitelabel_user_ticket_slip.additional_data 
        FROM whitelabel_user_ticket_line
        LEFT JOIN lottery_type_data ON lottery_type_data.id = lottery_type_data_id
        LEFT JOIN whitelabel_user_ticket_slip ON whitelabel_user_ticket_slip.id = whitelabel_user_ticket_slip_id 
        WHERE whitelabel_user_ticket_line.whitelabel_user_ticket_id = :ticket_id 
        ORDER BY id";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     *
     * @param array $whitelabel
     *
     * @return null|array
     */
    public static function get_all_for_whitelabel_with_currencies($whitelabel): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        $query = "SELECT 
            wutl.*, 
            user_currency.id AS user_currency_id,
            user_currency.code AS user_currency_code,
            lottery_currency.id AS lottery_currency_id,
            lottery_currency.code AS lottery_currency_code,
            manager_currency.code AS manager_currency_code 
        FROM whitelabel_user_ticket_line wutl
        INNER JOIN whitelabel_user_ticket wut ON wutl.whitelabel_user_ticket_id = wut.id 
        INNER JOIN currency user_currency ON wut.currency_id = user_currency.id 
        INNER JOIN whitelabel ON wut.whitelabel_id = whitelabel.id 
        INNER JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        INNER JOIN lottery ON wut.lottery_id = lottery.id 
        INNER JOIN currency lottery_currency ON lottery.currency_id = lottery_currency.id 
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wut.whitelabel_id = :whitelabel_id ";
        }

        $query .= " ORDER BY wutl.id ";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }

            /** @var object $db */
            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param array $set
     * @param int   $line_id
     *
     * @return int|null
     */
    public static function update_manager_values_by_line_id($set, $line_id): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        // At least one manager value should be set beside of ID of line of course
        if (is_null($set) ||
            (is_null($set['amount_manager']) &&
                is_null($set['prize_manager']) &&
                is_null($set['prize_net_manager']) &&
                is_null($set['uncovered_prize_manager'])) ||
            is_null($line_id)
        ) {
            return $result;
        }

        $query = "UPDATE 
            whitelabel_user_ticket_line 
        SET ";

        $params = [];
        if (!is_null($set['amount_manager'])) {
            $params[] = "amount_manager = :amount_manager";
        }
        if (!is_null($set['prize_manager'])) {
            $params[] = "prize_manager = :prize_manager";
        }
        if (!is_null($set['prize_net_manager'])) {
            $params[] = "prize_net_manager = :prize_net_manager";
        }
        if (!is_null($set['uncovered_prize_manager'])) {
            $params[] = "uncovered_prize_manager = :uncovered_prize_manager";
        }

        $query .= implode(", ", $params);

        $query .= " WHERE id = :line_id";

        try {
            $db = DB::query($query);

            if (!is_null($set['amount_manager'])) {
                $db->param(":amount_manager", $set['amount_manager']);
            }
            if (!is_null($set['prize_manager'])) {
                $db->param(":prize_manager", $set['prize_manager']);
            }
            if (!is_null($set['prize_net_manager'])) {
                $db->param(":prize_net_manager", $set['prize_net_manager']);
            }
            if (!is_null($set['uncovered_prize_manager'])) {
                $db->param(":uncovered_prize_manager", $set['uncovered_prize_manager']);
            }

            $db->param(":line_id", intval($line_id));

            $result = $db->execute();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param array $data
     * @param int   $line_id
     *
     * @return int|null
     */
    public static function update_amount_payment_by_line_id(
        array $data,
        int $line_id = null
    ): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        // At least one manager value should be set beside of ID of line of course
        if (is_null($data) ||
            is_null($data['amount_payment']) ||
            is_null($line_id)
        ) {
            return $result;
        }

        $query = "UPDATE 
            whitelabel_user_ticket_line 
        SET ";

        $params = [];
        if (!is_null($data['amount_payment'])) {
            $params[] = "amount_payment = :amount_payment";
        }

        $query .= implode(", ", $params);

        $query .= " WHERE id = :line_id";

        try {
            $db = DB::query($query);

            if (!is_null($data['amount_payment'])) {
                $db->param(":amount_payment", $data['amount_payment']);
            }

            $db->param(":line_id", intval($line_id));

            $result = $db->execute();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     * Select lines for specified tickets.
     * NOTE: ids and columns should be clean, use only from safe scopes.
     * NOTE2: they are ordered by ticket_id ascending (from oldest ticket)
     *
     * @param array  $ticket_ids ids of the tickets.
     * @param string $columns    columns to be selected.
     *
     * @return Database_Result select result
     * @throws \Exception on database errors
     */
    public static function by_ticket_ids(array $ticket_ids, string $columns = '*'): Database_Result
    {
        $ticket_ids_string = implode(',', $ticket_ids);

        return DB::query("
            SELECT $columns FROM whitelabel_user_ticket_line
            WHERE whitelabel_user_ticket_id IN ($ticket_ids_string)
            ORDER BY whitelabel_user_ticket_id ASC;
        ")->execute();
    }

    /**
     * Set all pending lines to lost, for specified tickets.
     *
     * @param array $ticket_ids ticket ids must be clean.
     *
     * @return integer number of updated rows
     * @throws \Exception on database errors.
     */
    public static function pending_to_lost_by_tickets(array $ticket_ids): int
    {
        return DB::update(self::$_table_name)
            ->set(
                [
                    'status' => Helpers_General::TICKET_STATUS_NO_WINNINGS,
                    'prize_local' => 0,
                    'prize_usd' => 0,
                    'prize' => 0,
                    'prize_net_local' => 0,
                    'prize_net_usd' => 0,
                    'prize_net' => 0,
                    'uncovered_prize_local' => 0,
                    'uncovered_prize_usd' => 0,
                    'uncovered_prize' => 0,
                    'payout' => true,
                ]
            )
            ->where('whitelabel_user_ticket_id', 'IN', $ticket_ids)
            ->where('status', '=', Helpers_General::TICKET_STATUS_PENDING)
            ->execute();
    }

    /**
     * Build base query for set winning lines.
     *
     * @param string                 $prize_local
     * @param string                 $prize_usd
     * @param integer                $lottery_type_data_id
     * @param Model_Lottery_Provider $provider
     * @param bool                   $payout
     *
     * @return Database_Query_Builder_Update query.
     */
    public static function set_winning_base(
        string $prize_local,
        string $prize_usd,
        int $lottery_type_data_id,
        Model_Lottery_Provider $provider,
        bool $payout
    ): Database_Query_Builder_Update
    {
        return self::set_winning_base_($prize_local, $prize_usd, $provider, $payout, [
            'lottery_type_data_id' => $lottery_type_data_id,
            'uncovered_prize_local' => 0,
            'uncovered_prize_usd' => 0,
        ]);
    }

    /**
     * Fill manager fields in query.
     *
     * @param Database_Query_Builder_Update $query
     * @param string                        $prize_manager
     * @param Model_Lottery_Provider        $provider
     *
     * @return void.
     */
    public static function set_winning_manager(
        Database_Query_Builder_Update &$query,
        string $prize_manager,
        Model_Lottery_Provider $provider
    ): void
    {
        self::set_winning_manager_($query, $prize_manager, $provider, [
            'uncovered_prize_manager' => 0
        ]);
    }

    /**
     * Finish set winning query and execute.
     *
     * @param Database_Query_Builder_Update $query
     * @param string                        $prize_user
     * @param array                         $line_ids
     * @param Model_Lottery_Provider        $provider
     *
     * @return string sql from query.
     */
    public static function set_winning_compile(
        Database_Query_Builder_Update $query,
        string $prize_user,
        array $line_ids,
        Model_Lottery_Provider $provider
    ): string
    {
        return self::set_winning_finish_($query, $prize_user, $line_ids, $provider, [
            'uncovered_prize' => 0
        ])
            ->compile();
    }

    /**
     *
     * @param array $ids
     *
     * @return array
     */
    public static function get_all_for_crm(array $ids): array
    {
        $lines = [];
        $query = DB::select('id', 'numbers', 'bnumbers', 'prize', 'prize_usd', 'prize_manager', 'whitelabel_user_ticket_id')
            ->from('whitelabel_user_ticket_line')
            ->where('whitelabel_user_ticket_id', 'in', $ids);

        /** @var object $query */
        $result = $query->execute()->as_array();

        if (count($result) > 0) {
            foreach ($result as $data) {
                if (!array_key_exists($data['whitelabel_user_ticket_id'], $lines)) {
                    $lines[$data['whitelabel_user_ticket_id']] = [];
                }
                $lines[$data['whitelabel_user_ticket_id']][] = $data;
            }
        }

        return $lines;
    }
}
