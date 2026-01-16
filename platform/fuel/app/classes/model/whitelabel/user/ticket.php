<?php

use Carbon\Carbon;
use Fuel\Core\Database_Query_Builder_Select;
use Fuel\Core\Database_Query_Builder_Update;
use Fuel\Core\DB;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;

/** field 'ip' - ip which user had during buying the ticket */
class Model_Whitelabel_User_Ticket extends Model_Model
{
    use Model_Traits_Set_Prizes;

    // TODO: {Vordis 2019-11-20 13:14:53} tentative consts as links, but they should be only here
    /**
     * @var int
     */
    const MODEL_PURCHASE = Helpers_General::LOTTERY_MODEL_PURCHASE;
    /**
     * @var int
     */
    const MODEL_MIXED = Helpers_General::LOTTERY_MODEL_MIXED;
    /**
     * @var int
     */
    const MODEL_PURCHASE_SCAN = Helpers_General::LOTTERY_MODEL_PURCHASE_SCAN;
    /**
     * @var int
     */
    const MODEL_NONE = Helpers_General::LOTTERY_MODEL_NONE;

    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_ticket';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     *
     * @param int   $paid
     * @param array $whitelabel
     *
     * @return null|int
     */
    public static function count_for_whitelabel_paid($paid, $whitelabel = [], $multi_draws = false): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_user_ticket 
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id";
        }

        if ($multi_draws) {
            $query .= " AND multi_draw_id IS NOT NULL";
        }

        $query .= " AND whitelabel_user_ticket.paid = " . $paid;

        if ($multi_draws) {
            $query .= " GROUP BY multi_draw_id";
        }

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            /** @var object $db */
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || is_null($res[0])) {
            return $result;
        }

        $result = $res[0]['count'];

        return $result;
    }

    /**
     *
     * @param int   $paid
     * @param array $whitelabel
     *
     * @return null|int
     */
    public static function count_for_whitelabel_multidraw($paid, $whitelabel = []): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                COUNT(*) AS count 
            FROM multi_draw
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND multi_draw.whitelabel_id = :whitelabel_id";
        }

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            /** @var object $db */
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || is_null($res[0])) {
            return $result;
        }

        $result = $res[0]['count'];

        return $result;
    }

    /**
     * This is for export full date purpose
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
                (CASE WHEN whitelabel_transaction.type = 0 
                    THEN CONCAT(whitelabel.prefix, 'P', whitelabel_transaction.token)
                    ELSE CONCAT(whitelabel.prefix, 'D', whitelabel_transaction.token)
                END) AS trans_prefix_token,
                lottery.name AS lottery_name,
                lottery.next_date_local AS next_date_local,
                currency.code AS c_code,
                whitelabel_user_ticket.* 
            FROM whitelabel_user_ticket
            INNER JOIN whitelabel 
                ON whitelabel_user_ticket.whitelabel_id = whitelabel.id 
            INNER JOIN whitelabel_transaction 
                ON whitelabel_user_ticket.whitelabel_transaction_id = whitelabel_transaction.id 
            INNER JOIN lottery 
                ON whitelabel_user_ticket.lottery_id = lottery.id 
            LEFT JOIN currency 
                ON whitelabel_transaction.currency_id = currency.id 
            WHERE 1=1 ";

        if (!empty($user) && !empty($user["id"])) {
            $query .= " AND whitelabel_user_ticket.whitelabel_user_id = :user_id";
        }

        $query .= " ORDER BY whitelabel_user_ticket.id ";

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
     * @param int|null $transaction_id
     *
     * @return null|array
     */
    public static function get_full_data_with_counted_lines(int $transaction_id = null, bool $orderByKenoFirst = false): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            whitelabel_user_ticket.*, 
            whitelabel_user_ticket_keno_data.numbers_per_line,
            whitelabel_transaction.payment_currency_id, 
            whitelabel_transaction.amount as transaction_amount, 
            GROUP_CONCAT(draw_date) multiple_draw_dates, 
            COUNT(whitelabel_user_ticket_line.id) AS count,
            COALESCE(whitelabel_user_ticket_line.amount_payment, 0) AS line_payment_amount, 
            COALESCE(whitelabel_user_ticket_line.amount, 0) AS line_price, 
            whitelabel_user_ticket_line.bonus_amount AS bonus_line_price, 
            multi_draw.tickets as multi_draw_tickets, 
            multi_draw.id as multi_draw_id, 
            multi_draw.amount as multi_draw_amount, 
            multi_draw.bonus_amount as multi_draw_bonus_amount, 
            multi_draw.old_ticket_price as multi_draw_old_ticket_price, 
            multi_draw.discount as multi_draw_discount,
            lottery_type_multiplier.multiplier as ticket_multiplier,
            lottery.type as lottery_type
        FROM whitelabel_user_ticket
        LEFT JOIN multi_draw ON multi_draw.id = whitelabel_user_ticket.multi_draw_id 
        LEFT JOIN whitelabel_user_ticket_keno_data ON whitelabel_user_ticket_keno_data.whitelabel_user_ticket_id = whitelabel_user_ticket.id
        LEFT JOIN lottery_type_multiplier ON whitelabel_user_ticket_keno_data.lottery_type_multiplier_id = lottery_type_multiplier.id
        INNER JOIN whitelabel_user_ticket_line ON whitelabel_user_ticket_line.whitelabel_user_ticket_id = whitelabel_user_ticket.id 
        INNER JOIN whitelabel_transaction ON whitelabel_transaction.id = whitelabel_user_ticket.whitelabel_transaction_id 
        LEFT JOIN lottery ON lottery.id = whitelabel_user_ticket.lottery_id
        WHERE 1=1 ";

        if (!empty($transaction_id)) {
            $query .= "AND whitelabel_user_ticket.whitelabel_transaction_id = :transaction_id ";
        }

        $query .= "GROUP BY
            whitelabel_user_ticket_line.whitelabel_user_ticket_id,
            whitelabel_user_ticket_line.amount,
            whitelabel_user_ticket_line.bonus_amount,
            whitelabel_user_ticket_line.amount_payment,
            whitelabel_user_ticket_keno_data.numbers_per_line,
            whitelabel_user_ticket_keno_data.lottery_type_multiplier_id";

        if ($orderByKenoFirst) {
            $query .= " ORDER BY FIELD(lottery.type, 'keno') DESC";
        } else {
            $query .= " ORDER BY whitelabel_user_ticket.id";
        }

        try {
            $db = DB::query($query);

            if (!empty($transaction_id)) {
                $db->param(":transaction_id", $transaction_id);
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
     * @param int|null $transaction_id
     *
     * @return null|array
     */
    public static function get_tickets_for_email_by_transaction_id(?int $transaction_id): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            whitelabel_user_ticket.*, 
            multi_draw.tickets as multi_draw_tickets, 
            multi_draw.id as multi_draw_id, 
            multi_draw.amount as multi_draw_amount, 
            multi_draw.old_ticket_price as multi_draw_old_ticket_price, 
            multi_draw.discount as multi_draw_discount 
        FROM whitelabel_user_ticket
        LEFT JOIN multi_draw ON multi_draw.id = whitelabel_user_ticket.multi_draw_id 
        WHERE 1=1 ";

        $query .= "AND whitelabel_user_ticket.whitelabel_transaction_id = :transaction_id ";


        $query .= "ORDER BY whitelabel_user_ticket.id";

        try {
            $db = DB::query($query);

            $db->param(":transaction_id", $transaction_id);

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
     * @param Model_Whitelabel_Transaction|null $transaction
     *
     * @return null|int
     */
    public static function get_counted_by_transaction($transaction): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $res = null;
        $result = null;

        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_ticket 
        WHERE 1=1 ";

        if (!empty($transaction) && !empty($transaction->id)) {
            $query .= " AND whitelabel_transaction_id = :transaction_id ";
        }

        $query .= " AND date_processed IS NOT NULL";

        try {
            $db = DB::query($query);

            if (!empty($transaction) && !empty($transaction->id)) {
                $db->param(":transaction_id", $transaction->id);
            }

            /** @var object $db */
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || is_null($res[0])) {
            return $result;
        }

        $result = $res[0]['count'];

        return $result;
    }

    /**
     *
     * @param array  $whitelabel
     * @param array  $user
     * @param array  $params
     * @param string $filter_add
     *
     * @return null|int
     */
    public static function get_counted_by_user_and_whitelabel_filtered(
        $whitelabel,
        $user,
        $params = [],
        $filter_add = "",
        $type = false
    ): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $res = null;
        $result = null;

        $subquery_lottery_ticket = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_ticket
        WHERE 1=1 ";

        $subquery_raffle_ticket = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_raffle_ticket
        WHERE 1=1";

        $additional_where = "";
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $additional_where .= " AND whitelabel_id = :whitelabel_id ";
        }

        if (!empty($user) && !empty($user["id"])) {
            $additional_where .= " AND whitelabel_user_id = :user_id";
        }

        if ($type == "awaiting") {
            $additional_where .= " AND status = ".Helpers_General::TICKET_STATUS_PENDING;
        } elseif ($type == "past") {
            $additional_where .= " AND status != ".Helpers_General::TICKET_STATUS_PENDING;
        }

        $subquery_lottery_ticket .= $additional_where . ' AND paid = ' . Helpers_General::TICKET_PAID;
        $subquery_raffle_ticket .= $additional_where;

        $query = 'SELECT (' . $subquery_lottery_ticket . ') + (' . $subquery_raffle_ticket . ') AS count FROM dual';

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }

            if (!empty($user) && !empty($user["id"])) {
                $db->param(":user_id", $user["id"]);
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            /** @var object $db */
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || is_null($res[0])) {
            return $result;
        }

        $result = $res[0]['count'];

        return $result;
    }

    /**
     *
     * @param array  $whitelabel
     * @param array  $user
     * @param array  $sort
     * @param int    $offset
     * @param int    $limit
     * @param array  $params
     * @param string $filter_add
     *
     * @return array|null
     */
    public static function get_data_by_user_and_whitelabel_filtered(
        $whitelabel,
        $user,
        $sort = [],
        $offset = 0,
        $limit = 10,
        $params = [],
        $filter_add = "",
        $type = false
    ): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;

        $isSort = !empty($sort) && !empty($sort['db']);
        $forceIndex = $isSort ? 'FORCE INDEX (whitelabel_user_ticket_w_id_wu_id_paid_prize_local_idmx) ' : '';
        $subquery_user_ticket = "SELECT 1 AS ticket_type, wut.id, whitelabel_id, whitelabel_user_id, lottery_id, wut.currency_id, token, name, amount as ticket_amount, paid, status, draw_date, date, prize
            FROM whitelabel_user_ticket wut $forceIndex
            JOIN lottery l ON wut.lottery_id = l.id WHERE 1=1";
        $subquery_raffle_ticket = "SELECT 2 AS ticket_type, wrt.id, whitelabel_id, whitelabel_user_id, raffle_id, wrt.currency_id, token, r.name, amount as ticket_amount, status, " . Helpers_General::TICKET_STATUS_PENDING . " , NULL, created_at AS date, prize
            FROM whitelabel_raffle_ticket wrt
            JOIN raffle r ON wrt.raffle_id WHERE 1=1";

        $additional_params = "";
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $additional_params .= " AND whitelabel_id = :whitelabel_id ";
        }

        if (!empty($user) && !empty($user["id"])) {
            $additional_params .= " AND whitelabel_user_id = :user_id";
        }
        $additional_params .= " " . $filter_add;

        if ($type == "awaiting") {
            $additional_params .= " AND status = ".Helpers_General::TICKET_STATUS_PENDING;
        } elseif ($type == "past") {
            $additional_params .= " AND status != ".Helpers_General::TICKET_STATUS_PENDING;
        }

        $subquery_user_ticket .= $additional_params . " AND paid = " . Helpers_General::TICKET_PAID;
        $subquery_raffle_ticket .= $additional_params;

        if ($isSort) {
            $subquery_user_ticket .= " ORDER BY :order ";
            $subquery_raffle_ticket .= " ORDER BY :order ";
        }

        $query = "SELECT * FROM (
            (". $subquery_user_ticket ." LIMIT :limitPerSingleQuery)
            UNION ALL
            (". $subquery_raffle_ticket ." LIMIT :limitPerSingleQuery))
            AS tickets WHERE 1=1";

        $query .= " AND paid = " . Helpers_General::TICKET_PAID;
        $query .= " " . $filter_add;

        if ($isSort) {
            $query .= " ORDER BY :order ";
        }

        $query .= " LIMIT :offset, :limit";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }

            if (!empty($user) && !empty($user["id"])) {
                $db->param(":user_id", $user["id"]);
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            if ($isSort) {
                $db->param(":order", DB::expr($sort['db']));
            }

            $db->param(":offset", $offset);
            $db->param(":limit", $limit);

            /**
             * This query combines two queries: regular tickets and raffle tickets
             * Get all these queries together by UNION ALL then sort it and limit
             * Because both query for regular tickets and raffles are not limited
             * Union takes all rows and then limit it at the and
             * Because we want to combine this queries, sort by dates and then limit per page
             * When we first order each query, max needed rows is (offset + 1) * limit
             * E.g. 3rd page, 25 per page
             * When our queries are sorted we can limit it to 3 * 25 = 75
             * So when there are no raffle tickets, we have enough regular tickets to show
             * When its combined we have a little more than we need but still query is much faster
             */
            $limitPerSingleQuery = ((int)$offset + 1) * (int)$limit;
            $db->param(':limitPerSingleQuery', $limitPerSingleQuery);

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
     * @param array  $whitelabel
     * @param array  $params
     * @param string $filter_add
     *
     * @return null|int
     */
    public static function get_counted_by_whitelabel_filtered(
        $whitelabel,
        $params = [],
        $filter_add = ""
    ): ?int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $res = null;
        $result = null;

        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_ticket ";

        $areFilters = !empty($filter_add);
        if ($areFilters) {
            $query .= "LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id
        LEFT JOIN whitelabel_transaction ON whitelabel_transaction.id = whitelabel_user_ticket.whitelabel_transaction_id 
        LEFT JOIN multi_draw ON multi_draw.id = whitelabel_user_ticket.multi_draw_id ";
        }

        $query .= "WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND paid = " . Helpers_General::TICKET_PAID;
        $query .= " " . $filter_add;

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            /** @var object $db */
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || is_null($res[0])) {
            return $result;
        }

        $result = $res[0]['count'];

        return $result;
    }

    /**
     *
     * @param array  $whitelabel
     * @param Object $pagination
     * @param array  $sort
     * @param array  $params
     * @param string $filter_add
     *
     * @return null|array
     */
    public static function get_full_data_paid_for_whitelabel(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $filter_add = ""
    ): ?array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            whitelabel_user_ticket.*, 
            whitelabel_user.id AS uid, 
            whitelabel_user.token AS utoken, 
            whitelabel_transaction.token AS ptoken, 
            whitelabel_user.is_deleted, 
            whitelabel_user.is_active,
            whitelabel_user.is_confirmed, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user.email, 
            whitelabel_user.balance,
            whitelabel_user.login AS user_login,
            whitelabel_user_ticket.line_count AS count,
            user_currency.code AS user_currency_code,
            lottery_currency.code AS lottery_currency_code,
            manager_currency.code AS manager_currency_code, 
            multi_draw.token AS mdtoken 
        FROM whitelabel_user_ticket
        LEFT JOIN currency user_currency ON whitelabel_user_ticket.currency_id = user_currency.id 
        LEFT JOIN whitelabel ON whitelabel_user_ticket.whitelabel_id = whitelabel.id 
        LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        LEFT JOIN lottery ON whitelabel_user_ticket.lottery_id = lottery.id 
        LEFT JOIN currency lottery_currency ON lottery.currency_id = lottery_currency.id 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id
        LEFT JOIN whitelabel_transaction ON whitelabel_transaction.id = whitelabel_user_ticket.whitelabel_transaction_id
        LEFT JOIN multi_draw ON multi_draw.id = whitelabel_user_ticket.multi_draw_id
        WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND paid = " . Helpers_General::TICKET_PAID;
        $query .= " " . $filter_add;

        if (!empty($sort) && !empty($sort["db"])) {
            $query .= " ORDER BY :order ";
        }

        $query .= " LIMIT :offset, :limit";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            $db->param(":offset", $pagination->offset);
            $db->param(":limit", $pagination->per_page);

            if (!empty($sort) && !empty($sort["db"])) {
                $db->param(":order", DB::expr($sort["db"]));
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
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
     * Get single row of ticket based on whitelabel and token with currency data
     * for user and manager
     *
     * @param array  $whitelabel
     * @param int $token
     *
     * @return array|null
     */
    public static function get_single_with_currencies(
        array $whitelabel,
        int $token
    ): ?array
    {
        // add non global params
        $params = [];
        $params[] = [":whitelabel_id", $whitelabel["id"]];
        $params[] = [":token", $token];

        $query_string = "SELECT 
            whitelabel_user_ticket.*, 
            multi_draw.tickets as multi_draw_tickets, 
            user_currency.code AS user_currency_code,
            lottery_currency.code AS lottery_currency_code, 
            multi_draw.tickets as multi_draw_tickets, 
            manager_currency.code AS manager_currency_code 
        FROM whitelabel_user_ticket 
        INNER JOIN currency user_currency ON whitelabel_user_ticket.currency_id = user_currency.id 
        INNER JOIN whitelabel ON whitelabel_user_ticket.whitelabel_id = whitelabel.id 
        INNER JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        INNER JOIN lottery ON whitelabel_user_ticket.lottery_id = lottery.id 
        INNER JOIN currency lottery_currency ON lottery.currency_id = lottery_currency.id 
        LEFT JOIN multi_draw ON multi_draw.id = whitelabel_user_ticket.multi_draw_id 
        WHERE whitelabel_user_ticket.whitelabel_id = :whitelabel_id 
            AND whitelabel_user_ticket.token = :token 
        LIMIT 1";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch count of paid tickets for whitelabel filtered.
     *
     * @param string $add           filter adds.
     * @param array  $params        filter params.
     * @param int    $whitelabel_id whitelabel id
     * @param bool   $is_bonus      determine if data should be for bonus or not
     *
     * @return int count of the paid tickets for specified whitelabel.
     */
    public static function get_data_count_for_reports(
        string $add,
        array $params,
        int $whitelabel_id,
        bool $is_bonus = false
    ): int
    {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_ticket 
        JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id 
        WHERE whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";

        if ($is_bonus) {
            $query_string .= " AND whitelabel_user_ticket.whitelabel_transaction_id IS NULL ";
        } else {
            $query_string .= " AND whitelabel_user_ticket.paid = " .
                Helpers_General::TICKET_PAID . " 
            AND whitelabel_user_ticket.whitelabel_transaction_id IS NOT NULL ";
        }

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    /**
     * Fetch count of paid ticket lines for whitelabel filtered.
     *
     * @param string $add           filter adds.
     * @param array  $params        filter params.
     * @param int    $whitelabel_id whitelabel id
     *
     * @return int count of the paid ticket lines for specified whitelabel.
     */
    public static function get_line_sum_for_reports(
        string $add,
        array $params,
        int $whitelabel_id
    ): int
    {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $query_string = "SELECT 
            SUM(line_count) AS line_sum 
        FROM whitelabel_user_ticket 
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id 
        WHERE whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";

        $query_string .= " AND whitelabel_user_ticket.paid = " .
            Helpers_General::TICKET_PAID . " 
        AND whitelabel_user_ticket.whitelabel_transaction_id IS NOT NULL ";

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'line_sum');
    }

    /**
     * Fetch count of user tickets filtered.
     *
     * @param string $add             filter adds.
     * @param array  $params          filter params.
     * @param int    $whitelabel_type whitelabel type could be null
     * @param int    $whitelabel_id   whitelabel id could be null
     * @param bool   $is_full_report  For full report noting changed
     * @param bool   $is_bonus        determine if data should be for bonus or not
     *
     * @return int count of paid tickets
     */
    public static function get_counted_data_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false,
        bool $is_bonus = false
    ): int
    {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }

        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }

        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_user_ticket FORCE INDEX (whitelabel_id_paid_lottery_id_date_index, paid_date_index)
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id 
        INNER JOIN whitelabel_transaction ON whitelabel_user_ticket.whitelabel_transaction_id = whitelabel_transaction.id            
        WHERE 1=1 ";

        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }

        if ($is_bonus) {
            $query_string .= " AND whitelabel_user_ticket.whitelabel_transaction_id IS NULL ";
        } else {
            $query_string .= " AND whitelabel_user_ticket.paid = " .
                Helpers_General::TICKET_PAID . " 
            AND whitelabel_user_ticket.whitelabel_transaction_id IS NOT NULL ";
        }

        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";
        }

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    /**
     * Fetch count of paid ticket lines for whitelabel filtered.
     *
     * @param string $add             filter adds.
     * @param array  $params          filter params.
     * @param int    $whitelabel_type whitelabel type could be null
     * @param int    $whitelabel_id   whitelabel id could be null
     * @param bool   $is_full_report  For full report noting changed
     *
     * @return int count of the paid ticket lines for specified whitelabel.
     */
    public static function get_line_sum_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ): int
    {
        // add non global params
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }

        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }

        $query_string = "SELECT 
            SUM(line_count) AS line_sum 
        FROM whitelabel_user_ticket FORCE INDEX (paid_date_index, whitelabel_user_ticket_w_id_paid_amount_idmx, whitelabel_id_paid_lottery_id_date_index)
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id 
        INNER JOIN whitelabel_transaction ON whitelabel_user_ticket.whitelabel_transaction_id = whitelabel_transaction.id
        WHERE 1=1 ";

        $query_string .= " AND whitelabel_user_ticket.paid = " .
            Helpers_General::TICKET_PAID . " 
        AND whitelabel_user_ticket.whitelabel_transaction_id IS NOT NULL ";

        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }

        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'line_sum');
    }

    /**
     * Fetch data for win tickets filtered.
     *
     * @param string $add             filter adds.
     * @param array  $params          filter params.
     * @param int    $whitelabel_type whitelabel type could be null
     * @param int    $whitelabel_id   whitelabel id could be null
     * @param bool   $is_full_report  For full report noting changed
     *
     * @return array consist won tickets data
     */
    public static function get_win_data_for_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ): array
    {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }

        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }

        $query_string = "SELECT 
            COUNT(*) AS count,
            COALESCE(SUM(whitelabel_user_ticket.prize_usd), 0.00) AS sum_prize_usd,
            COALESCE(SUM(whitelabel_user_ticket.prize_manager), 0.00) AS sum_prize_manager
        FROM whitelabel_user_ticket FORCE INDEX (whitelabel_id_paid_lottery_id_date_index, paid_date_index)
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }

        $query_string .= "WHERE whitelabel_user_ticket.paid = " . Helpers_General::TICKET_PAID . " ";
        $query_string .= "AND whitelabel_user_ticket.status = " . Helpers_General::TICKET_STATUS_WIN . " ";

        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result_row($result, [], 0);
    }

    /**
     * Fetch sums of paid tickets for whitelabel filtered by date.
     *
     * @param string $add           filter adds.
     * @param array  $params        filter params.
     * @param int    $whitelabel_id whitelabel id
     *
     * @return array consists of the sums of paid tickets for specified whitelabel.
     */
    public static function get_sums_paid_for_reports(
        string $add,
        array $params,
        int $whitelabel_id
    ): ?array
    {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        // Because internal SQL-s are a bit different
        // should have different alias for whitelabel_user_ticket
        // table to make possible to connect with external
        // table by lottery_id
        $add_internal = str_replace("wut.", "wut_internal.", $add);

        $query_string = "SELECT 
            wut.lottery_id, 
            
            COALESCE(SUM(wut.amount), 0.00) AS amount_sum, 
            COALESCE(SUM(wut.amount_local), 0.00) AS amount_local_sum, 
            COALESCE(SUM(wut.amount_usd), 0.00) AS amount_usd_sum, 
            COALESCE(SUM(wut.amount_manager), 0.00) AS amount_manager_sum,

            COALESCE(SUM(wut.cost_local), 0.00) AS cost_local_sum, 
            COALESCE(SUM(wut.cost_usd), 0.00) AS cost_usd_sum, 
            COALESCE(SUM(wut.cost), 0.00) AS cost_sum, 
            COALESCE(SUM(wut.cost_manager), 0.00) AS cost_manager_sum,

            COALESCE(SUM(wut.income_local), 0.00) AS income_local_sum, 
            COALESCE(SUM(wut.income_usd), 0.00) AS income_usd_sum, 
            COALESCE(SUM(wut.income), 0.00) AS income_sum, 
            COALESCE(SUM(wut.income_manager), 0.00) AS income_manager_sum,

            COALESCE(SUM(wut.margin_local), 0.00) AS margin_local_sum, 
            COALESCE(SUM(wut.margin_usd), 0.00) AS margin_usd_sum, 
            COALESCE(SUM(wut.margin), 0.00) AS margin_sum, 
            COALESCE(SUM(wut.margin_manager), 0.00) AS margin_manager_sum,
            
            (
                SELECT 
                    COALESCE(SUM(wutl.uncovered_prize), 0.00) AS uncovered_prize_sum 
                FROM whitelabel_user_ticket_line wutl 
                JOIN whitelabel_user_ticket wut_internal ON wut_internal.id = wutl.whitelabel_user_ticket_id 
                JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                WHERE wut_internal.lottery_id = wut.lottery_id " . $add_internal .
            ") AS uncovered_prize,
            (
                SELECT 
                    COALESCE(SUM(wutl.uncovered_prize_usd), 0.00) AS uncovered_prize_usd_sum 
                FROM whitelabel_user_ticket_line wutl 
                JOIN whitelabel_user_ticket wut_internal ON wut_internal.id = wutl.whitelabel_user_ticket_id 
                JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                WHERE wut_internal.lottery_id = wut.lottery_id " . $add_internal .
            ") AS uncovered_prize_usd,
            (
                SELECT 
                    COALESCE(SUM(wutl.uncovered_prize_local), 0.00) AS uncovered_prize_local_sum 
                FROM whitelabel_user_ticket_line wutl 
                JOIN whitelabel_user_ticket wut_internal ON wut_internal.id = wutl.whitelabel_user_ticket_id 
                JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                WHERE wut_internal.lottery_id = wut.lottery_id " . $add_internal .
            ") AS uncovered_prize_local,
            (
                SELECT 
                    COALESCE(SUM(wutl.uncovered_prize_manager), 0.00) AS uncovered_prize_manager_sum 
                FROM whitelabel_user_ticket_line wutl 
                JOIN whitelabel_user_ticket wut_internal ON wut_internal.id = wutl.whitelabel_user_ticket_id 
                JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                WHERE wut_internal.lottery_id = wut.lottery_id " . $add_internal .
            ") AS uncovered_prize_manager 
        FROM whitelabel_user_ticket wut
        JOIN whitelabel_user ON whitelabel_user.id = wut.whitelabel_user_id
        WHERE wut.whitelabel_id = :whitelabel_id 
            AND wut.paid = " . Helpers_General::TICKET_PAID .
            " " . $add . " 
        GROUP BY wut.lottery_id 
        ORDER BY wut.lottery_id";

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Get unsynchronized tickets for lottery with lines attached.
     * IMPORTANT: parameters are considered as clean, don't use this function in dangerous scopes.
     *
     * @param integer $lottery_id id of the lottery NOTE: string also works but must be numeric.
     * @param string  $draw_date  date of the draw for which tickets should be fetched.
     * @param array   $columns    columns to be selected by query, all by default.
     *
     * @return Database_Query_Builder_Select query.
     */
    public static function unsychronized_for_lottery_with_lines(
        int $lottery_id,
        string $draw_date,
        array $columns = ['*']
    ): Database_Query_Builder_Select
    {
        return DB::select_array($columns)
            ->from([self::$_table_name, 'wut']) // wut = whitelabel user ticket
            ->join(['whitelabel_user_ticket_line', 'wutl'], 'LEFT') // wutl = whitelabel user ticket line
            ->on('wutl.whitelabel_user_ticket_id', '=', 'wut.id')
            ->join(['whitelabel_user_ticket_keno_data', 'wutkd'], 'LEFT')
            ->on('wutkd.whitelabel_user_ticket_id', '=', 'wut.id')
            ->join(['lottery_type_multiplier', 'ltm'], 'LEFT')
            ->on('ltm.id', '=', 'lottery_type_multiplier_id')
            ->join(['lcs_ticket', 'lt'], 'LEFT')
            ->on('lt.whitelabel_user_ticket_slip_id', '=', 'wutl.whitelabel_user_ticket_slip_id')
            ->where('lt.id', 'IS', null)
            ->where('wut.lottery_id', '=', $lottery_id)
            ->where('draw_date', '=', $draw_date)
            ->where('paid', '=', true)
            ->where('model', 'in', [self::MODEL_PURCHASE, self::MODEL_PURCHASE_SCAN])
            ->order_by('wutl.whitelabel_user_ticket_id', 'asc')
            ->order_by('whitelabel_user_ticket_slip_id', 'asc')
            ->order_by('wutl.id', 'asc'); // from the oldest

    }

    /**
     * Get unsynchronized tickets for lottery with lines attached.
     * Select only columns needed for lcs synchronization.
     * IMPORTANT: parameters are considered as clean, don't use this function in dangerous scopes.
     *
     * @param integer $lottery_id id of the lottery NOTE: string also works but must be numeric.
     * @param string  $draw_date  date of the draw for which tickets should be fetched.
     *
     * @return Database_Query_Builder_Select query.
     */
    public static function unsychronized_for_lottery_with_lines_lcs(
        int $lottery_id,
        string $draw_date
    ): Database_Query_Builder_Select
    {
        return self::unsychronized_for_lottery_with_lines(
            $lottery_id,
            $draw_date,
            [['wutl.id', 'id'], ['wutl.whitelabel_user_ticket_slip_id', 'whitelabel_user_ticket_slip_id'], 'ip', 'numbers', 'bnumbers', 'wutl.whitelabel_user_ticket_id', ['token', 'ticket_token'], 'multiplier', 'numbers_per_line']
        );
    }

    /**
     * Mass update tickets for specified lottery.
     * Set their synchronization status to true.
     *
     * @param int $lottery_id
     *
     * @return int result of update (how many rows were updated)
     * @throws \Exception on database errors.
     */
    public static function update_to_synchronized_for_lottery(int $lottery_id): int
    {
        return DB::update(self::$_table_name)
            ->value('is_synchronized', 1)
            ->value('date_processed', Carbon::now()->toDateTimeString())
            ->where('lottery_id', '=', $lottery_id)
            ->execute();
    }

    /**
     * Build query, which will update is_synchronized flag for specified tickets.
     *
     * @param array $ids
     *
     * @return Database_Query_Builder_Update
     */
    public static function buildQueryBuilderUpdateToSynchronized(array $ids): Database_Query_Builder_Update
    {
        return DB::update(self::$_table_name)
            ->value('is_synchronized', true)
            ->where('id', 'IN', $ids);
    }

    /**
     * Mass update tickets for specified ids.
     * Set their synchronization status to true.
     *
     * @param array $ids
     *
     * @return int result of update (how many rows were updated)
     * @throws \Exception on database errors.
     */
    public static function updateToSynchronized(array $ids): int
    {
        return self::buildQueryBuilderUpdateToSynchronized($ids)
            ->execute();
    }

    /**
     * Get pending tickets for lottery with lcs_ticket (and slips).
     *
     * @param integer $lottery_id id of the lottery NOTE: string also works but must be numeric.
     * @param string  $draw_date  date of draw that tickets belongs to.
     * @param array  $columns    columns to be selected by query, all by default.
     *
     * @return Database_Query_Builder_Select query.
     */
    public static function pending_for_lottery_with_lcs_ticket_user_and_whitelabel(
        int $lottery_id,
        string $draw_date,
        array $columns = ['*']
    ): Database_Query_Builder_Select
    {
        return DB::select_array($columns)
            ->from([self::$_table_name, 'wut'])
            ->join(['whitelabel', 'w'], 'LEFT')
            ->on('w.id', '=', 'wut.whitelabel_id')
            ->join(['whitelabel_user', 'wu'], 'LEFT')
            ->on('wu.id', '=', 'whitelabel_user_id')
            ->join(['whitelabel_user_ticket_slip', 'wuts'], 'LEFT')
            ->on('whitelabel_user_ticket_id', '=', 'wut.id')
            ->join(['lcs_ticket', 'lt'], 'LEFT')
            ->on('whitelabel_user_ticket_slip_id', '=', 'wuts.id')
            ->join(['whitelabel_user_group', 'wug'], 'LEFT')
            ->on('wug.id', '=', 'wu.prize_payout_whitelabel_user_group_id')
            ->where('lottery_id', '=', $lottery_id)
            ->where('draw_date', '=', $draw_date)
            ->where('is_synchronized', '=', true)
            ->where('status', '=', Helpers_General::TICKET_STATUS_PENDING);
    }

    /**
     * Get pending tickets for lottery with lcs_ticket (and slips), select columns for update ticket prize task.
     *
     * @param integer $lottery_id id of the lottery NOTE: string also works but must be numeric.
     * @param string  $draw_date  date of draw that tickets belongs to.
     *
     * @return Database_Query_Builder_Select query.
     */
    public static function pending_for_lottery_with_lcs_ticket_user_and_whitelabel_task(
        int $lottery_id,
        string $draw_date
    ): Database_Query_Builder_Select
    {
        return self::pending_for_lottery_with_lcs_ticket_user_and_whitelabel(
            $lottery_id,
            $draw_date,
            [
                ['wut.id', 'ticket_id'],
                'uuid',
                ['wu.currency_id', 'user_currency_id'],
                ['w.manager_site_currency_id', 'manager_currency_id'],
                ['wut.whitelabel_user_id', 'user_id'],
                ['wug.prize_payout_percent', 'prize_payout_percent']
            ]
        );
    }

    /**
     * Set tickets and their lines as lost, based on ticket ids.
     *
     * @param array $ticket_ids ids of the tickets that should be updated.
     *
     * @return int number of rows updated
     * @throws \Exception on database errors.
     */
    public static function set_as_lost_with_lines(array $ticket_ids): int
    {
        return DB::update([self::$_table_name, 'wut'])
            ->join(['whitelabel_user_ticket_line', 'wutl'], 'INNER')
            ->on('whitelabel_user_ticket_id', '=', 'wut.id')
            ->set(
                [
                    'wut.status' => Helpers_General::TICKET_STATUS_NO_WINNINGS,
                    'wut.date_processed' => Helpers_Time::now(),
                    'wut.prize_local' => 0,
                    'wut.prize_usd' => 0,
                    'wut.prize' => 0,
                    'wut.prize_net_local' => 0,
                    'wut.prize_net_usd' => 0,
                    'wut.prize_net' => 0,
                    'wut.payout' => true,
                    'wutl.status' => Helpers_General::TICKET_STATUS_NO_WINNINGS,
                    'wutl.prize_local' => 0,
                    'wutl.prize_usd' => 0,
                    'wutl.prize' => 0,
                    'wutl.prize_net_local' => 0,
                    'wutl.prize_net_usd' => 0,
                    'wutl.prize_net' => 0,
                    'wutl.uncovered_prize_local' => 0,
                    'wutl.uncovered_prize_usd' => 0,
                    'wutl.uncovered_prize' => 0,
                    'wutl.payout' => true,
                ]
            )
            ->where('wut.id', 'IN', $ticket_ids)
            ->execute();
    }

    /**
     * Build base query for set winning tickets.
     *
     * @param string                 $prize_local
     * @param string                 $prize_usd
     * @param Model_Lottery_Provider $provider
     * @param bool                   $payout
     * @param bool                   $is_prize_jackpot
     *
     * @return Database_Query_Builder_Update query.
     */
    public static function set_winning_base(
        string $prize_local,
        string $prize_usd,
        Model_Lottery_Provider $provider,
        bool $payout,
        bool $is_prize_jackpot
    ): Database_Query_Builder_Update
    {
        return self::set_winning_base_($prize_local, $prize_usd, $provider, $payout, [
            'date_processed' => Helpers_Time::now(),
            'prize_jackpot' => $is_prize_jackpot,
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
        self::set_winning_manager_($query, $prize_manager, $provider);
    }

    /**
     * Finish set winning query and compile it.
     *
     * @param Database_Query_Builder_Update $query
     * @param string                        $prize_user
     * @param array                         $ticket_ids
     * @param Model_Lottery_Provider        $provider
     *
     * @return string compiled query in sql.
     */
    public static function set_winning_compile(
        Database_Query_Builder_Update $query,
        string $prize_user,
        array $ticket_ids,
        Model_Lottery_Provider $provider
    ): string
    {
        return self::set_winning_finish_($query, $prize_user, $ticket_ids, $provider)
            ->compile();
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_sold_tickets_count_subquery(
        string $sub_phrase = ""
    ): string
    {
        $sold_tickets_query_text = "(
            SELECT 
                COUNT(*) AS sold_count 
            FROM whitelabel_user_ticket wut_internal 
            INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
            INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        $sold_tickets_query_text .= $sub_phrase;

        $sold_tickets_query_text .= " AND wut_internal.paid = " . Helpers_General::TICKET_PAID . " ";

        $sold_tickets_query_text .= " AND wut_internal.whitelabel_transaction_id IS NOT NULL";

        $sold_tickets_query_text .= ") AS sold_tickets_count, ";

        return $sold_tickets_query_text;
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_sold_lines_count_subquery(
        string $sub_phrase = ""
    ): string
    {
        $sold_lines_query_text = "(
            SELECT 
                SUM(line_count) AS sold_line_count 
            FROM whitelabel_user_ticket wut_internal 
            INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
            INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        $sold_lines_query_text .= $sub_phrase;

        $sold_lines_query_text .= " AND wut_internal.paid = " . Helpers_General::TICKET_PAID . " ";

        $sold_lines_query_text .= " AND wut_internal.whitelabel_transaction_id IS NOT NULL";

        $sold_lines_query_text .= ") AS sold_lines_count, ";

        return $sold_lines_query_text;
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_bonus_count_subquery(
        string $sub_phrase = ""
    ): string
    {
        $bonuses_query_text = "(
            SELECT 
                SUM(wut_internal.line_count) AS bonus_count 
            FROM whitelabel_user_ticket wut_internal 
            INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
            INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        $bonuses_query_text .= $sub_phrase;

        $bonuses_query_text .= " AND wut_internal.whitelabel_transaction_id IS NULL";

        $bonuses_query_text .= ") AS bonus_tickets_count, ";

        return $bonuses_query_text;
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_costs_sums_subquery(
        string $sub_phrase = ""
    ): string
    {
        $costs_definitions = [
            'cost',
            'cost_usd',
            'cost_manager'
        ];

        $costs_query_texts = "";

        foreach ($costs_definitions as $key => $single_text) {
            $single_query_string = "(
                SELECT 
                    COALESCE(SUM(wut_internal." . $single_text . "), 0.00) AS sum_" . $single_text . " 
                FROM whitelabel_user_ticket wut_internal 
                INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id 
                INNER JOIN whitelabel_transaction ON whitelabel_transaction.id = wut_internal.whitelabel_transaction_id ";

            $single_query_string .= $sub_phrase;

            $single_query_string .= " AND whitelabel_transaction.type = " .
                Helpers_General::TYPE_TRANSACTION_PURCHASE . " 
                AND whitelabel_transaction.status = " .
                Helpers_General::STATUS_TRANSACTION_APPROVED . " ";

            $single_query_string .= ") AS " . $single_text . "_sum, ";

            $costs_query_texts .= $single_query_string;
        }

        $costs_query_string = $costs_query_texts . " ";

        return $costs_query_string;
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_win_sums(
        string $sub_phrase = ""
    ): string
    {
        $win_sums_definitions = [
            'usd',
            'manager'
        ];

        $win_sums_query_string = "";

        foreach ($win_sums_definitions as $key => $single_text) {
            $win_sums_query_string .= "(
                SELECT 
                    COALESCE(SUM(wut_internal.prize_" . $single_text . "), 0.00) AS sum_prize_" . $single_text . "  
                FROM whitelabel_user_ticket wut_internal 
                INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

            $win_sums_query_string .= $sub_phrase;

            $win_sums_query_string .= "
                AND wut_internal.status = " . Helpers_General::TICKET_STATUS_WIN . "  
            ) AS win_" . $single_text . "_sum, ";
        }

        return $win_sums_query_string;
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_income_sums_subquery(
        string $sub_phrase = ""
    ): string
    {
        $incomes_definitions = [
            'income_local',
            'income_usd',
            'income',
            'income_manager'
        ];

        $incomes_query_texts = "";

        foreach ($incomes_definitions as $key => $single_text) {
            $single_query_string = "(
                SELECT 
                    COALESCE(SUM(wut_internal." . $single_text . "), 0.00) AS sum_" . $single_text . " 
                FROM whitelabel_user_ticket wut_internal 
                INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

            $single_query_string .= $sub_phrase;

            $single_query_string .= " AND wut_internal.paid = " . Helpers_General::TICKET_PAID;

            $single_query_string .= " AND wut_internal.whitelabel_transaction_id IS NOT NULL";

            $single_query_string .= ") AS " . $single_text . "_sum, ";

            $incomes_query_texts .= $single_query_string;
        }

        $incomes_query_string = $incomes_query_texts . " ";

        return $incomes_query_string;
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_bonus_sums_subquery(
        string $sub_phrase = ""
    ): string
    {
        $bonuses_definitions = [
            'income_local',
            'income_usd',
            'income',
            'income_manager'
        ];

        $bonuses_query_texts = "";

        foreach ($bonuses_definitions as $key => $single_text) {
            $single_query_string = "(
                SELECT 
                    COALESCE(ABS(SUM(wut_internal." . $single_text . ")), 0.00) AS sum_bonus_" . $single_text . " 
                FROM whitelabel_user_ticket wut_internal 
                INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

            $single_query_string .= $sub_phrase;

            $single_query_string .= " AND wut_internal.whitelabel_transaction_id IS NULL";

            $single_query_string .= ") AS bonus_" . $single_text . "_sum, ";

            $bonuses_query_texts .= $single_query_string;
        }

        $bonuses_query_string = $bonuses_query_texts . " ";

        return $bonuses_query_string;
    }

    /**
     *
     * @param string $sub_phrase
     *
     * @return string
     */
    private static function prepare_uncovered_sums_subquery(
        string $sub_phrase = ""
    ): string
    {
        $uncovered_prices_definitions = [
            'prize',
            'prize_usd',
            'prize_local',
            'prize_manager'
        ];

        $uncovered_prices_query_string = [];

        foreach ($uncovered_prices_definitions as $key => $single_text) {
            $single_query_string = "(
                SELECT 
                    COALESCE(SUM(wutl.uncovered_" . $single_text . "), 0.00) AS uncovered_" . $single_text . "_sum 
                FROM whitelabel_user_ticket_line wutl 
                INNER JOIN whitelabel_user_ticket wut_internal ON wut_internal.id = wutl.whitelabel_user_ticket_id 
                INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
                INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

            $single_query_string .= $sub_phrase;

            $single_query_string .= ") AS uncovered_" . $single_text;

            $uncovered_prices_query_string[] = $single_query_string;
        }

        $query_string = implode(", ", $uncovered_prices_query_string);

        return $query_string;
    }

    /**
     *
     * @param string $add_internal
     * @param int    $whitelabel_type
     * @param int    $whitelabel_id
     * @param bool   $is_full_report For full report noting changed
     *
     * @return string
     */
    private static function get_sub_phrase(
        string $add_internal = "",
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ): string
    {
        $sub_phrase = "";
        if (!empty($whitelabel_type)) {
            $sub_phrase .= " AND whitelabel.type = :whitelabel_type ";
        }

        $sub_phrase .= "WHERE wut_internal.lottery_id = wut.lottery_id " .
            $add_internal . " ";

        if (!empty($whitelabel_id)) {
            $sub_phrase .= " AND whitelabel.id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $sub_phrase .= " AND whitelabel.is_report = 1 ";
        }

        return $sub_phrase;
    }

    /**
     * Fetch sums of paid tickets for whitelabel filtered by date.
     *
     * @param string $add             filter adds.
     * @param array  $params          filter params.
     * @param array  $params          filter params.
     * @param array  $sort            Sort data table.
     * @param int    $whitelabel_type whitelabel type could be null
     * @param int    $whitelabel_id   whitelabel id could be null
     * @param bool   $is_full_report  For full report noting changed
     *
     * @return array consists of the sums of amounts, incomes, costs, margins
     */
    public static function get_sums_paid_for_full_reports(
        string $add,
        array $params,
        array $sort = [],
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ): ?array
    {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }

        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }

        if (!empty($sort) && !empty($sort["db"])) {
            $params[] = [":order", DB::expr($sort["db"])];
        }

        // Because internal SQL-s are a bit different
        // should have different alias for whitelabel_user_ticket
        // table to make possible to connect with external
        // table by lottery_id
        $add_internal = str_replace("wut.", "wut_internal.", $add);

        // Because that sub phrase is needed to insert within different
        // sub queries to dont repeat the whole code many times
        $sub_phrase = self::get_sub_phrase(
            $add_internal,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );

        $main_query = "SELECT 
            lottery.id,
            lottery.name AS lottery_name,

            COALESCE(inner_total_query.sold_tickets_count, 0) AS lottery_sold_tickets_count,
            COALESCE(inner_total_query.sold_lines_count, 0) AS lottery_sold_lines_count,
            
            COALESCE(inner_total_query.bonus_tickets_count, 0) AS lottery_bonus_tickets_count,

            COALESCE(inner_total_query.win_tickets_count, 0) AS lottery_win_tickets_count,
            COALESCE(inner_total_query.win_usd_sum, 0) AS lottery_win_usd_sum,
            COALESCE(inner_total_query.win_manager_sum, 0) AS lottery_win_manager_sum,

            COALESCE(inner_total_query.amount_sum, 0.00) AS lottery_amount_sum, 
            COALESCE(inner_total_query.amount_local_sum, 0.00) AS lottery_amount_local_sum, 
            COALESCE(inner_total_query.amount_usd_sum, 0.00) AS lottery_amount_usd_sum, 
            COALESCE(inner_total_query.amount_manager_sum, 0.00) AS lottery_amount_manager_sum,

            COALESCE(inner_total_query.cost_sum, 0.00) AS lottery_cost_sum, 
            COALESCE(inner_total_query.cost_usd_sum, 0.00) AS lottery_cost_usd_sum, 
            COALESCE(inner_total_query.cost_manager_sum, 0.00) AS lottery_cost_manager_sum,

            COALESCE(inner_total_query.income_local_sum, 0.00) AS lottery_income_local_sum, 
            COALESCE(inner_total_query.income_usd_sum, 0.00) AS lottery_income_usd_sum, 
            COALESCE(inner_total_query.income_sum, 0.00) AS lottery_income_sum, 
            COALESCE(inner_total_query.income_manager_sum, 0.00) AS lottery_income_manager_sum,
            
            COALESCE(inner_total_query.bonus_income_local_sum, 0.00) AS lottery_bonus_local_sum, 
            COALESCE(inner_total_query.bonus_income_usd_sum, 0.00) AS lottery_bonus_usd_sum, 
            COALESCE(inner_total_query.bonus_income_sum, 0.00) AS lottery_bonus_sum, 
            COALESCE(inner_total_query.bonus_income_manager_sum, 0.00) AS lottery_bonus_manager_sum,

            COALESCE(inner_total_query.margin_local_sum, 0.00) AS lottery_margin_local_sum, 
            COALESCE(inner_total_query.margin_usd_sum, 0.00) AS lottery_margin_usd_sum, 
            COALESCE(inner_total_query.margin_sum, 0.00) AS lottery_margin_sum, 
            COALESCE(inner_total_query.margin_manager_sum, 0.00) AS lottery_margin_manager_sum,

            COALESCE(inner_total_query.uncovered_prize, 0.00) AS lottery_uncovered_prize_sum, 
            COALESCE(inner_total_query.uncovered_prize_usd, 0.00) AS lottery_uncovered_prize_usd_sum, 
            COALESCE(inner_total_query.uncovered_prize_local, 0.00) AS lottery_uncovered_prize_local_sum, 
            COALESCE(inner_total_query.uncovered_prize_manager, 0.00) AS lottery_uncovered_prize_manager_sum

        FROM lottery 
        LEFT JOIN (";

        $query_string = "SELECT 
            lottery.id, ";

        $query_string .= self::prepare_sold_tickets_count_subquery($sub_phrase);

        $query_string .= self::prepare_sold_lines_count_subquery($sub_phrase);

        $query_string .= self::prepare_bonus_count_subquery($sub_phrase);

        $query_string .= "COALESCE(SUM(wut.amount), 0.00) AS amount_sum, 
            COALESCE(SUM(wut.amount_local), 0.00) AS amount_local_sum, 
            COALESCE(SUM(wut.amount_usd), 0.00) AS amount_usd_sum, 
            COALESCE(SUM(wut.amount_manager), 0.00) AS amount_manager_sum, ";

        $query_string .= self::prepare_costs_sums_subquery($sub_phrase);

        $query_string .= "COALESCE(SUM(wut.margin_local), 0.00) AS margin_local_sum, 
            COALESCE(SUM(wut.margin_usd), 0.00) AS margin_usd_sum, 
            COALESCE(SUM(wut.margin), 0.00) AS margin_sum, 
            COALESCE(SUM(wut.margin_manager), 0.00) AS margin_manager_sum,
            ";

        // Win count
        $query_string .= "(
            SELECT 
                COUNT(whitelabel_user_ticket_line.id) AS win_count 
            FROM whitelabel_user_ticket_line
            INNER JOIN whitelabel_user_ticket wut_internal ON whitelabel_user_ticket_line.whitelabel_user_ticket_id = wut_internal.id
            INNER JOIN whitelabel_user ON whitelabel_user.id = wut_internal.whitelabel_user_id 
            INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }

        $query_string .= "WHERE wut_internal.lottery_id = wut.lottery_id " .
            $add_internal . " ";

        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel.id = :whitelabel_id ";
        }

        $query_string .= "
            AND whitelabel_user_ticket_line.status = " . Helpers_General::TICKET_STATUS_WIN . "  
        ) AS win_tickets_count, ";

        $query_string .= self::prepare_win_sums($sub_phrase);
        $query_string .= " ";

        $query_string .= self::prepare_income_sums_subquery($sub_phrase);
        $query_string .= " ";

        $query_string .= self::prepare_bonus_sums_subquery($sub_phrase);
        $query_string .= " ";

        $query_string .= self::prepare_uncovered_sums_subquery($sub_phrase);
        $query_string .= " ";

        $query_string .= "FROM lottery 
            INNER JOIN whitelabel_user_ticket wut ON wut.lottery_id = lottery.id 
            INNER JOIN whitelabel_user ON whitelabel_user.id = wut.whitelabel_user_id 
            INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }

        $query_string .= "WHERE wut.paid = " .
            Helpers_General::TICKET_PAID . " ";

        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel.id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }

        $query_string .= $add . " 
            GROUP BY lottery.id ";

        $main_query .= $query_string;
        $main_query .= "
        ) AS inner_total_query 
            ON lottery.id = inner_total_query.id 
        WHERE lottery.is_enabled = 1 ";

        if (!empty($sort) && !empty($sort["db"])) {
            $main_query .= " ORDER BY :order ";
        }

        // execute safe query
        $result = parent::execute_query($main_query, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch sums of paid tickets for whitelabel filtered by date.
     *
     * @param string $addExtraFilters Can be mix of country, language, date range filters
     * @param array  $params          filter params.
     * @param array  $sort            Sort data table.
     * @param int    $whitelabelType whitelabel type could be null
     * @param int    $whitelabelId   whitelabel id could be null
     * @param bool   $isFullReport  For full report nothing changed
     *
     * @return array consists of the sums of amounts, incomes, costs, margins
     */
    public static function getOptimizedSumsPaidForFullReports(
        string $addExtraFilters,
        array $params,
        array $sort = [],
        ?int $whitelabelType = null,
        ?int $whitelabelId = null,
        bool $isFullReport = false
    ): ?array
    {
        if (!empty($whitelabelType)) {
            $params[] = [":whitelabel_type", $whitelabelType];
        }

        if (!empty($whitelabelId)) {
            $params[] = [":whitelabel_id", $whitelabelId];
        }

        if (!empty($sort) && !empty($sort["db"])) {
            $params[] = [":order", DB::expr($sort["db"])];
        }

        $mainQuery = "SELECT
            lottery.type,
            lottery.id,
            lottery.slug,
            lottery.name AS lottery_name,
            COALESCE(count(wut.id), 0) AS lottery_sold_tickets_count,
            COALESCE(sum(wut.line_count), 0) AS lottery_sold_lines_count,
            COALESCE(count(IF(wut.whitelabel_transaction_id is null, 1, null)), 0) AS lottery_bonus_tickets_count,
            COALESCE(count(IF(wut.status = '1', 1, null)), 0) AS lottery_win_tickets_count,
            COALESCE(sum(wut.prize_usd), 0.00) AS lottery_win_usd_sum,
            COALESCE(sum(wut.prize_manager), 0.00) AS lottery_win_manager_sum,
            COALESCE(sum(wut.amount), 0.00) AS lottery_amount_sum,
            COALESCE(sum(wut.amount_local), 0.00) AS lottery_amount_local_sum,
            COALESCE(sum(wut.amount_usd), 0.00) AS lottery_amount_usd_sum,
            COALESCE(sum(wut.amount_manager), 0.00) AS lottery_amount_manager_sum,
            COALESCE(sum(wut.cost), 0.00) AS lottery_cost_sum,
            COALESCE(sum(wut.cost_usd), 0.00) AS lottery_cost_usd_sum,
            COALESCE(sum(wut.cost_manager), 0.00) AS lottery_cost_manager_sum,
            COALESCE(sum(wut.income_local), 0.00) AS lottery_income_local_sum,
            COALESCE(sum(wut.income_usd), 0.00) AS lottery_income_usd_sum,
            COALESCE(sum(wut.income), 0.00) AS lottery_income_sum,
            COALESCE(sum(wut.income_manager), 0.00) AS lottery_income_manager_sum,
            COALESCE(abs(sum(IF(wut.whitelabel_transaction_id is null, wut.income_local, 0))), 0.00) AS lottery_bonus_local_sum,
            COALESCE(abs(sum(IF(wut.whitelabel_transaction_id is null, wut.income_usd, 0))), 0.00) AS lottery_bonus_usd_sum,
            COALESCE(abs(sum(IF(wut.whitelabel_transaction_id is null, wut.income, 0))), 0.00) AS lottery_bonus_sum,
            COALESCE(abs(sum(IF(wut.whitelabel_transaction_id is null, wut.income_manager, 0))), 0.00) AS lottery_bonus_manager_sum,
            COALESCE(sum(wut.margin_local), 0.00) AS lottery_margin_local_sum,
            COALESCE(sum(wut.margin_usd), 0.00) AS lottery_margin_usd_sum,
            COALESCE(sum(wut.margin), 0.00) AS lottery_margin_sum,
            COALESCE(sum(wut.margin_manager), 0.00) AS lottery_margin_manager_sum,

            0.00 AS lottery_uncovered_prize_sum,
            0.00 AS lottery_uncovered_prize_usd_sum,
            0.00 AS lottery_uncovered_prize_local_sum,
            0.00 AS lottery_uncovered_prize_manager_sum

        FROM lottery ";

        $queryString = "LEFT JOIN whitelabel_user_ticket wut
        ON wut.lottery_id = lottery.id
        LEFT JOIN whitelabel_transaction wt
        ON wut.whitelabel_transaction_id = wt.id
        LEFT JOIN whitelabel_user
        ON whitelabel_user.id = wut.whitelabel_user_id
        INNER JOIN whitelabel
        ON whitelabel_user.whitelabel_id = whitelabel.id
        WHERE lottery.is_enabled = 1";

        if (!empty($whitelabelId)) {
            $queryString .= " AND wut.whitelabel_id = :whitelabel_id ";
        }

        if (!empty($whitelabelType)) {
            $queryString .= " AND whitelabel.id IN (SELECT id FROM whitelabel WHERE type = :whitelabel_type) ";
        }

        if (!$isFullReport && is_null($whitelabelId)) {
            $queryString .= " AND whitelabel.id IN (SELECT id FROM whitelabel WHERE is_report = 1) ";
        }

        $queryString .= $addExtraFilters;

        $queryString .= "AND wut.paid = " . Helpers_General::TICKET_PAID . "
            GROUP BY lottery.id, lottery.name";

        $mainQuery .= $queryString;

        if (!empty($sort) && !empty($sort["db"])) {
            $mainQuery .= " ORDER BY :order ";
        }

        // execute safe query
        $result = parent::execute_query($mainQuery, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch sums of bonuses filtered.
     *
     * @param string $add           filter adds.
     * @param array  $params        filter params.
     * @param int    $whitelabel_id whitelabel id
     *
     * @return array consists of the sums of bonuses for specified whitelabel.
     */
    public static function get_sums_bonuses_for_reports(
        string $add,
        array $params,
        int $whitelabel_id
    ): ?array
    {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $query_string = "SELECT 
            COALESCE(SUM(bonus_cost_manager), 0.00) AS sum_bonus_cost_manager 
        FROM whitelabel_user_ticket 
        JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id 
        WHERE whitelabel_user_ticket.whitelabel_id = :whitelabel_id 
            AND whitelabel_user_ticket.whitelabel_transaction_id IS NULL " .
            $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch sums of bonuses filtered.
     *
     * @param string $add             filter adds.
     * @param array  $params          filter params.
     * @param int    $whitelabel_type whitelabel type could be null
     * @param int    $whitelabel_id   whitelabel id could be null
     * @param bool   $is_full_report  For full report noting changed
     *
     * @return array consists of the sums of bonuses
     */
    public static function get_sums_bonuses_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ): ?array
    {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }

        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }

        $query_string = "SELECT 
            COALESCE(SUM(bonus_cost_manager), 0.00) AS sum_bonus_cost_manager, 
            COALESCE(SUM(bonus_cost_usd), 0.00) AS sum_bonus_cost_usd 
        FROM whitelabel_user_ticket 
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }

        $query_string .= "WHERE whitelabel_user_ticket.whitelabel_transaction_id IS NULL ";

        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }

        $query_string .= $add;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     *
     * @access public
     *
     * @param int $whitelabel_id
     *
     * @return array
     */
    public static function get_sold_tickets_lines_count_for_crm_last_seven_days($whitelabel_id): array
    {
        $res = [];

        $query = DB::select(DB::expr('SUM(line_count) AS count'), DB::expr('DATE(date) AS date'))
            ->from('whitelabel_user_ticket')
            ->where('paid', '=', 1)
            ->and_where('whitelabel_transaction_id', 'IS NOT', null)
            ->and_where('date', '>=', DB::expr('DATE(NOW()) - INTERVAL 6 DAY'));

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return int
     */
    public static function get_sold_tickets_lines_count_for_crm($whitelabel_id, $start_date, $end_date): int
    {
        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('SUM(line_count) as count'))
            ->from('whitelabel_user_ticket')
            ->where('paid', '=', 1)
            ->and_where('whitelabel_transaction_id', 'IS NOT', null)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        /** @var object $query */
        $response = $query->execute()->as_array();

        return $response[0]['count'] ?? 0;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return float
     */
    public static function get_sold_tickets_amount_for_crm($whitelabel_id, $start_date, $end_date): float
    {
        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('SUM(amount_usd) as amount'))
            ->from('whitelabel_user_ticket')
            ->where('paid', '=', 1)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        /** @var object $query */
        $response = $query->execute()->as_array();

        return $response[0]['amount'] ?? 0;
    }

    /**
     *
     * @access public
     *
     * @param int $whitelabel_id
     *
     * @return array
     */
    public static function get_won_tickets_count_for_crm_last_seven_days($whitelabel_id): array
    {
        $query = DB::select(DB::expr('COUNT(whitelabel_user_ticket_line.id) AS count'), DB::expr('DATE(date) AS date'))
            ->from('whitelabel_user_ticket_line')
            ->join(['whitelabel_user_ticket', DB::expr('whitelabel_user_ticket')])
            ->on('whitelabel_user_ticket_line.whitelabel_user_ticket_id', '=', 'whitelabel_user_ticket.id')
            ->where('whitelabel_user_ticket.date', '>=', DB::expr('DATE(NOW()) - INTERVAL 6 DAY'))
            ->and_where('whitelabel_user_ticket_line.status', '=', 1);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(whitelabel_user_ticket.date)'));
        /** @var object $query */
        $res = $query->execute()->as_array() ?? [];

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return int
     */
    public static function get_won_tickets_count_for_crm($whitelabel_id, $start_date, $end_date): int
    {
        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = $query = DB::select(DB::expr('COUNT(whitelabel_user_ticket_line.id) as count'))
            ->from('whitelabel_user_ticket_line')
            ->join('whitelabel_user_ticket')
            ->on('whitelabel_user_ticket_line.whitelabel_user_ticket_id', '=', 'whitelabel_user_ticket.id')
            ->where('whitelabel_user_ticket.date', '>=', $start)
            ->and_where('whitelabel_user_ticket.date', '<=', $end)
            ->and_where('whitelabel_user_ticket_line.status', '=', 1);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }

        /** @var object $query */
        $response = $query->execute()->as_array();

        return $response[0]['count'] ?? 0;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_amount_for_crm_last_seven_days($whitelabel_id): array
    {
        $res = [];

        $amount = DB::expr('SUM(amount) AS count');

        if ($whitelabel_id == 0) {
            $amount = DB::expr('SUM(amount_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $amount = DB::expr('SUM(amount_manager) AS count');
        }

        $query = DB::select($amount, DB::expr('DATE(date) AS date'))
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', DB::expr('DATE(NOW()) - INTERVAL 6 DAY'));

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public static function get_amount_for_crm_date_range($whitelabel_id, $start_date, $end_date): array
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $amount = DB::expr('SUM(amount) AS count');

        if ($whitelabel_id == 0) {
            $amount = DB::expr('SUM(amount_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $amount = DB::expr('SUM(amount_manager) AS count');
        }

        $query = DB::select($amount, DB::expr('DATE(date) AS date'))
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return float
     */
    public static function get_total_amount_for_crm($whitelabel_id, $start_date, $end_date): float
    {
        $res = 0;

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $amount = DB::expr('SUM(amount) AS count');

        if ($whitelabel_id == 0) {
            $amount = DB::expr('SUM(amount_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $amount = DB::expr('SUM(amount_manager) AS count');
        }

        $query = DB::select($amount)
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->where('whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (isset($result[0]['count'])) {
            $res = $result[0]['count'];
        }

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int $whitelabel_id
     *
     * @return array
     */
    public static function get_cost_for_crm_last_seven_days($whitelabel_id): array
    {
        $res = [];

        $cost = DB::expr('SUM(cost) AS count');

        if ($whitelabel_id == 0) {
            $cost = DB::expr('SUM(cost_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $cost = DB::expr('SUM(cost_manager) AS count');
        }

        $query = DB::select($cost, DB::expr('DATE(date) AS date'))
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', DB::expr('DATE(NOW()) - INTERVAL 6 DAY'));

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return array
     */
    public static function get_cost_for_crm_date_range($whitelabel_id, $start_date, $end_date): array
    {
        $res = [];

        $cost = DB::expr('SUM(cost) AS count');

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        if ($whitelabel_id == 0) {
            $cost = DB::expr('SUM(cost_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $cost = DB::expr('SUM(cost_manager) AS count');
        }

        $query = DB::select($cost, DB::expr('DATE(date) AS date'))
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return float
     */
    public static function get_total_cost_for_crm($whitelabel_id, $start_date, $end_date): float
    {
        $res = 0;

        $cost = DB::expr('SUM(cost) AS count');

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        if ($whitelabel_id == 0) {
            $cost = DB::expr('SUM(cost_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $cost = DB::expr('SUM(cost_manager) AS count');
        }

        $query = DB::select($cost)
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->where('whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (isset($result[0]['count'])) {
            $res = $result[0]['count'];
        }

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int $whitelabel_id
     *
     * @return array
     */
    public static function get_income_for_crm_last_seven_days($whitelabel_id): array
    {
        $income = DB::expr('SUM(income) AS count');

        if ($whitelabel_id == 0) {
            $income = DB::expr('SUM(income_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $income = DB::expr('SUM(income_manager) AS count');
        }

        $query = DB::select($income, DB::expr('DATE(date) AS date'))
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', DB::expr('DATE(NOW()) - INTERVAL 6 DAY'));

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return array
     */
    public static function get_income_for_crm_date_range($whitelabel_id, $start_date, $end_date): array
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $income = DB::expr('SUM(income) AS count');

        if ($whitelabel_id == 0) {
            $income = DB::expr('SUM(income_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $income = DB::expr('SUM(income_manager) AS count');
        }

        $query = DB::select($income, DB::expr('DATE(date) AS date'))
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);
        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return float
     */
    public static function get_total_income_for_crm($whitelabel_id, $start_date, $end_date): float
    {
        $res = 0;

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $income = DB::expr('SUM(income) AS count');

        if ($whitelabel_id == 0) {
            $income = DB::expr('SUM(income_usd) AS count');
        } elseif ($whitelabel_id > 0) {
            $income = DB::expr('SUM(income_manager) AS count');
        }

        $query = DB::select($income)->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->where('whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (isset($result[0]['count'])) {
            $res = $result[0]['count'];
        }

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return array
     */
    public static function get_top_seller_lotteries_for_crm($whitelabel_id, $start_date, $end_date): array
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $amount = DB::expr('SUM(amount) AS amount');

        if ($whitelabel_id == 0) {
            $amount = DB::expr('SUM(amount_usd) AS amount');
        } elseif ($whitelabel_id > 0) {
            $amount = DB::expr('SUM(amount_manager) AS amount');
        }

        $query = DB::select(
            $amount,
            'lottery.name'
        )
            ->from('whitelabel_user_ticket')
            ->join('lottery')->on('whitelabel_user_ticket.lottery_id', '=', 'lottery.id')
            ->where('whitelabel_user_ticket.paid', '=', 1)
            ->and_where('whitelabel_user_ticket.date', '>=', $start)
            ->and_where('whitelabel_user_ticket.date', '<=', $end)
            ->group_by('whitelabel_user_ticket.lottery_id')
            ->order_by('amount', 'DESC')
            ->limit(10);

        if ($whitelabel_id) {
            $query->where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }

        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $analogical_start_date
     * @param string $end_date
     *
     * @return array
     */
    public static function get_top_seller_countries_for_crm($whitelabel_id, $start_date, $analogical_start_date, $end_date): array
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $start_analogical = Helpers_Crm_General::prepare_start_date($analogical_start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $amount = DB::expr('SUM(amount) AS amount');

        if ($whitelabel_id == 0) {
            $amount = DB::expr('SUM(amount_usd) AS amount');
        } elseif ($whitelabel_id > 0) {
            $amount = DB::expr('SUM(amount_manager) AS amount');
        }

        $analogical_period_query = DB::select(
            ['whitelabel_user.last_country', 'country'],
            $amount
        )
            ->from('whitelabel_user_ticket')
            ->join('whitelabel_user')->on('whitelabel_user_ticket.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->where('whitelabel_user_ticket.paid', '=', 1)
            ->and_where('whitelabel_user_ticket.date', '>=', $start_analogical)
            ->and_where('whitelabel_user_ticket.date', '<=', $start);
        if ($whitelabel_id) {
            $analogical_period_query->and_where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }
        $analogical_period_query->group_by('whitelabel_user.last_country');

        $period_query = DB::select(
            DB::expr('SUM(line_count) AS count'),
            ['whitelabel_user.last_country', 'country'],
            $amount
        )
            ->from('whitelabel_user_ticket')
            ->join('whitelabel_user')->on('whitelabel_user_ticket.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->where('whitelabel_user_ticket.paid', '=', 1)
            ->and_where('whitelabel_user_ticket.date', '>=', $start)
            ->and_where('whitelabel_user_ticket.date', '<=', $end);
        if ($whitelabel_id) {
            $period_query->and_where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }
        $period_query->group_by('whitelabel_user.last_country');

        $query = DB::select(
            't1.count',
            't1.country',
            ['t1.amount', 'amount'],
            ['t2.amount', 'previous_amount']
        )->from([$period_query, 't1'])
            ->join([$analogical_period_query, 't2'], 'LEFT')->on('t1.country', '=', 't2.country');

        $query->order_by('t1.amount', 'DESC')
            ->limit(10);

        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return array
     */
    public static function get_top_seller_languages_for_crm($whitelabel_id, $start_date, $end_date): array
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $amount = DB::expr('SUM(amount) AS amount');

        if ($whitelabel_id == 0) {
            $amount = DB::expr('SUM(amount_usd) AS amount');
        } elseif ($whitelabel_id > 0) {
            $amount = DB::expr('SUM(amount_manager) AS amount');
        }

        $query = DB::select(
            DB::expr('SUM(line_count) AS count'),
            $amount,
            'language.code'
        )
            ->from('whitelabel_user_ticket')
            ->join('whitelabel_user')->on('whitelabel_user_ticket.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('language')->on('whitelabel_user.language_id', '=', 'language.id')
            ->where('whitelabel_user_ticket.paid', '=', 1)
            ->and_where('whitelabel_user_ticket.date', '>=', $start)
            ->and_where('whitelabel_user_ticket.date', '<=', $end)
            ->group_by('whitelabel_user.language_id')
            ->order_by('count', 'DESC')
            ->limit(10);

        if ($whitelabel_id) {
            $query->where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }

        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return float
     */
    public static function get_ftp_tickets_amount_for_crm($whitelabel_id, $start_date, $end_date): float
    {
        $res = 0;

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('SUM(amount_usd) AS amount'))->from('whitelabel_user_ticket')
            ->join('whitelabel_user')->on('whitelabel_user_ticket.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->where('whitelabel_user_ticket.paid', '=', 1)
            ->and_where('whitelabel_user.first_purchase', '!=', null)
            ->and_where('whitelabel_user.second_purchase', '=', null)
            ->and_where('whitelabel_user_ticket.date', '>=', $start)
            ->and_where('whitelabel_user_ticket.date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (isset($result[0]['amount'])) {
            $res = $result[0]['amount'];
        }

        return $res;
    }

    /**
     *
     * @access public
     *
     * @param int    $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     *
     * @return float
     */
    public static function get_stp_tickets_amount_for_crm($whitelabel_id, $start_date, $end_date): float
    {
        $res = 0;

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('SUM(amount_usd) AS amount'))->from('whitelabel_user_ticket')
            ->join('whitelabel_user')->on('whitelabel_user_ticket.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->where('whitelabel_user_ticket.paid', '=', 1)
            ->and_where('whitelabel_user.second_purchase', '!=', null)
            ->and_where('whitelabel_user_ticket.date', '>=', $start)
            ->and_where('whitelabel_user_ticket.date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (isset($result[0]['amount'])) {
            $res = $result[0]['amount'];
        }

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public static function get_whitelabels_amount_income_date_range($whitelabel_id, $start_date, $end_date): array
    {
        $res = [];

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $amount = DB::expr('SUM(amount) AS amount');
        $income = DB::expr('SUM(income) AS income');

        if ($whitelabel_id == 0) {
            $amount = DB::expr('SUM(amount_usd) AS amount');
            $income = DB::expr('SUM(income_usd) AS income');
        } elseif ($whitelabel_id > 0) {
            $amount = DB::expr('SUM(amount_manager) AS amount');
            $income = DB::expr('SUM(income_manager) AS income');
        }

        $subquery_amount = DB::select(
            'whitelabel_id',
            $amount
        )
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end)
            ->group_by('whitelabel_id');

        $subquery_income = DB::select(
            'whitelabel_id',
            $income
        )
            ->from('whitelabel_transaction')
            ->where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end)
            ->group_by('whitelabel_id');

        $query = DB::select('whitelabel.name', 't.amount', 's.income')
            ->from('whitelabel')
            ->join([$subquery_amount, 't'])
            ->on('t.whitelabel_id', '=', 'whitelabel.id')
            ->join([$subquery_income, 's'])
            ->on('s.whitelabel_id', '=', 'whitelabel.id')
            ->order_by('t.amount', 'DESC');

        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @param int $multi_draw_id
     *
     * @return mixed
     */
    public static function get_processed_tickets_for_multidraw($multi_draw_id)
    {
        $columns = ['wut.id'];

        return DB::select_array($columns)
            ->from([self::$_table_name, 'wut'])
            ->join(['whitelabel', 'w'], 'LEFT')
            ->on('w.id', '=', 'wut.whitelabel_id')
            ->join(['whitelabel_user', 'wu'], 'LEFT')
            ->on('wu.id', '=', 'whitelabel_user_id')
            ->join(['whitelabel_user_ticket_slip', 'wuts'], 'LEFT')
            ->on('whitelabel_user_ticket_id', '=', 'wut.id')
            ->join(['lottorisq_ticket', 'lt'])
            ->on('lt.whitelabel_user_ticket_slip_id', '=', 'wuts.id')
            ->where('wut.multi_draw_id', '=', $multi_draw_id)
            ->where('wut.status', '=', Helpers_General::TICKET_STATUS_PENDING)
            ->execute();
    }

    /**
     * Fetch counted tickets nad sum of lines for given user ID.
     *
     * @param int $whitelabel_user_id User ID
     *
     * @return array
     */
    public static function get_count_tickets_and_lines_for_user(
        int $whitelabel_user_id = 0
    ): array
    {
        $params = [];

        $params[] = [":whitelabel_user_id", $whitelabel_user_id];

        $query_string = "SELECT 
            COUNT(*) AS counted_tickets, 
            COALESCE(SUM(`line_count`), 0) AS sum_lines 
        FROM `whitelabel_user_ticket` 
        WHERE `whitelabel_user_id` = :whitelabel_user_id 
            AND whitelabel_transaction_id IS NOT NULL 
            AND paid = " . Helpers_General::TICKET_PAID;

        // execute safe query
        $result = parent::execute_query($query_string, $params);

        // safely retrieve value
        return parent::get_array_result_row($result, [], 0);
    }

    /**
     *
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return array
     */
    public static function get_tickets_lotteries_for_crm($whitelabel_id, $start_date, $end_date): array
    {
        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(
            DB::expr('SUM(whitelabel_user_ticket.line_count) AS count'),
            DB::expr('SUM(whitelabel_user_ticket.amount_manager) AS amountManagerSum'),
            DB::expr('SUM(whitelabel_user_ticket.amount_usd) AS amountUsdSum'),
            'lottery.name'
        )
            ->from('whitelabel_user_ticket')
            ->join('lottery')->on('whitelabel_user_ticket.lottery_id', '=', 'lottery.id')
            ->where('whitelabel_user_ticket.paid', '=', 1)
            ->and_where('whitelabel_user_ticket.date', '>=', $start)
            ->and_where('whitelabel_user_ticket.date', '<=', $end)
            ->group_by('whitelabel_user_ticket.lottery_id')
            ->order_by('count', 'DESC')
            ->limit(10);

        if ($whitelabel_id) {
            $query->where('whitelabel_user_ticket.whitelabel_id', '=', $whitelabel_id);
        }

        /** @var object $query */
        $lotteries = $query->execute()->as_array();

        $response = [];
        foreach ($lotteries as $singleLottery) {
            $whitelabelRepository = Container::get(WhitelabelRepository::class);
            $whitelabel = $whitelabelRepository->findOneById($whitelabel_id);
            $amount = is_null($whitelabel_id) ? $singleLottery['amountUsdSum'] : $singleLottery['amountManagerSum'];
            $amountSumDisplay = Lotto_View::format_currency(
                $amount,
                $whitelabel->currency->code ?? 'USD', // $whitelabel doesn`t exists when super admin is logged in
                true
            );
            $singleLottery['amountSumDisplay'] = $amountSumDisplay;
            $response[] = $singleLottery;
        }

        return $response;
    }

    /**
     *
     * @access public
     * @param string $start_date
     * @param string $end_date
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_tickets_lines_count_for_crm($start_date, $end_date, $whitelabel_id)
    {
        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('SUM(line_count) AS count'), DB::expr('DATE(date) AS date'))
            ->from('whitelabel_user_ticket')
            ->where('paid', '=', 1)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $query->group_by(DB::expr('DATE(date)'));
        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $active_tab
     * @param array $filters
     * @param int $multidraw_id
     * @param int $page
     * @param int $items_per_page
     * @param string $sort_by
     * @param string $order
     * @return array
     */
    public static function get_full_data_for_crm(
        ?int $whitelabel_id,
        string $active_tab,
        array $filters,
        ?int $multidraw_id,
        ?int $page = null,
        ?int $items_per_page = null,
        ?string $sort_by = null,
        ?string $order = null,
        bool $is_cache_disabled = false
    ): array {
        $res = [];

        $query = DB::select(
            'wut.*',
            'whitelabel.prefix',
            ['wlcurr.code', 'manager_currency_code'],
            ['lcurr.code', 'lottery_currency_code'],
            ['ucurr.code', 'user_currency_code'],
            ['lottery.name', 'lname'],
            ['wt.token', 'ttoken'],
            ['wu.token', 'utoken'],
            ['multi_draw.token', 'mtoken'],
            'wu.name',
            'wu.surname',
            'wu.email',
            ['wu.login', 'user_login']
        )
            ->from(DB::expr('whitelabel_user_ticket AS wut FORCE INDEX (token_index)'))
            ->join('whitelabel')->on('wut.whitelabel_id', '=', 'whitelabel.id')
            ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('wut.whitelabel_transaction_id', '=', 'wt.id')
            ->join(['whitelabel_user', 'wu'], 'LEFT')->on('wut.whitelabel_user_id', '=', 'wu.id')
            ->join('lottery')->on('wut.lottery_id', '=', 'lottery.id')
            ->join('multi_draw', 'LEFT')->on('multi_draw.id', '=', 'wut.multi_draw_id')
            ->join(['currency', 'lcurr'])->on('lottery.currency_id', '=', 'lcurr.id')
            ->join(['currency', 'ucurr'])->on('wu.currency_id', '=', 'ucurr.id')
            ->join(['currency', 'wlcurr'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'wlcurr.id')
            ->where('wut.paid', '=', Helpers_General::TICKET_PAID);

        switch ($active_tab) {
            case 'pending':
                $query->and_where('wut.status', '=', Helpers_General::TICKET_STATUS_PENDING);
                break;
            case 'win':
                $query->and_where('wut.status', '=', Helpers_General::TICKET_STATUS_WIN);
                break;
            case 'nowinnings':
                $query->and_where('wut.status', '=', Helpers_General::TICKET_STATUS_NO_WINNINGS);
                break;
            case 'canceled':
                $query->and_where('wut.status', '=', Helpers_General::TICKET_STATUS_CANCELED);
                break;
        }

        if ($whitelabel_id) {
            $query->and_where('wut.whitelabel_id', '=', $whitelabel_id);
        }

        if ($multidraw_id) {
            $query->and_where('multi_draw.token', '=', $multidraw_id);
        }

        $query = self::prepare_filters($filters, $query, $whitelabel_id);

        if ($sort_by) {
            $query->order_by($sort_by, $order);
        }

        if ($items_per_page) {
            $query->limit($items_per_page)->offset($items_per_page * ($page - 1));
        }

        if ($is_cache_disabled) {
            $query->caching(false);
        }

        /** @var object $query */
        $res = $query->execute()->as_array();

        return $res;
    }

    /**
     * Mass update tickets for specified user ids.
     * Update their payout_prize_percent.
     *
     * @param int[] $user_ids
     * @param float $payout_percent
     * @return int result of update (how many rows were updated)
     */
    public static function update_payout_prize_percent_for_users(array $user_ids, float $payout_percent): int
    {
        return DB::update(self::$_table_name)
            ->value('prize_payout_percent', $payout_percent)
            ->where('whitelabel_user_id', 'IN', $user_ids)
            ->execute();
    }

    /**
     *
     * @param array $filters
     * @param Database_Query_Builder_Select $query
     * @param int $whitelabel
     * @return Database_Query_Builder_Select
     */
    private static function prepare_filters($filters, $query, $whitelabel)
    {
        foreach ($filters as $filter) {
            if ($filter['column'] === 'token') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where_open();
                $query->where('wut.token', 'LIKE', $value)
                    ->or_where('wt.token', 'LIKE', $value)
                    ->or_where('lottery.name', 'LIKE', $value);
                $query->and_where_close();
            }
            if ($filter['column'] === 'utoken') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where_open();
                $query->where('wu.name', 'LIKE', $value)
                    ->or_where('wu.login', 'LIKE', $value)
                    ->or_where('wu.token', 'LIKE', $value)
                    ->or_where('wu.surname', 'LIKE', $value)
                    ->or_where('wu.email', 'LIKE', $value);
                $query->and_where_close();
            }
            if ($filter['column'] === 'status') {
                $query->and_where('wut.status', '=', intval($filter['value']));
            }
            if ($filter['column'] === 'amount') {
                $amount = 'wut.amount_usd';
                if ($whitelabel) {
                    $amount = 'wut.amount_manager';
                }
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where($amount, '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where($amount, '>=', intval($filter['start']))
                        ->and_where($amount, '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where($amount, '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'bonus_amount') {
                $amount = 'wut.bonus_amount_usd';
                if ($whitelabel) {
                    $amount = 'wut.bonus_amount_manager';
                }
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where($amount, '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where($amount, '>=', intval($filter['start']))
                        ->and_where($amount, '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where($amount, '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'prize') {
                $prize = 'wut.prize_usd';
                if ($whitelabel) {
                    $prize = 'wut.prize_manager';
                }
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where($prize, '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where($prize, '>=', intval($filter['start']))
                        ->and_where($prize, '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where($prize, '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'prize_payout_percent') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('wut.prize_payout_percent', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('wut.prize_payout_percent', '>=', intval($filter['start']))
                        ->and_where('wut.prize_payout_percent', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('wut.prize_payout_percent', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('wut.date', '>=', $start);
                $query->and_where('wut.date', '<=', $end);
            }
            if ($filter['column'] == 'draw_date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('wut.draw_date', '>=', $start);
                $query->and_where('wut.draw_date', '<=', $end);
            }
            if ($filter['column'] === 'payout') {
                $query->and_where('wut.payout', '=', intval($filter['value']))
                    ->and_where('wut.status', '=', Helpers_General::TICKET_STATUS_WIN);
            }
            if ($filter['column'] === 'line_count') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('wut.line_count', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('wut.line_count', '>=', intval($filter['start']))
                        ->and_where('wut.line_count', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('wut.line_count', '<=', intval($filter['end']));
                }
            }
        }

        return $query;
    }

    /**
     *
     * @param int $whitelabel_id
     * @param array $filters
     * @param int $multidraw_id
     * @param int $status
     * @return int
     */
    public static function get_tickets_counts_for_crm(?int $whitelabel_id, array $filters, ?int $multidraw_id, ?int $status): int
    {
        $res = 0;

        $query = DB::select(
            DB::expr('COUNT(*) as count')
        )
            ->from(DB::expr('whitelabel_user_ticket AS wut FORCE INDEX (whitelabel_user_ticket_w_id_paid_id_tid_uid_s_p_lid_draw_idmx)'));

        $areFilters = !empty($filters);
        if ($areFilters) {
            $query->join('whitelabel')->on('wut.whitelabel_id', '=', 'whitelabel.id')
                ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('wut.whitelabel_transaction_id', '=', 'wt.id')
                ->join('lottery')->on('wut.lottery_id', '=', 'lottery.id')
                ->join(['currency', 'lcurr'])->on('lottery.currency_id', '=', 'lcurr.id')
                ->join(['currency', 'wlcurr'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'wlcurr.id')
                ->join(['whitelabel_user', 'wu'], 'LEFT')->on('wut.whitelabel_user_id', '=', 'wu.id');
        }

        $query->where('wut.paid', '=', Helpers_General::TICKET_PAID);

        if (isset($status)) {
            $query->and_where('wut.status', '=', $status);
        }

        if (isset($whitelabel_id)) {
            $query->and_where('wut.whitelabel_id', '=', $whitelabel_id);
        }

        if ($multidraw_id) {
            $query->and_where('wut.multi_draw_id', '=', $multidraw_id);
        }

        $query = self::prepare_filters($filters, $query, $whitelabel_id);

        /** @var object $query */
        $result = $query->execute()->as_array();
        if (isset($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    /**
     *
     * @param string $token
     * @return array
     */
    public static function get_single_for_crm(string $token, int $whitelabelId): array
    {
        $ticket = [];

        $query = DB::select(
            'wut.*',
            'whitelabel.prefix',
            ['multi_draw.tickets', 'multi_draw_tickets'],
            ['wlcurr.code', 'manager_currency_code'],
            ['lcurr.code', 'lottery_currency_code'],
            ['ucurr.code', 'user_currency_code'],
            ['lottery.name', 'lname'],
            ['wt.token', 'ttoken'],
            ['wu.token', 'utoken'],
            'wu.name',
            'wu.surname',
            'wu.email',
            ['wu.login', 'user_login']
        )
            ->from(['whitelabel_user_ticket', 'wut'])
            ->join('whitelabel')->on('wut.whitelabel_id', '=', 'whitelabel.id')
            ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('wut.whitelabel_transaction_id', '=', 'wt.id')
            ->join(['whitelabel_user', 'wu'], 'LEFT')->on('wut.whitelabel_user_id', '=', 'wu.id')
            ->join('lottery')->on('wut.lottery_id', '=', 'lottery.id')
            ->join(['currency', 'lcurr'])->on('lottery.currency_id', '=', 'lcurr.id')
            ->join(['currency', 'ucurr'])->on('wu.currency_id', '=', 'ucurr.id')
            ->join(['currency', 'wlcurr'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'wlcurr.id')
            ->join('multi_draw', 'LEFT')->on('multi_draw.id', '=', 'wut.multi_draw_id')
            ->where('wut.token', '=', $token)
            ->and_where('whitelabel.id', '=', $whitelabelId);

        /** @var object $query */
        $result = $query->execute()->as_array();

        if (isset($result[0])) {
            $ticket = $result[0];
        }

        return $ticket;
    }

    public static function find_pending_for_draw(Model_Lottery_Draw $draw): ?array
    {
        return static::find([
            'where' => [
                'lottery_id' => $draw['lottery_id'],
                'status' => Helpers_General::TICKET_STATUS_PENDING,
                'draw_date' => $draw['date_local']
            ]
        ]);
    }
}
