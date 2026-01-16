<?php

use Fuel\Core\Database_Query_Builder;
use Models\Whitelabel;
use Repositories\WhitelabelRepository;
use Services\Logs\FileLoggerService;

class Model_Withdrawal_Request extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'withdrawal_request';
    
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
    
    /**
     *
     * @return array
     */
    public function get_whitelabel()
    {
        return $this->whitelabel;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @return null|int
     */
    public static function count_for_whitelabel(
        $whitelabel = [],
        $user = []
    ) {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        
        $query = "SELECT 
                COUNT(*) AS count 
            FROM withdrawal_request 
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($user) && !empty($user['id'])) {
            $query .= " AND whitelabel_user_id = :user_id";
        }

        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($user) && !empty($user['id'])) {
                $db->param(":user_id", $user['id']);
            }
            
            /** @var object $db */
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
     * @return null|array
     */
    public static function get_full_data_for_user_rodo($user)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                withdrawal.name AS withdrawal_type,
                CONCAT(whitelabel.prefix, 'T', withdrawal_request.token) AS withdrawal_prefix_token,
                currency.code AS c_code,
                withdrawal_request.* 
            FROM withdrawal_request 
            INNER JOIN whitelabel 
                ON withdrawal_request.whitelabel_id = whitelabel.id 
            INNER JOIN withdrawal 
                ON withdrawal_request.withdrawal_id = withdrawal.id
            LEFT JOIN currency 
                ON withdrawal_request.currency_id = currency.id
            WHERE 1=1 ";
        
        if (!empty($user) && !empty($user["id"])) {
            $query .= " AND withdrawal_request.whitelabel_user_id = :user_id";
        }
        
        $query .= " ORDER BY withdrawal_request.id";
        
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
     * @param array $whitelabel
     * @param array $user
     * @return int
     */
    public static function get_sum_in_USD($whitelabel, $user)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = 0;
        $res = null;
        $query = "SELECT 
                COALESCE(SUM(amount_usd), 0) AS amount_usd_sum 
            FROM withdrawal_request 
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($user) && !empty($user['id'])) {
            $query .= " AND whitelabel_user_id = :user_id";
        }
        
        $query .= " AND withdrawal_id = " . Helpers_Withdrawal_Method::WITHDRAWAL_DEBIT_CARD;
        $query .= " AND status IN (";
        $query .= Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING . ", ";
        $query .= Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED . ")";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($user) && !empty($user['id'])) {
                $db->param(":user_id", $user['id']);
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

        $result = $res[0]['amount_usd_sum'];
            
        return $result;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param array $user
     * @param int $offset Default 0
     * @param int $limit Default 5
     * @return null|array
     */
    public static function get_data_for_user_and_whitelabel(
        $whitelabel,
        $user,
        $offset = 0,
        $limit = 5
    ) {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $isCasino = (int)!empty(IS_CASINO);
        $query = "SELECT 
                withdrawal_request.*, 
                withdrawal.name 
            FROM withdrawal_request
            LEFT JOIN withdrawal ON withdrawal.id = withdrawal_request.withdrawal_id
            WHERE is_casino = $isCasino ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND withdrawal_request.whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($user) && !empty($user['id'])) {
            $query .= " AND withdrawal_request.whitelabel_user_id = :user_id";
        }
        
        $query .= " ORDER BY withdrawal_request.id DESC ";
        $query .= " LIMIT :offset, :limit";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($user) && !empty($user['id'])) {
                $db->param(":user_id", $user['id']);
            }
            $db->param(":offset", $offset);
            $db->param(":limit", $limit);
            
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
     * @param array $whitelabel
     * @param int $token
     * @return string
     */
    public static function get_data_for_whitelabel_by_token($whitelabel, $token)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            wr.*, 
            w.name, 
            withdrawal_currency.id AS withdrawal_currency_id,
            withdrawal_currency.code AS withdrawal_currency_code,
            withdrawal_currency.rate AS withdrawal_currency_rate,
            manager_currency.code AS manager_currency_code 
        FROM withdrawal_request wr
        LEFT JOIN withdrawal w ON w.id = wr.withdrawal_id 
        INNER JOIN currency withdrawal_currency ON wr.currency_id = withdrawal_currency.id 
        INNER JOIN whitelabel wl ON wl.id = wr.whitelabel_id 
        INNER JOIN currency manager_currency ON wl.manager_site_currency_id = manager_currency.id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND wr.whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($token)) {
            $query .= " AND wr.token = :token";
        }
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($token)) {
                $db->param(":token", $token);
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
        $result = $res[0];
        
        return $result;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param array $params
     * @param string $filter_add
     * @return null|int
     */
    public static function count_for_whitelabel_filtered(
        $whitelabel = [],
        $params = [],
        $filter_add = ""
    ) {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            COUNT(*) AS count 
        FROM withdrawal_request
        LEFT JOIN whitelabel_user ON whitelabel_user.id = withdrawal_request.whitelabel_user_id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND withdrawal_request.whitelabel_id = :whitelabel_id ";
        }
        
        $query .= " " . $filter_add;
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
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

        if ($res === null || empty($res[0])) {
            return $res;
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
     * @param string $filter_add
     * @return null|array
     */
    public static function get_full_data_for_whitelabel_filtered(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $filter_add = ""
    ): ?array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $balanceFieldName = IS_CASINO ? 'casino_balance' : 'balance';
        $query = "SELECT 
            withdrawal_request.*, 
            whitelabel_user.id AS uid, 
            withdrawal.name AS wname, 
            whitelabel_user.token AS utoken, 
            whitelabel_user.is_deleted, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user.email, 
            whitelabel_user.$balanceFieldName, 
            user_currency.id AS user_currency_id,
            user_currency.code AS user_currency_code,
            user_currency.rate AS user_currency_rate,
            withdrawal_currency.id AS withdrawal_currency_id,
            withdrawal_currency.code AS withdrawal_currency_code,
            withdrawal_currency.rate AS withdrawal_currency_rate,
            manager_currency.code AS manager_currency_code 
        FROM withdrawal_request
        INNER JOIN whitelabel_user ON whitelabel_user.id = withdrawal_request.whitelabel_user_id 
        INNER JOIN withdrawal ON withdrawal.id = withdrawal_request.withdrawal_id 
        INNER JOIN currency user_currency ON whitelabel_user.currency_id = user_currency.id 
        INNER JOIN currency withdrawal_currency ON withdrawal_request.currency_id = withdrawal_currency.id 
        INNER JOIN whitelabel ON whitelabel.id = withdrawal_request.whitelabel_id 
        INNER JOIN currency manager_currency ON whitelabel.manager_site_currency_id = manager_currency.id 
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND withdrawal_request.whitelabel_id = :whitelabel_id ";
        }
        
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
            /** @var object $pagination */
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
     * Fetch count of pending withdrawals for user
     *
     * @param int $whitelabel_id whitelabel id
     * @param int $user_id user id
     * @return int count of the pending withdrawals for specified user.
     */
    public static function fetch_count_pending_for_user(int $whitelabel_id, int $user_id, $isCasino = false): int
    {
        $params = [];
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":whitelabel_user_id", $user_id];
        $params[] = [":status", Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING];
        $isCasino = (int)$isCasino;

        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM withdrawal_request 
        WHERE whitelabel_id = :whitelabel_id
        AND whitelabel_user_id = :whitelabel_user_id 
        AND status = :status
        AND is_casino = $isCasino";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }

    public static function get_withdrawals_count_for_crm(?int $whitelabelId, array $filters, bool $isCasino): int
    {
        $res = 0;

        $query = DB::select(DB::expr('COUNT(*) as count'))
        ->from('withdrawal_request')
        ->join('whitelabel_user')->on('withdrawal_request.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id');

        if ($whitelabelId) {
            $query->and_where('withdrawal_request.whitelabel_id', '=', $whitelabelId);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query = self::prepare_filters($filters, $query, $whitelabelId);

        /** @var object $query */
        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    public static function get_withdrawals_pending_count_for_crm(?int $whitelabelId, array $filters, bool $isCasino): int
    {
        $res = 0;

        $query = DB::select(DB::expr('COUNT(*) as count'))
        ->from('withdrawal_request')
        ->join('whitelabel_user')->on('withdrawal_request.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id')
        ->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING);

        if ($whitelabelId) {
            $query->and_where('withdrawal_request.whitelabel_id', '=', $whitelabelId);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query = self::prepare_filters($filters, $query, $whitelabelId);

        /** @var object $query */
        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    public static function get_withdrawals_approved_count_for_crm(?int $whitelabelId, array $filters, bool $isCasino): int
    {
        $res = 0;

        $query = DB::select(DB::expr('COUNT(*) as count'))
        ->from('withdrawal_request')
        ->join('whitelabel_user')->on('withdrawal_request.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id')
        ->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED);

        if ($whitelabelId) {
            $query->and_where('withdrawal_request.whitelabel_id', '=', $whitelabelId);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query = self::prepare_filters($filters, $query, $whitelabelId);

        /** @var object $query */
        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    public static function get_withdrawals_declined_count_for_crm(?int $whitelabelId, array $filters, bool $isCasino): int
    {
        $res = 0;

        $query = DB::select(DB::expr('COUNT(*) as count'))
        ->from('withdrawal_request')
        ->join('whitelabel_user')->on('withdrawal_request.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id')
        ->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED);

        if ($whitelabelId) {
            $query->and_where('withdrawal_request.whitelabel_id', '=', $whitelabelId);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query = self::prepare_filters($filters, $query, $whitelabelId);

        /** @var object $query */
        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    public static function get_withdrawals_canceled_count_for_crm(?int $whitelabelId, array $filters, bool $isCasino): int
    {
        $res = 0;

        $query = DB::select(DB::expr('COUNT(*) as count'))
        ->from('withdrawal_request')
        ->join('whitelabel_user')->on('withdrawal_request.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id')
        ->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED);

        if ($whitelabelId) {
            $query->and_where('withdrawal_request.whitelabel_id', '=', $whitelabelId);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query = self::prepare_filters($filters, $query, $whitelabelId);

        /** @var object $query */
        $result = $query->execute()->as_array();
        if (!empty($result[0])) {
            $res = $result[0]['count'];
        }
        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param string $active_tab
     * @param array $filters
     * @param int $page
     * @param int $items_per_page
     * @param string $sort_by
     * @param string $order
     * @return array
     */
    public static function get_withdrawals_data_for_crm(
        ?int $whitelabel_id,
        string $active_tab,
        array $filters = [],
        ?int $page = null,
        ?int $items_per_page = null,
        ?string $sort_by = null,
        ?string $order = null,
        bool $is_cache_disabled = false,
        bool $isCasino = false
    ) {
        $userBalanceFieldName = $isCasino ? 'casino_balance' : 'balance';
        $query = DB::select(
            'withdrawal_request.*',
            ['whitelabel.prefix','whitelabel_prefix'],
            ['c1.code','whitelabel_currency_code'],
            ['c2.code','user_currency_id'],
            ['c2.code','user_currency_code'],
            ['c2.rate','user_currency_rate'],
            ['whitelabel_user.token', 'user_token'],
            ["whitelabel_user.{$userBalanceFieldName}", 'user_balance'],
            ['whitelabel_user.name', 'user_name'],
            ['whitelabel_user.surname', 'user_surname'],
            ['whitelabel_user.email', 'user_email'],
            ['whitelabel_user.login', 'user_login'],
            ['withdrawal.name', 'method'],
            ['whitelabel_user_group.name', 'user_prize_group_name']
        )
        ->from('withdrawal_request')
        ->join('whitelabel')->on('withdrawal_request.whitelabel_id', '=', 'whitelabel.id')
        ->join('whitelabel_user')->on('withdrawal_request.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join(['currency', 'c1'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'c1.id')
        ->join(['currency', 'c2'], 'LEFT')->on('whitelabel_user.currency_id', '=', 'c2.id')
        ->join('withdrawal', 'LEFT')->on('withdrawal_request.withdrawal_id', '=', 'withdrawal.id')
        ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id');

        switch ($active_tab) {
            case 'pending':
                $query->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING);
            break;
            case 'approved':
                $query->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED);
            break;
            case 'declined':
                $query->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED);
            break;
            case 'canceled':
                $query->where('withdrawal_request.status', '=', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED);
        }

        if ($whitelabel_id) {
            $query->where('withdrawal_request.whitelabel_id', '=', $whitelabel_id);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query = self::prepare_filters($filters, $query, $whitelabel_id);

        if ($sort_by) {
            if ($sort_by === 'request_details') {
                $sort_by = 'data';
            }

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
     *
     * @access public
     * @param int $token
     * @return array
     */
    public static function get_single_for_crm($token, bool $isCasino = false)
    {
        $result = [];
        $res = null;
        $userBalanceFieldName = $isCasino ? 'casino_balance' : 'balance';

        $query = DB::select(
            'withdrawal_request.*',
            ['whitelabel.prefix','whitelabel_prefix'],
            ['c1.code','whitelabel_currency_code'],
            ['c2.code','user_currency_id'],
            ['c2.code','user_currency_code'],
            ['c2.rate','user_currency_rate'],
            ['whitelabel_user.token', 'user_token'],
            ["whitelabel_user.{$userBalanceFieldName}", 'user_balance'],
            ['whitelabel_user.name', 'user_name'],
            ['whitelabel_user.surname', 'user_surname'],
            ['whitelabel_user.email', 'user_email'],
            ['whitelabel_user.login', 'user_login'],
            ['withdrawal.name', 'method'],
            ['whitelabel_user_group.name', 'user_prize_group_name']
        )
        ->from('withdrawal_request')
        ->join('whitelabel')->on('withdrawal_request.whitelabel_id', '=', 'whitelabel.id')
        ->join('whitelabel_user')->on('withdrawal_request.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join(['currency', 'c1'], 'LEFT')->on('whitelabel.manager_site_currency_id', '=', 'c1.id')
        ->join(['currency', 'c2'], 'LEFT')->on('whitelabel_user.currency_id', '=', 'c2.id')
        ->join('withdrawal', 'LEFT')->on('withdrawal_request.withdrawal_id', '=', 'withdrawal.id')
        ->join('whitelabel_user_group', 'LEFT')->on('whitelabel_user.prize_payout_whitelabel_user_group_id', '=', 'whitelabel_user_group.id');

        if (!empty($token)) {
            $query->where('withdrawal_request.token', '=', $token);
        }
        $query->limit(1);

        /** @var object $query */
        $res = $query->execute()->as_array();
        if (!empty($res[0])) {
            $result = $res[0];
        }
        
        return $result;
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
            if ($filter['column'] == 'token') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('withdrawal_request.token', 'LIKE', $value);
            }
            if ($filter['column'] === 'user_prize_group_name')
            {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('whitelabel_user_group.name', 'LIKE', $value);
            }
            if ($filter['column'] == 'user_name') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where_open()
                        ->where('whitelabel_user.name', 'LIKE', $value)
                        ->or_where('whitelabel_user.token', 'LIKE', $value)
                        ->or_where('whitelabel_user.email', 'LIKE', $value)
                        ->and_where_close();
            }
            if ($filter['column'] == 'method') {
                $query->and_where('withdrawal_request.withdrawal_id', '=', intval($filter['value']));
            }
            if ($filter['column'] == 'status') {
                $query->and_where('withdrawal_request.status', '=', intval($filter['value']));
            }
            if ($filter['column'] == 'amount') {
                $amount = 'withdrawal_request.amount_usd';
                if ($whitelabel) {
                    $amount = 'withdrawal_request.amount_manager';
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
            if ($filter['column'] == 'user_balance') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('whitelabel_user.balance', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.balance', '>=', intval($filter['start']))
                    ->and_where('whitelabel_user.balance', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('whitelabel_user.balance', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] == 'date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('withdrawal_request.date', '>=', $start);
                $query->and_where('withdrawal_request.date', '<=', $end);
            }
            if ($filter['column'] == 'date_confirmed') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('withdrawal_request.date_confirmed', '>=', $start);
                $query->and_where('withdrawal_request.date_confirmed', '<=', $end);
            }
            if ($filter['column'] === 'request_details') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('withdrawal_request.data', 'LIKE', $value);
            }
        }

        return $query;
    }

    public static function get_withdrawals_count_for_crm_last_month(?int $whitelabelId, bool $isCasino): array
    {
        $query = DB::select('status', DB::expr('COUNT(*) AS count'))->from('withdrawal_request')
        ->where('date', '>=', DB::expr(DB::expr('DATE(NOW()) - INTERVAL 30 DAY')));

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query->group_by('status');

        /** @var Database_Query_Builder $results */
        $results = $query->execute();

        return $results->as_array();
    }

    public static function get_pending_for_crm_date_range(
        ?int $whitelabelId,
        string $startDate,
        string $endDate,
        bool $isCasino = false
    ): array {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = DB::select(
            DB::expr('SUM(amount_manager) AS amountSumManager'),
            DB::expr('SUM(amount_usd) AS amountUsdSum'),
            DB::expr('date(date) as date'),
            DB::expr('COUNT(*) AS count')
        )->from('withdrawal_request')
        ->where('status', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING)
        ->and_where('date', '>=', $start)
        ->and_where('date', '<=', $end)
        ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $query->group_by(DB::expr('date(date)'));
        /** @var object $query */
        $response['groupByDatePerMonth'] = $query->execute()->as_array();
        self::addAdditionalDataWithAmountSumDisplayAndCountToResponse($response, $whitelabelId);


        return $response;
    }

    public static function get_approved_for_crm_date_range(
        ?int $whitelabelId,
        string $startDate,
        string $endDate,
        bool $isCasino = false
    ): array {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = DB::select(
            DB::expr('SUM(amount_manager) AS amountSumManager'),
            DB::expr('SUM(amount_usd) AS amountUsdSum'),
            DB::expr('date(date) as date'),
            DB::expr('COUNT(*) AS count')
        )->from('withdrawal_request')
        ->where('status', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED)
        ->and_where('date', '>=', $start)
        ->and_where('date', '<=', $end);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $query->and_where('is_casino', '=', $isCasino);

        $query->group_by(DB::expr('date(date)'));
        /** @var object $query */
        $response['groupByDatePerMonth'] = $query->execute()->as_array();
        self::addAdditionalDataWithAmountSumDisplayAndCountToResponse($response, $whitelabelId);

        return $response;
    }

    public static function get_canceled_for_crm_date_range(
        ?int $whitelabelId,
        string $startDate,
        string $endDate,
        bool $isCasino = false
    ): array
    {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = DB::select(
            DB::expr('SUM(amount_manager) AS amountSumManager'),
            DB::expr('SUM(amount_usd) AS amountUsdSum'),
            DB::expr('date(date) as date'),
            DB::expr('COUNT(*) AS count')
        )->from('withdrawal_request')
        ->where('status', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED)
        ->and_where('date', '>=', $start)
        ->and_where('date', '<=', $end)
        ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $query->group_by(DB::expr('date(date)'));
        /** @var object $query */
        $response['groupByDatePerMonth'] = $query->execute()->as_array();
        self::addAdditionalDataWithAmountSumDisplayAndCountToResponse($response, $whitelabelId);

        return $response;
    }

    public static function get_declined_for_crm_date_range(
        ?int $whitelabelId,
        string $startDate,
        string $endDate,
        bool $isCasino = false
    ): array {
        $start = Helpers_Crm_General::prepare_start_date($startDate);
        $end = Helpers_Crm_General::prepare_end_date($endDate);

        $query = DB::select(
            DB::expr('SUM(amount_manager) AS amountSumManager'),
            DB::expr('SUM(amount_usd) AS amountUsdSum'),
            DB::expr('date(date) as date'),
            DB::expr('COUNT(*) AS count')
        )->from('withdrawal_request')
        ->where('status', Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED)
        ->and_where('date', '>=', $start)
        ->and_where('date', '<=', $end)
        ->and_where('is_casino', '=', $isCasino);

        if ($whitelabelId) {
            $query->and_where('whitelabel_id', '=', $whitelabelId);
        }

        $query->group_by(DB::expr('date(date)'));
        /** @var object $query */
        $response['groupByDatePerMonth'] = $query->execute()->as_array();
        self::addAdditionalDataWithAmountSumDisplayAndCountToResponse($response, $whitelabelId);

        return $response;
    }

    private static function addAdditionalDataWithAmountSumDisplayAndCountToResponse(array &$response, ?int $whitelabelId): void
    {
        if (!empty($response['groupByDatePerMonth'])) {
            $whitelabelRepository = Container::get(WhitelabelRepository::class);
            $whitelabel = $whitelabelRepository->findOneById($whitelabelId);
            $amountSum = '0.00';
            $count = '0';
            foreach ($response['groupByDatePerMonth'] as $singleMonth) {
                $amountFromSingleMonth = is_null($whitelabelId) ? $singleMonth['amountUsdSum'] : $singleMonth['amountSumManager'];
                $amountSum = bcadd($amountFromSingleMonth, $amountSum, 2);
                $count = bcadd($singleMonth['count'], $count);
            }
            $amountSumDisplay = Lotto_View::format_currency(
                $amountSum,
                $whitelabel->currency->code ?? 'USD', // $whitelabel not exists when super admin is logged in
                true
            );
            $response['additionalData'] = [
                'amountSumDisplay' => $amountSumDisplay,
                'count' => $count,
            ];
        }
    }
}
