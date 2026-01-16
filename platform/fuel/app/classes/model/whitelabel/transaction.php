<?php

use Mautic\Api\Data;
use Models\WhitelabelTransaction;
use Fuel\Core\Database_Query_Builder;
use Fuel\Core\Database_Query_Builder_Where;
use Fuel\Core\Database_Query_Builder_Select;
use Helpers\TransactionHelper;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;

/**
 *@property-read int $id
 *@property-read mixed $date_confirmed
 */
class Model_Whitelabel_Transaction extends Model_Model
{
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_transaction';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @param array $whitelabel
     */
    public function __construct($whitelabel = [])
    {
        parent::__construct();
        $this->whitelabel = $whitelabel;
    }

    public static function fromOrm(WhitelabelTransaction $transaction): self
    {
        return self::get_transaction_for_prefixed_token($transaction->prefixed_token, $transaction->whitelabel_id);
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }
    
    /**
     * @param int $type
     * @param array $whitelabel
     * @return null|int
     */
    public static function count_for_whitelabel($type, $whitelabel = []):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        
        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_transaction 
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_transaction.whitelabel_id = :whitelabel_id";
        }

        $query .= " AND whitelabel_transaction.type = ".$type;
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            $res = $db->execute()->as_array();
            if (is_null($res)) {
                return -1;
            }
            if (empty($res[0])) {
                return -1;
            }
            $result = $res[0]['count'];
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }
    
    /**
     *
     * @param array $user
     * @return array
     */
    public static function get_full_data_for_user_rodo($user):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        
        $query = "SELECT 
                (CASE WHEN whitelabel_transaction.type = 0 
                    THEN CONCAT(whitelabel.prefix, 'P', whitelabel_transaction.token)
                    ELSE CONCAT(whitelabel.prefix, 'D', whitelabel_transaction.token)
                END) AS trans_prefix_token,
                (CASE WHEN whitelabel_transaction.payment_method_type = 1 
                        THEN 'Balance' 
                    WHEN whitelabel_transaction.payment_method_type = 2 
                        THEN 'Credit Card'
                    WHEN whitelabel_transaction.payment_method_type = 3 
                        THEN whitelabel_payment_method.name END
                ) AS method_name,
                currency.code AS c_code,
                whitelabel_transaction.*
            FROM whitelabel_transaction 
            INNER JOIN whitelabel 
                ON whitelabel_transaction.whitelabel_id = whitelabel.id 
            LEFT JOIN whitelabel_payment_method 
                ON whitelabel_transaction.whitelabel_payment_method_id = whitelabel_payment_method.id 
            
            LEFT JOIN currency 
                ON whitelabel_transaction.currency_id = currency.id 
            WHERE 1=1 ";
        
        if (!empty($user) && !empty($user["id"])) {
            $query .= " AND whitelabel_transaction.whitelabel_user_id = :user_id";
        }
        
        $query .= " ORDER BY whitelabel_transaction.id";

        try {
            $db = DB::query($query);
            
            if (!empty($user) && !empty($user["id"])) {
                $db->param(":user_id", $user["id"]);
            }
            
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
     * @param array|null $whitelabel
     * @param int $payment_type
     * @param int $payment_method_id
     * @return array
     */
    public static function get_unfinished_or_with_error(
        array $whitelabel = null,
        int $payment_type = Helpers_General::PAYMENT_TYPE_OTHER,
        int $payment_method_id = Helpers_Payment_Method::NETELLER
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        
        $query = "SELECT 
                wt.id, 
                wt.token, 
                wt.additional_data, 
                wt.whitelabel_id, 
                w.prefix, 
                wt.type, 
                wpm.data,
                wt.whitelabel_payment_method_id 
            FROM whitelabel_transaction wt 
            LEFT JOIN whitelabel w ON w.id = wt.whitelabel_id 
            LEFT JOIN whitelabel_payment_method wpm ON wpm.id = wt.whitelabel_payment_method_id 
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND wt.whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($payment_type)) {
            $query .= " AND wt.payment_method_type = :payment_type";
        }
        
        if (!empty($payment_method_id)) {
            $query .= " AND wpm.payment_method_id = :payment_method_id";
        }
        
        $query .= " AND wt.date_confirmed IS NULL";
        
        $query .= " AND wt.status IN (";
        $query .= Helpers_General::STATUS_TRANSACTION_PENDING . ", ";
        $query .= Helpers_General::STATUS_TRANSACTION_ERROR . ")";
        
        $query .= " AND wt.date >= DATE_SUB(NOW(), INTERVAL 1 DAY)";

        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($payment_type)) {
                $db->param(":payment_type", $payment_type);
            }
            
            if (!empty($payment_method_id)) {
                $db->param(":payment_method_id", $payment_method_id);
            }
            
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
     * @param array $whitelabel
     * @param array $user
     * @param int|null $status
     * @return null|int
     */
    public static function get_count_filtered_for_user_and_whitelabel(
        $whitelabel,
        $user,
        $status = null
    ):? int {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        
        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_transaction
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id";
        }
            
        if (!empty($user) && !empty($user["id"])) {
            $query .= " AND whitelabel_user_id = :user_id";
        }
        
        if (!empty($status)) {
            $query .= " AND status = :status";
        }
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($user) && !empty($user["id"])) {
                $db->param(":user_id", $user['id']);
            }

            if (!empty($status)) {
                $db->param(":status", $status);
            }
            
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null || count($res) == 0) {
            return $result;
        }
        
        $result = $res[0]['count'];
        
        return $result;
    }
        public static function get_filtered_data_for_user_and_whitelabel(
        array $whitelabel,
        array $user,
        int $status = null,
        array $sort = [],
        int $offset = 0,
        int $limit = 10,
        bool $getOnlyDeposits = false
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $isCasino = (int)!empty(IS_CASINO);
        $query = "SELECT 
            whitelabel_transaction.* 
        FROM whitelabel_transaction
        WHERE is_casino = $isCasino";
        
        if ($getOnlyDeposits){
            $query .= " AND type = 1";
        }

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id ";
        }
            
        if (!empty($user) && !empty($user["id"])) {
            $query .= " AND whitelabel_user_id = :user_id ";
        }
        
        $transaction_statuses_data = TransactionHelper::getStatuses();
        $transaction_statuses = array_keys($transaction_statuses_data);
        
        if ($status !== null && in_array($status, $transaction_statuses)) {
            $query .= " AND status = :status ";
        }
        
        if (!empty($sort) && !empty($sort["db"])) {
            $query .= " ORDER BY :order ";
        }
        
        $query .= "LIMIT :offset, :limit";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($user) && !empty($user["id"])) {
                $db->param(":user_id", $user['id']);
            }

            if ($status !== null && in_array($status, $transaction_statuses)) {
                $db->param(":status", $status);
            }
            
            if (!empty($sort) && !empty($sort["db"])) {
                $db->param(":order", DB::expr($sort['db']));
            }
            
            $db->param(":offset", $offset);
            $db->param(":limit", $limit);
            
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
     * @param array $whitelabel
     * @param int $type
     * @param array $params
     * @param string $filter_add
     * @return null|array
     */
    public static function get_count_filtered_for_whitelabel_by_type(
        $whitelabel,
        $type,
        $params = [],
        $filter_add = ""
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_transaction
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_transaction.whitelabel_id = :whitelabel_id";
        }
            
        if (!is_null($type)) {
            $query .= " AND whitelabel_transaction.type = :type";
        }
        
        $query .= " " . $filter_add;
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!is_null($type)) {
                $db->param(":type", $type);
            }
            
            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }
            
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($res === null || count($res) == 0) {
            return $result;
        }
        
        $result = $res[0];
        
        return $result;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param array $pagination
     * @param array $sort
     * @param array $params
     * @param int $type
     * @param string $filter_add
     * @return array
     */
    public static function get_full_data_for_whitelabel_by_type(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $type,
        $filter_add = ""
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            whitelabel_transaction.*, 
            transaction_currency.code AS transaction_currency_code,
            payment_currency.code AS payment_currency_code,
            manager_currency.code AS manager_currency_code,
            whitelabel_user.id AS uid, 
            whitelabel_user.token AS utoken, 
            whitelabel_user.is_deleted, 
            whitelabel_user.is_active, 
            whitelabel_user.is_confirmed, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user.email,
            whitelabel_user.login AS user_login,
            COUNT(whitelabel_user_ticket.id) AS count,
            (
                SELECT COUNT(*) 
                FROM whitelabel_user_ticket wut 
                WHERE wut.whitelabel_transaction_id = whitelabel_transaction.id 
                AND wut.date_processed IS NOT NULL
            ) AS count_processed
        FROM whitelabel_transaction
        FORCE INDEX (whitelabel_transaction_w_id_type_id_wu_id_status_pm_wpm_idmx)
        INNER JOIN whitelabel ON whitelabel_transaction.whitelabel_id = whitelabel.id 
        LEFT JOIN currency transaction_currency ON whitelabel_transaction.currency_id = transaction_currency.id 
        LEFT JOIN currency payment_currency ON whitelabel_transaction.payment_currency_id = payment_currency.id 
        LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id
        LEFT JOIN whitelabel_user_ticket ON whitelabel_user_ticket.whitelabel_transaction_id = whitelabel_transaction.id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_transaction.whitelabel_id = :whitelabel_id ";
        }
        
        if (!is_null($type)) {
            $query .= " AND whitelabel_transaction.type = :type ";
        }
        
        $query .= " " . $filter_add;
        
        $query .= " GROUP BY whitelabel_transaction.id ";
        
        if (!empty($sort) && !empty($sort["db"])) {
            $query .= " ORDER BY :order ";
        }
        
        $query .= " LIMIT :offset, :limit";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!is_null($type)) {
                $db->param(":type", $type);
            }
            
            /** @var object $pagination */
            $db->param(":offset", $pagination->offset);
            $db->param(":limit", $pagination->per_page);
            
            if (!empty($sort) && !empty($sort["db"])) {
                $db->param(":order", DB::expr($sort["db"]));
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }
            
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
     * @param array $whitelabel
     * @param string $token
     * @return null|array
     */
    public static function get_single_for_whitelabel_by_token(
        array $whitelabel,
        string $token
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query_result = null;
        
        if (empty($whitelabel) || empty($token)) {
            return $result;
        }
        
        $query = "SELECT 
            whitelabel_transaction.*,              
            transaction_currency.code AS transaction_currency_code,
            payment_currency.code AS payment_currency_code,
            manager_currency.code AS manager_currency_code
        FROM whitelabel_transaction 
        INNER JOIN whitelabel ON whitelabel_transaction.whitelabel_id = whitelabel.id 
        LEFT JOIN currency transaction_currency ON whitelabel_transaction.currency_id = transaction_currency.id 
        LEFT JOIN currency payment_currency ON whitelabel_transaction.payment_currency_id = payment_currency.id 
        LEFT JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND whitelabel_transaction.token = :token LIMIT 1";

        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            $db->param(":token", $token);

            $query_result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if ($query_result === null || count($query_result) == 0) {
            return $result;
        }
        
        $result = $query_result[0];
        
        return $result;
    }
    
    /**
     * This function get sum of amount values in USD of the approved transactions
     * made by credit card for the user
     *
     * @param array $whitelabel
     * @param array $user
     * @return float
     */
    public static function get_sum_in_USD($whitelabel, $user):? float
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = 0;
        $res = null;
        $query = "SELECT 
                COALESCE(SUM(amount_usd), 0) AS amount_usd_sum 
            FROM whitelabel_transaction 
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($user) && !empty($user['id'])) {
            $query .= " AND whitelabel_user_id = :user_id";
        }
        
        $query .= " AND status = " . Helpers_General::STATUS_TRANSACTION_APPROVED;
        $query .= " AND payment_method_type = " . Helpers_General::PAYMENT_TYPE_CC;
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($user) && !empty($user['id'])) {
                $db->param(":user_id", $user['id']);
            }
            
            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        if (is_null($res) || is_null($res[0])) {
            return $result;
        }

        $result = $res[0]['amount_usd_sum'];
            
        return $result;
    }
    
    /**
     * Fetch sum of approved transaction of deposits filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @return array consists of the sum of approved deposits for specified whitelabel.
     */
    public static function get_sum_deposits_for_reports(
        string $add,
        array $params,
        int $whitelabel_id
    ):? array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        
        $query_string = "SELECT 
            COALESCE(SUM(amount), 0) AS sum,
            COALESCE(SUM(amount_manager), 0) AS sum_manager  
        FROM whitelabel_transaction 
        JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        WHERE whitelabel_transaction.whitelabel_id = :whitelabel_id  
            AND type = " . Helpers_General::TYPE_TRANSACTION_DEPOSIT . "
            AND status = " . Helpers_General::STATUS_TRANSACTION_APPROVED .
            " " . $add;
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch sum of approved transaction of deposits filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_type whitelabel type could be null
     * @param int $whitelabel_id whitelabel id could be null
     * @param bool $is_full_report For full report noting changed
     * @return array consists of the sum of approved deposits
     */
    public static function get_sum_deposits_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ):? array {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }
        
        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }
        
        $query_string = "SELECT 
            COALESCE(SUM(amount), 0.00) AS sum,
            COALESCE(SUM(amount_manager), 0.00) AS sum_manager,
            COALESCE(SUM(amount_usd), 0.00) AS sum_usd 
        FROM whitelabel_transaction 
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";
        
        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }
        
        $query_string .= "WHERE whitelabel_transaction.type = " .
            Helpers_General::TYPE_TRANSACTION_DEPOSIT . "
            AND whitelabel_transaction.status = " .
            Helpers_General::STATUS_TRANSACTION_APPROVED . " ";
        
        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel.id = :whitelabel_id ";
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
     * Fetch sum of approved transaction of deposits grouped by currency filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @return array consists of the sum of approved deposits for specified whitelabel.
     */
    public static function get_sum_deposits_by_currency_for_reports(
        string $add,
        array $params,
        int $whitelabel_id
    ):? array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        
        $query_string = "SELECT 
            whitelabel_transaction.currency_id, 
            COALESCE(SUM(amount), 0) AS sum,
            COALESCE(SUM(amount_manager), 0) AS sum_manager 
        FROM whitelabel_transaction 
        JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        WHERE whitelabel_transaction.whitelabel_id = :whitelabel_id  
            AND type = " . Helpers_General::TYPE_TRANSACTION_DEPOSIT . "
            AND status = " . Helpers_General::STATUS_TRANSACTION_APPROVED .
            " " . $add . "
            GROUP BY whitelabel_transaction.currency_id";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch sum of approved transaction of deposits grouped by currency filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_type whitelabel type could be null
     * @param int $whitelabel_id whitelabel id could be null
     * @param bool $is_full_report For full report noting changed
     * @return array consists of the sum of approved deposits for specified whitelabel.
     */
    public static function get_sum_deposits_by_currency_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ):? array {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }
        
        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }
        
        $query_string = "SELECT 
            whitelabel_transaction.currency_id, 
            COALESCE(SUM(amount), 0.00) AS sum,
            COALESCE(SUM(amount_manager), 0.00) AS sum_manager,
            COALESCE(SUM(amount_usd), 0.00) AS sum_usd 
        FROM whitelabel_transaction 
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";
        
        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }
        
        $query_string .= "WHERE whitelabel_transaction.type = " .
            Helpers_General::TYPE_TRANSACTION_DEPOSIT . "
            AND whitelabel_transaction.status = " .
            Helpers_General::STATUS_TRANSACTION_APPROVED . " ";
        
        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel.id = :whitelabel_id ";
        }
        
        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }
        
        $query_string .= $add . " ";
        
        $query_string .= "GROUP BY whitelabel_transaction.currency_id";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch sums of approved transaction of sales filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @return array consists of the sums of approved sales for specified whitelabel.
     */
    public static function get_sums_sales_for_reports(
        string $add,
        array $params,
        int $whitelabel_id
    ):? array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];

        $filters = "AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id" . $add;

        $marginManagerQuery = self::getSumsMarginManagerQuery($filters);

        $query_string = "SELECT 
            SUM(amount_manager) AS amount_manager,
            SUM(income_manager) AS income_manager,
            SUM(cost_manager) AS cost_manager,
            " . $marginManagerQuery . " AS margin_manager,
            SUM(payment_cost_manager) AS payment_cost_manager 
        FROM whitelabel_transaction 
        JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        WHERE whitelabel_transaction.whitelabel_id = :whitelabel_id  
            AND type = " . Helpers_General::TYPE_TRANSACTION_PURCHASE . "
            AND status = " . Helpers_General::STATUS_TRANSACTION_APPROVED .
            " " . $add;
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch sums of approved transaction of sales filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_type whitelabel type could be null
     * @param int $whitelabel_id whitelabel id could be null
     * @param bool $is_full_report For full report noting changed
     * @return array consists of the sums of approved sales for specified whitelabel.
     */
    public static function get_sums_sales_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ):? array {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }

        $filters = $add;

        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
            $filters = "AND whitelabel_user_ticket.whitelabel_id = :whitelabel_id" . $add;
        }

        $marginManagerQuery = self::getSumsMarginManagerQuery($filters);

        $queryString = "SELECT 
            COALESCE(SUM(amount_manager), 0.00) AS amount_manager,
            COALESCE(SUM(amount_usd), 0.00) AS amount_usd,
            COALESCE(SUM(income_manager), 0.00) AS income_manager,
            COALESCE(SUM(income_usd), 0.00) AS income_usd,
            COALESCE(SUM(cost_manager), 0.00) AS cost_manager,
            COALESCE(SUM(cost_usd), 0.00) AS cost_usd,
            " . $marginManagerQuery . " AS margin_manager,
            COALESCE(SUM(margin_usd), 0.00) AS margin_usd,
            COALESCE(SUM(payment_cost_manager), 0.00) AS payment_cost_manager,
            COALESCE(SUM(payment_cost_usd), 0.00) AS payment_cost_usd 
        FROM whitelabel_transaction FORCE INDEX (whitelabel_transaction_w_id_w_user_id_date_idmx)
        INNER JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";

        if (!empty($whitelabel_type)) {
            $queryString .= " AND whitelabel.type = :whitelabel_type ";
        }
        
        $queryString .= "WHERE whitelabel_transaction.type = " .
            Helpers_General::TYPE_TRANSACTION_PURCHASE . "
            AND whitelabel_transaction.status = " .
            Helpers_General::STATUS_TRANSACTION_APPROVED . " ";
        
        if (!empty($whitelabel_id)) {
            $queryString .= " AND whitelabel_transaction.whitelabel_id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $queryString .= " AND whitelabel.is_report = 1 ";
        }

        $queryString .= $add;

        // execute safe query
        $result = parent::execute_query($queryString, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     *
     * @param string $prefixed_token
     * @param int $whitelabel_id numeric id of the whitelabel, use (int) in passing to make sure that integer is passed.
     * @return Model_Whitelabel_Transaction|null null on failure, array containing transaction on success.
     */
    public static function get_transaction_for_prefixed_token(
        string $prefixed_token,
        int $whitelabel_id
    ):? Model_Whitelabel_Transaction {
        // remove prefix from token, cast it to int.
        $token_int = (int)substr($prefixed_token, 3);
        // fetch transaction from database.
        $transactions = Model_Whitelabel_Transaction::find(
            [
                "where" => [
                    "whitelabel_id" => $whitelabel_id,
                    "token" => $token_int
                ]
            ]
        );

        // return transaction or null on failure
        return $transactions[0] ?? null;
    }

    public static function get_transaction_for_prefixed_token_and_gateway_transaction_id(
        string $prefixed_token,
        string $transactionGatewayId
    ):? Model_Whitelabel_Transaction {
        // remove prefix from token, cast it to int.
        $token_int = (int)substr($prefixed_token, 3);
        if (empty($transactionGatewayId)) {
            throw new Exception('Please fix sender to persist gateway transaction ID, so we can find transaction for correct whitelabel');
        }
        // fetch transaction from database.
        $transactions = Model_Whitelabel_Transaction::find(
            [
                "where" => [
                    "token" => $token_int,
                    "transaction_out_id" => $transactionGatewayId
                ]
            ]
        );

        // return transaction or null on failure
        return $transactions[0] ?? null;
    }
    
    /**
     * Fetch count of user tickets filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_type whitelabel type could be null
     * @param int $whitelabel_id whitelabel id could be null
     * @param bool $is_full_report For full report noting changed
     * @return int count of paid tickets
     */
    public static function get_counted_deposits_for_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ): int {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }
        
        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }
        
        $query_string = "SELECT 
            COUNT(*) as count 
        FROM whitelabel_transaction 
        INNER JOIN whitelabel_user ON whitelabel_transaction.whitelabel_user_id = whitelabel_user.id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";
        
        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }
        
        $query_string .= "WHERE whitelabel_transaction.status = " .
                Helpers_General::STATUS_TRANSACTION_APPROVED . " 
            AND whitelabel_transaction.type = " .
                Helpers_General::TYPE_TRANSACTION_DEPOSIT . " ";
        
        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel.id = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }
        
        $query_string .= $add;
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
        
    /**
     *
     * @param int $whitelabel_id
     * @param int $user_id
     * @param int $type
     * @return int
     */
    public static function get_count_for_user_by_type(
        int $whitelabel_id,
        int $user_id,
        int $type
    ): int {
        $params = [];
        
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":user_id", $user_id];
        $params[] = [":type", $type];
        
        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_transaction 
        WHERE whitelabel_transaction.whitelabel_id = :whitelabel_id 
            AND whitelabel_transaction.whitelabel_user_id = :user_id 
            AND whitelabel_transaction.type = :type 
            AND whitelabel_transaction.status = " . Helpers_General::STATUS_TRANSACTION_APPROVED;
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     *
     * @param int $whitelabel_id
     * @param int $user_id
     * @return array|null
     */
    public static function get_last_purchase_date(
        int $whitelabel_id,
        int $user_id
    ):? array {
        $params = [];
        
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":user_id", $user_id];
        
        $query_string = "SELECT 
            whitelabel_transaction.date 
        FROM whitelabel_transaction 
        WHERE whitelabel_transaction.whitelabel_id = :whitelabel_id 
            AND whitelabel_transaction.whitelabel_user_id = :user_id 
            AND whitelabel_transaction.type = " .
                Helpers_General::TYPE_TRANSACTION_PURCHASE . "
            AND whitelabel_transaction.status = " .
                Helpers_General::STATUS_TRANSACTION_APPROVED . "
        ORDER BY whitelabel_transaction.date DESC 
        LIMIT 1";
       
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_deposits_count_for_crm_last_seven_days($whitelabel_id): array
    {
        $res = [];

        $query = DB::select(DB::expr('COUNT(*) AS count'), DB::expr('DATE(date) AS date'))->from('whitelabel_transaction')
        ->where('type', '=', Helpers_General::TYPE_TRANSACTION_DEPOSIT)
        ->and_where('status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
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
     * @param int $whitelabel_id
     * @param string $start_date
     * @param string $end_date
     * @return int
     */
    public static function get_deposits_count_for_crm_date_range($whitelabel_id, $start_date, $end_date): int
    {
        $res = 0;

        $start = Helpers_Crm_General::prepare_start_date($start_date);
        $end = Helpers_Crm_General::prepare_end_date($end_date);

        $query = DB::select(DB::expr('COUNT(*) as count'))->from('whitelabel_transaction')
        ->where('type', '=', 1)
        ->and_where('status', '=', 1)
        ->and_where('date', '>=', $start)
        ->and_where('date', '<=', $end);

        if ($whitelabel_id) {
            $query->and_where('whitelabel_id', '=', $whitelabel_id);
        }

        $result = $query->execute();
        if (!empty($result[0]['count'])) {
            $res = $result[0]['count'];
        }
        
        return $res;
    }

    public static function get_transactions_count_for_crm_last_month(
        ?int $whitelabelId,
        bool $isCasino,
        bool $isDeposit
    ): array {
        $query = DB::select('status', DB::expr('COUNT(*) AS count'))->from('whitelabel_transaction')
            ->where('date', '>=', DB::expr(DB::expr('DATE(NOW()) - INTERVAL 30 DAY')))
            ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        self::addConditionIfDeposit($query, $isDeposit);

        $query->group_by('status');
        return $query->execute()->as_array();
    }

    public static function get_transactions_pending_for_crm_date_range(
        ?int $whitelabelId,
        string $startDate,
        string $endDate,
        bool $isCasino,
        bool $isDeposit
    ): array
    {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = DB::select(DB::expr('sum(amount_manager) as amountManagerSum'), DB::expr('sum(amount_usd) as amountUsdSum'), DB::expr('date(date) as date'), DB::expr('COUNT(*) AS count'))->from('whitelabel_transaction')
            ->where('status', 0)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end)
            ->and_where('is_casino', '=', $isCasino);

        self::addConditionIfDeposit($query, $isDeposit);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $query->group_by(DB::expr('date(date)'));

        $response['groupedTransactionsByDatePerMonth'] = $query->execute()->as_array();

        self::addAdditionalDataWithAmountSumDisplayAndCountToResponse($response, $whitelabelId);

        return $response;
    }

    public static function get_transactions_approved_for_crm_date_range(
        ?int $whitelabelId,
        string $startDate,
        string $endDate,
        bool $isCasino,
        bool $isDeposit
    ): array {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = DB::select(DB::expr('sum(amount_manager) as amountManagerSum'), DB::expr('sum(amount_usd) as amountUsdSum'), DB::expr('date(date) as date'), DB::expr('COUNT(*) AS count'))->from('whitelabel_transaction')
            ->where('status', 1)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end)
            ->and_where('is_casino', '=', $isCasino);

        self::addConditionIfDeposit($query, $isDeposit);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $query->group_by(DB::expr('date(date)'));

        $response['groupedTransactionsByDatePerMonth'] = $query->execute()->as_array();
        self::addAdditionalDataWithAmountSumDisplayAndCountToResponse($response, $whitelabelId);

        return $response;
    }

    public static function get_transactions_error_for_crm_date_range(
        ?int $whitelabelId,
        string $startDate,
        string $endDate,
        bool $isCasino,
        bool $isDeposit
    ): array {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = DB::select(DB::expr('sum(amount_manager) as amountManagerSum'), DB::expr('sum(amount_usd) as amountUsdSum'), DB::expr('date(date) as date'), DB::expr('COUNT(*) AS count'))->from('whitelabel_transaction')
            ->where('status', 2)
            ->and_where('date', '>=', $start)
            ->and_where('date', '<=', $end)
            ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        self::addConditionIfDeposit($query, $isDeposit);

        $query->group_by(DB::expr('date(date)'));

        $response['groupedTransactionsByDatePerMonth'] = $query->execute()->as_array();

        self::addAdditionalDataWithAmountSumDisplayAndCountToResponse($response, $whitelabelId);

        return $response;
    }

    public static function get_transactions_data_for_crm(
        ?int $whitelabel_id,
        string $active_tab,
        array $filters = [],
        ?int $page = null,
        ?int $items_per_page = null,
        ?string $sort_by = null,
        ?string $order = null,
        bool $is_cache_disabled = false,
        bool $isCasino = false,
        bool $isDeposit = false
    ): array {
        $query = DB::select(
            'whitelabel_transaction.*',
            ['whitelabel.prefix','whitelabel_prefix'],
            ['currency.code','whitelabel_currency_code'],
            ['whitelabel_user.token', 'user_token'],
            ['whitelabel_user.name', 'user_name'],
            ['whitelabel_user.surname', 'user_surname'],
            ['whitelabel_user.email', 'user_email'],
            ['whitelabel_user.login', 'user_login'],
            ['payment_method.name', 'method'],
            [DB::expr(self::getTicketsCountPerTransactionQuery()), 'tickets_count'],
            [DB::expr(self::getTicketsProcessedCountPerTransactionQuery()), 'tickets_processed_count'],
        )
            ->from('whitelabel_transaction')
            ->join('whitelabel')->on('whitelabel_transaction.whitelabel_id', '=', 'whitelabel.id')
            ->join('currency', 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'currency.id')
            ->join('whitelabel_user', 'LEFT')->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_payment_method', 'LEFT')->on('whitelabel_transaction.whitelabel_payment_method_id', '=', 'whitelabel_payment_method.id')
            ->join('payment_method', 'LEFT')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id')
            ->and_where('is_casino', '=', $isCasino);

        self::addConditionIfDeposit($query, $isDeposit);
        
        switch ($active_tab) {
            case 'purchases':
                $query->and_where('whitelabel_transaction.type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE);
            break;
            case 'deposits':
                $query->and_where('whitelabel_transaction.type', '=', Helpers_General::TYPE_TRANSACTION_DEPOSIT);
            break;
            case 'pending':
                $query->and_where('whitelabel_transaction.status', '=', Helpers_General::STATUS_TRANSACTION_PENDING);
            break;
            case 'approved':
                $query->and_where('whitelabel_transaction.status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED);
            break;
            case 'error':
                $query->and_where('whitelabel_transaction.status', '=', Helpers_General::STATUS_TRANSACTION_ERROR);
        }

        if ($whitelabel_id) {
            $query->and_where('whitelabel_transaction.whitelabel_id', '=', $whitelabel_id);
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

        return $query->execute()->as_array();
    }

    public static function get_transactions_count_for_crm(
        ?int $whitelabelId,
        array $filters,
        bool $isCasino = false,
        bool $isDeposit = false
    ): int {
        $res = 0;
            
        $query = DB::select(
            'whitelabel_transaction.id'
        )
            ->from('whitelabel_transaction')
            ->join('whitelabel_user', 'LEFT')->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_payment_method', 'LEFT')->on('whitelabel_transaction.whitelabel_payment_method_id', '=', 'whitelabel_payment_method.id')
            ->join('payment_method', 'LEFT')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id')
            ->where('is_casino', '=', $isCasino);
        
        if ($whitelabelId) {
            $query->and_where('whitelabel_transaction.whitelabel_id', '=', $whitelabelId);
        }

        self::addConditionIfDeposit($query, $isDeposit);

        $query = self::prepare_filters($filters, $query, $whitelabelId);
        $query = self::getCountQuery($query);

        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }


    public static function get_purchases_count_for_crm(
        ?int $whitelabelId,
        array $filters,
        bool $isCasino = false,
        bool $isDeposit = false
    ): int {
        $res = 0;

        $query = DB::select(
            'whitelabel_transaction.id'
        )
            ->from('whitelabel_transaction')
            ->where('whitelabel_transaction.type', '=', Helpers_General::TYPE_TRANSACTION_PURCHASE)
            ->join('whitelabel_user', 'LEFT')->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_payment_method', 'LEFT')->on('whitelabel_transaction.whitelabel_payment_method_id', '=', 'whitelabel_payment_method.id')
            ->join('payment_method', 'LEFT')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id')
            ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_transaction.whitelabel_id', '=', $whitelabelId);
        }

        self::addConditionIfDeposit($query, $isDeposit);
        
        $query = self::prepare_filters($filters, $query, $whitelabelId);
        $query = self::getCountQuery($query);

        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    public static function get_pending_count_for_crm(
        ?int $whitelabelId,
        array $filters,
        bool $isCasino = false,
        bool $isDeposit = false
    ): int {
        $res = [];

        $query = DB::select(
            'whitelabel_transaction.id'
        )
            ->from('whitelabel_transaction')->join('whitelabel_user', 'LEFT')->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_payment_method', 'LEFT')->on('whitelabel_transaction.whitelabel_payment_method_id', '=', 'whitelabel_payment_method.id')
            ->join('payment_method', 'LEFT')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id')
            ->where('whitelabel_transaction.status', '=', Helpers_General::STATUS_TRANSACTION_PENDING)
            ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_transaction.whitelabel_id', '=', $whitelabelId);
        }

        self::addConditionIfDeposit($query, $isDeposit);
        
        $query = self::prepare_filters($filters, $query, $whitelabelId);
        $query = self::getCountQuery($query);

        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    public static function get_approved_count_for_crm(
        ?int $whitelabelId,
        array $filters,
        bool $isCasino = false,
        bool $isDeposit = false
    ):int {
        $res = [];

        $query = DB::select(
            'whitelabel_transaction.id'
        )
            ->from('whitelabel_transaction')->join('whitelabel_user', 'LEFT')->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_payment_method', 'LEFT')->on('whitelabel_transaction.whitelabel_payment_method_id', '=', 'whitelabel_payment_method.id')
            ->join('payment_method', 'LEFT')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id')
            ->where('whitelabel_transaction.status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED)
            ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_transaction.whitelabel_id', '=', $whitelabelId);
        }

        self::addConditionIfDeposit($query, $isDeposit);

        $query = self::prepare_filters($filters, $query, $whitelabelId);
        $query = self::getCountQuery($query);

        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    public static function get_error_count_for_crm(
        ?int $whitelabelId,
        array $filters,
        bool $isCasino = false,
        bool $isDeposit = false
    ): int {
        $res = [];

        $query = DB::select(
            'whitelabel_transaction.id'
        )
            ->from('whitelabel_transaction')->join('whitelabel_user', 'LEFT')->on('whitelabel_transaction.whitelabel_user_id', '=', 'whitelabel_user.id')
            ->join('whitelabel_payment_method', 'LEFT')->on('whitelabel_transaction.whitelabel_payment_method_id', '=', 'whitelabel_payment_method.id')
            ->join('payment_method', 'LEFT')->on('whitelabel_payment_method.payment_method_id', '=', 'payment_method.id')
            ->where('whitelabel_transaction.status', '=', Helpers_General::STATUS_TRANSACTION_ERROR)
            ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_transaction.whitelabel_id', '=', $whitelabelId);
        }

        self::addConditionIfDeposit($query, $isDeposit);
        
        $query = self::prepare_filters($filters, $query, $whitelabelId);
        $query = self::getCountQuery($query);

        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    /**
     * @access private
     * @param array $filters
     * @param Database_Query_Builder_Select $query
     * @param int $whitelabel
     * @return Database_Query_Builder_Select
     */
    private static function prepare_filters($filters, $query, $whitelabel)
    {
        foreach ($filters as $filter) {
            if ($filter['column'] == 'full_token') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_transaction.token', 'LIKE', $value);
            }
            if ($filter['column'] == 'user_name') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where_open();
                $query->and_where('whitelabel_user.name', 'LIKE', $value)
                        ->or_where('whitelabel_user.login', 'LIKE', $value)
                        ->or_where('whitelabel_user.token', 'LIKE', $value)
                        ->or_where('whitelabel_user.email', 'LIKE', $value);
                $query->and_where_close();
            }
            if ($filter['column'] == 'method') {
                switch ($filter['value']) {
                    case "balance":
                        $query->and_where('whitelabel_transaction.payment_method_type', '=', Helpers_General::PAYMENT_TYPE_BALANCE);
                        break;
                    case "bonus_balance":
                        $query->and_where('whitelabel_transaction.payment_method_type', '=', Helpers_General::PAYMENT_TYPE_BONUS_BALANCE);
                        break;
                    default:
                        $query->and_where('payment_method.id', '=', intval($filter['value']));
                }
            }
            if ($filter['column'] == 'status') {
                $query->and_where('whitelabel_transaction.status', '=', intval($filter['value']));
            }
            if ($filter['column'] == 'amount') {
                $amount = 'whitelabel_transaction.amount_usd';
                if ($whitelabel) {
                    $amount = 'whitelabel_transaction.amount_manager';
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
            if ($filter['column'] == 'bonus_amount') {
                $amount = 'whitelabel_transaction.bonus_amount_usd';
                if ($whitelabel) {
                    $amount = 'whitelabel_transaction.bonus_amount_manager';
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
            if ($filter['column'] == 'tickets_count') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('tickets.count', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('tickets.count', '>=', intval($filter['start']))
                    ->and_where('tickets.count', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('tickets.count', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_transaction.date', '>=', $start);
                $query->and_where('whitelabel_transaction.date', '<=', $end);
            }
            if ($filter['column'] == 'date_confirmed') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('whitelabel_transaction.date_confirmed', '>=', $start);
                $query->and_where('whitelabel_transaction.date_confirmed', '<=', $end);
            }
        }

        return $query;
    }

    private static function addConditionIfDeposit(Database_Query_Builder_Where $query, bool $isDeposit): void
    {
        if ($isDeposit) {
            $query->and_where('whitelabel_transaction.type', '=', Helpers_General::TYPE_TRANSACTION_DEPOSIT);
        } else {
            $query->and_where('whitelabel_transaction.type', '<>', Helpers_General::TYPE_TRANSACTION_DEPOSIT);
        }
    }

    private static function getTicketsCountPerTransactionQuery(): string
    {
        return "(SELECT COUNT(*) AS count FROM `whitelabel_user_ticket` 
            WHERE `whitelabel_user_ticket`.`whitelabel_transaction_id` = `whitelabel_transaction`.`id` 
            GROUP BY `whitelabel_transaction`.`id`)";
    }

    private static function getTicketsProcessedCountPerTransactionQuery(): string
    {
        return "(SELECT COUNT(*) AS count FROM `whitelabel_user_ticket` 
            WHERE `whitelabel_user_ticket`.`whitelabel_transaction_id` = `whitelabel_transaction`.`id`
            AND `whitelabel_user_ticket`.`date_processed` IS NOT NULL
            GROUP BY `whitelabel_transaction`.`id`)";
    }

    private static function getSumsMarginManagerQuery(string $filters): string
    {
        return "(IFNULL(SUM(margin_manager), 0) +
                IFNULL((SELECT SUM(margin_manager)
                FROM whitelabel_user_ticket
                JOIN whitelabel_user ON whitelabel_user.id = whitelabel_user_ticket.whitelabel_user_id
                WHERE whitelabel_transaction_id IS NULL $filters), 0))";
    }

    private static function getCountQuery(Database_Query_Builder $query): Database_Query_Builder
    {
        return DB::select(DB::expr('COUNT(*) as count'))
            ->from(DB::expr("(" . $query->compile() . ") AS countQuery"));
    }

    private static function addAdditionalDataWithAmountSumDisplayAndCountToResponse(array &$response, ?int $whitelabelId): void
    {

        if (!empty($response['groupedTransactionsByDatePerMonth'])) {
            $whitelabelRepository = Container::get(WhitelabelRepository::class);
            $whitelabel = $whitelabelRepository->findOneById($whitelabelId);
            $amountSum = '0.00';
            foreach ($response['groupedTransactionsByDatePerMonth'] as $singleMonth) {
                $amountFromSingleMonth = is_null($whitelabelId) ? $singleMonth['amountUsdSum'] : $singleMonth['amountManagerSum'];
                $amountSum = bcadd($amountFromSingleMonth, $amountSum, 2);
            }
            $amountSumDisplay = Lotto_View::format_currency(
                $amountSum,
                $whitelabel->currency->code ?? 'USD', // $whitelabel not exists when super admin is logged in
                true
            );
            $response['additionalData'] = [
                'amountSumDisplay' => $amountSumDisplay,
            ];
        }
    }
}
