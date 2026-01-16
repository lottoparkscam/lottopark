<?php

use Services\Logs\FileLoggerService;

/**
 * Model class of Whitelabel to manage whitelabels
 * @deprecated
 */
class Model_Whitelabel extends \Model_Model
{

    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel';

    /**
     *
     * @var array
     */
    public static $cache_list = [
        "model_whitelabel.lotteriesbyhighestjackpot",
        "model_whitelabel.bydomain",
        "model_whitelabel.alllotteriesbyhighestjackpot",
        "model_whitelabel.lotteriesbynearestdraw",
        "model_whitelabel.lotteriesbycustomorder",
        "model_whitelabel.lotteriesbyorder",
        "model_whitelabel.kenobyhighestjackpot",
        "model_whitelabel.allkenobyhighestjackpot",
    ];

    /**
     *
     * @param string $domain
     * @return null|array
     */
    public static function get_by_domain(string $domain):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[1] . '.' . str_replace('.', '-', $domain);

        $query = "SELECT whitelabel.* 
            FROM whitelabel 
            WHERE whitelabel.domain = :domain";

        $db = DB::query($query);
        $db->param(":domain", $domain);

        try {
            try {
                $whitelabel = Lotto_Helper::get_cache($key);
            } catch (\CacheNotFoundException $e) { // catching CacheExpired too
                $whitelabel = $db->execute()->as_array();
                if ($whitelabel == null) {
                    return null;
                }
                $whitelabel = $whitelabel[0];
                Lotto_Helper::set_cache($key, $whitelabel, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $whitelabel = $db->execute()->as_array();
            if ($whitelabel == null) {
                return null;
            }
            $whitelabel = $whitelabel[0];
        }

        return $whitelabel;
    }

    /**
     *
     * @param string $whitelabel_id
     * @param boolean $all
     * @param boolean $kenoOnly
     * @return array
     * @throws \CacheExpiredException
     */
    public static function get_lotteries_by_highest_jackpot_for_whitelabel($whitelabel_id, $all = false, $kenoOnly = false)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);
        $expiredTime = Helpers_Whitelabel::get_expired_time();

        if ($all && $kenoOnly) {
            $key = self::$cache_list[7];
        } elseif ($kenoOnly) {
            $key = self::$cache_list[6];
        } elseif ($all) {
            $key = self::$cache_list[2];
        } else {
            $key = self::$cache_list[0];
        }

        if (!empty($whitelabel_id)) {
            $key .= '.' . $whitelabel_id;
        }

        $query = "SELECT 
            lottery.*, 
            currency.code AS currency, 
            whitelabel_lottery.is_enabled AS wis_enabled, 
            lottery_provider.id AS lp_id, 
            provider, 
            min_bets, 
            max_bets, 
            multiplier, 
            closing_time, 
            closing_times, 
            lottery_provider.timezone AS lp_timezone, 
            lottery_provider.offset AS lp_offset, 
            lottery_provider.data,
            fc.code AS force_currency,
            min_lines
        FROM lottery 
        JOIN whitelabel_lottery ON whitelabel_lottery.lottery_id = lottery.id 
        JOIN whitelabel ON whitelabel.id = whitelabel_lottery.whitelabel_id 
        JOIN currency ON currency.id = lottery.currency_id
        LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
        JOIN lottery_provider ON lottery_provider.id = whitelabel_lottery.lottery_provider_id 
        WHERE lottery.is_enabled = 1 
        AND lottery.playable = 1";

        if ($kenoOnly) {
            $query .= " AND lottery.type = 'keno'";
        }

        if (!empty($whitelabel_id)) {
            $query .= " AND whitelabel_lottery.whitelabel_id = :whitelabel_id";
        }

        $query .= " AND whitelabel_lottery.is_enabled = 1 
            " . (($all == false) ? " AND next_date_utc >= NOW() " : "") . "
            AND current_jackpot IS NOT NULL 
            AND current_jackpot != 0 
        ORDER BY current_jackpot_usd DESC";

        $db = DB::query($query);
        if (!empty($whitelabel_id)) {
            $db->param(":whitelabel_id", $whitelabel_id);
        }

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
                if (!$all) {
                    $now = new DateTime("now", new DateTimeZone("UTC"));
                    foreach ($lotteries as $lottery) {
                        $next = new DateTime($lottery['next_date_utc'], new DateTimeZone("UTC"));
                        if ($next < $now) {
                            throw new \CacheExpiredException();
                        }
                    }
                }
            } catch (\CacheNotFoundException $e) { // catching CacheExpired too
                $lotteries = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $lotteries = $db->execute()->as_array();
        }

        return $lotteries;
    }

    /**
     *
     * @param string $whitelabel_id
     * @return array
     * @throws \CacheExpiredException
     */
    public static function get_lotteries_by_nearest_draw_for_whitelabel($whitelabel_id)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[3];
        if (!empty($whitelabel_id)) {
            $key .= '.' . $whitelabel_id;
        }

        $query = "SELECT 
                lottery.*, currency.code AS currency, whitelabel_lottery.is_enabled AS wis_enabled, 
                lottery_provider.id AS lp_id, provider, min_bets, max_bets, multiplier, closing_time, closing_times, 
                lottery_provider.timezone AS lp_timezone, lottery_provider.offset AS lp_offset, lottery_provider.data,
                fc.code AS force_currency 
            FROM lottery 
            JOIN whitelabel_lottery ON whitelabel_lottery.lottery_id = lottery.id 
            JOIN whitelabel ON whitelabel.id = whitelabel_lottery.whitelabel_id 
            JOIN currency ON currency.id = lottery.currency_id 
            LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
            JOIN lottery_provider ON lottery_provider.id = whitelabel_lottery.lottery_provider_id 
            WHERE lottery.is_enabled = 1 ";

        if (!empty($whitelabel_id)) {
            $query .= " AND whitelabel_lottery.whitelabel_id = :whitelabel_id";
        }

        $query .= " AND whitelabel_lottery.is_enabled = 1 
            AND current_jackpot IS NOT NULL 
            AND current_jackpot != 0 ORDER BY next_date_utc";

        $db = DB::query($query);

        if (!empty($whitelabel_id)) {
            $db->param(":whitelabel_id", $whitelabel_id);
        }

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
                $now = new DateTime("now", new DateTimeZone("UTC"));
                foreach ($lotteries as $lottery) {
                    $next = new DateTime($lottery['next_date_utc'], new DateTimeZone("UTC"));
                    if ($next < $now) {
                        throw new \CacheExpiredException();
                    }
                }
            } catch (\CacheNotFoundException $e) { // catching CacheExpired too
                $lotteries = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $lotteries = $db->execute()->as_array();
        }

        return $lotteries;
    }

    /**
     *
     * @param int $whitelabel_id
     * @param array $order
     * @return array
     * @throws \CacheExpiredException
     */
    public static function get_lotteries_by_custom_order_for_whitelabel(int $whitelabel_id, $order)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[4];
        if (!empty($whitelabel_id)) {
            $key .= '.' . $whitelabel_id;
        }

        $query = "SELECT 
            lottery.*, 
            currency.code AS currency, 
            whitelabel_lottery.is_enabled AS wis_enabled, 
            lottery_provider.id AS lp_id, 
            provider, 
            min_bets, 
            max_bets, 
            multiplier, 
            closing_time,
            closing_times,  
            lottery_provider.timezone AS lp_timezone, 
            lottery_provider.offset AS lp_offset, 
            lottery_provider.data,
            fc.code AS force_currency
        FROM lottery
        JOIN whitelabel_lottery ON whitelabel_lottery.lottery_id = lottery.id
        JOIN whitelabel ON whitelabel.id = whitelabel_lottery.whitelabel_id
        JOIN currency ON currency.id = lottery.currency_id
        LEFT JOIN currency fc ON fc.id = lottery.force_currency_id
        JOIN lottery_provider ON lottery_provider.id = whitelabel_lottery.lottery_provider_id
        WHERE lottery.is_enabled = 1 ";

        if (!empty($whitelabel_id)) {
            $query .= " AND whitelabel_lottery.whitelabel_id = :whitelabel_id";
        }

        $query .= " AND whitelabel_lottery.is_enabled = 1
            AND current_jackpot IS NOT NULL 
            AND current_jackpot != 0 
            ORDER BY FIELD(lottery.id, :order) DESC, lottery.id ASC";

        $db = DB::query($query);
        if (!empty($whitelabel_id)) {
            $db->param(":whitelabel_id", $whitelabel_id);
        }
        $db->param(":order", DB::expr(implode(',', array_reverse(array_values($order)))));

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
                $now = new DateTime("now", new DateTimeZone("UTC"));
                foreach ($lotteries as $lottery) {
                    $next = new DateTime($lottery['next_date_utc'], new DateTimeZone("UTC"));
                    if ($next < $now) {
                        throw new \CacheExpiredException();
                    }
                }
            } catch (\CacheNotFoundException $e) { // catching CacheExpired too
                $lotteries = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $lotteries = $db->execute()->as_array();
        }

        return $lotteries;
    }

    /**
     *
     * @param int $whitelabel_id
     * @param string $order_by
     * @param string $order
     * @return array
     * @throws \CacheExpiredException
     */
    public static function get_lotteries_by_order_for_whitelabel(
        int $whitelabel_id,
        string $order_by,
        string $order
    ): array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $expiredTime = Helpers_Whitelabel::get_expired_time();
        $key = self::$cache_list[5];
        if (!empty($whitelabel_id)) {
            $key .= '.' . $whitelabel_id;
        }
        $key .= $order_by . $order;

        $query = "SELECT 
            lottery.*, 
            currency.code AS currency, 
            whitelabel_lottery.is_enabled AS wis_enabled, 
            lottery_provider.id AS lp_id, 
            provider, 
            min_bets, 
            max_bets,
            multiplier, 
            closing_time, 
            closing_times, 
            lottery_provider.timezone AS lp_timezone, 
            lottery_provider.offset AS lp_offset, 
            lottery_provider.data,
            fc.code AS force_currency
        FROM lottery 
        JOIN whitelabel_lottery ON whitelabel_lottery.lottery_id = lottery.id 
        JOIN whitelabel ON whitelabel.id = whitelabel_lottery.whitelabel_id 
        JOIN currency ON currency.id = lottery.currency_id 
        LEFT JOIN currency fc ON fc.id = lottery.force_currency_id 
        JOIN lottery_provider ON lottery_provider.id = whitelabel_lottery.lottery_provider_id 
        WHERE lottery.is_enabled = 1 ";

        if (!empty($whitelabel_id)) {
            $query .= " AND whitelabel_lottery.whitelabel_id = :whitelabel_id ";
        }

        $query .= " AND whitelabel_lottery.is_enabled = 1 
        AND next_date_utc >= NOW() 
        AND current_jackpot != 0 
        ORDER BY :order_by :order";

        $db = DB::query($query);

        if (!empty($whitelabel_id)) {
            $db->param(":whitelabel_id", $whitelabel_id);
        }

        $db->param(":order_by", DB::expr($order_by));
        $db->param(":order", DB::expr($order));

        try {
            try {
                $lotteries = Lotto_Helper::get_cache($key);
                $now = new DateTime("now", new DateTimeZone("UTC"));
                
                foreach ($lotteries as $lottery) {
                    $next = new DateTime(
                        $lottery['next_date_utc'],
                        new DateTimeZone("UTC")
                    );
                    if ($next < $now) {
                        throw new \CacheExpiredException();
                    }
                }
            } catch (\CacheNotFoundException $e) { // catching CacheExpired too
                $lotteries = $db->execute()->as_array();
                Lotto_Helper::set_cache($key, $lotteries, $expiredTime);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
            $lotteries = $db->execute()->as_array();
        }

        return $lotteries;
    }

    /**
     * @param array $whitelabel
     * @param string $domain
     * @param string $username
     * @return null|array
     */
    public static function exist_domain_username($whitelabel, $domain, $username)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel 
            WHERE 1=1 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND id != :whitelabel_id";
        }

        $query .= " AND (domain = :domain OR 
            username = :username)";

        try {
            $db = DB::query($query);
            $db->param(":domain", $domain);
            $db->param(":username", $username);

            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
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
     * @param string $domain
     * @param string $username
     * @param string $prefix
     * @return null|array
     */
    public static function exist_domain_username_prefix($domain, $username, $prefix)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel 
            WHERE domain = :domain 
                OR username = :username 
                OR prefix = :prefix";

        try {
            $db = DB::query($query);
            $db->param(":domain", $domain);
            $db->param(":username", $username);
            $db->param(":prefix", $prefix);
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
     * @param int $user_token
     * @return null|string
     */
    public static function get_domain_for_user($user_token):? string
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                whitelabel.domain 
            FROM whitelabel 
            INNER JOIN whitelabel_user ON whitelabel.id = whitelabel_user.whitelabel_id 
            WHERE whitelabel_user.token = :token 
            LIMIT 1";

        $db = DB::query($query);
        $db->param(":token", $user_token);

        try {
            $res = $db->execute();
            if ($res == null) {
                return $result;
            }

            $result = $res[0]["domain"];
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     * @param array $whitelabel
     * @return int
     */
    public static function count_active_users($whitelabel = [])
    {
        $add = " AND is_active = 1";
        $deleted_param = 0;

        if (!empty($whitelabel) && isset($whitelabel['user_activation_type']) &&
            (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED
        ) {
            $add .= " AND is_confirmed = 1";
        }
        
        $res = Model_Whitelabel_User::get_filtered_data_count(
            $whitelabel,
            [],
            $deleted_param,
            $add
        );
        
        if (is_null($res)) {
            return -1;
        }
        $allcount = $res[0]['count'];

        return $allcount;
    }

    /**
     *
     * @param array $whitelabel
     * @return int
     */
    public static function count_inactive_users($whitelabel = [])
    {
        $add = " AND (is_active = 0";
        $deleted_param = 0;

        if (!empty($whitelabel) && isset($whitelabel['user_activation_type']) &&
            (int)$whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED
        ) {
            $add .= " OR is_confirmed = 0";
        }
        $add .= ")";
        
        $res = Model_Whitelabel_User::get_filtered_data_count(
            $whitelabel,
            [],
            $deleted_param,
            $add
        );
        
        if (is_null($res)) {
            return -1;
        }
        $inactive = $res[0]['count'];

        return $inactive;
    }

    /**
     *
     * @param array $whitelabel
     * @return int
     */
    public static function count_deleted_users($whitelabel = [])
    {
        $add = "";
        $deleted_param = 1;

        $res = Model_Whitelabel_User::get_filtered_data_count(
            $whitelabel,
            [],
            $deleted_param,
            $add
        );
        
        if (is_null($res)) {
            return -1;
        }
        
        $deleted = $res[0]['count'];

        return $deleted;
    }
    
    /**
     *
     * @param int $id
     * @return null|array
     */
    public static function get_single_by_id(int $id = null):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            whitelabel.*
        FROM whitelabel 
        WHERE 1=1 ";
        
        if (!empty($id)) {
            $query .= " AND id = :id ";
        }
            
        $query .= "LIMIT 1";
        
        try {
            $db = DB::query($query);
            
            if (!empty($id)) {
                $db->param(":id", $id);
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
     * Get all payment types report
     *
     * @param int $whitelabel_id
     * @param string $date_start
     * @param string $date_end
     * @param int $type Default is purchase 0 (Purchase), 1 - Deposit
     * @param string $language Could be null
     * @param string $country Could be null
     * @return array
     */
    public static function get_payment_types_report(
        int $whitelabel_id,
        string $date_start,
        string $date_end,
        int $type = Helpers_General::TYPE_TRANSACTION_PURCHASE,
        string $language = null,
        string $country = null
    ) {
        $additional_query = '';
        
        /* CHECK IF THERE ARE SOME FILTERS AND BUILD ADDITIONAL QUERY */
        if (!empty($language) || !empty($country)) {
            $additional_query .= " INNER JOIN `whitelabel_user` 
                ON `whitelabel_transaction`.`whitelabel_user_id` = `whitelabel_user`.`id`";
        }
        
        /* ADD LANGUAGE FILTER IF EXISTS */
        if (!empty($language)) {
            $additional_query .= " AND `whitelabel_user`.`language_id` = :language";
        }
        
        /* ADD COUNTRY FILTER IF EXISTS */
        if (!empty($country)) {
            $additional_query .= " AND `whitelabel_user`.`country` = :country";
        }
        
        $query = "
            SELECT
                '" . Helpers_General::PAYMENT_TYPE_OTHER . "' AS method,
                `whitelabel_payment_method`.`name` AS payment,
                `payment_method`.`name` AS pname,  
                COALESCE(SUM(`whitelabel_transaction`.`amount_manager`), 0.00) AS amount_manager,
                COALESCE(SUM(`whitelabel_transaction`.`income_manager`), 0.00) AS income_manager,
                COALESCE(SUM(`whitelabel_transaction`.`cost_manager`), 0.00) AS cost_manager,
                COUNT(`whitelabel_transaction`.`id`) AS total 
            FROM `whitelabel_payment_method` 
            INNER JOIN `payment_method` ON `payment_method`.`id` = `whitelabel_payment_method`.`payment_method_id` 
            LEFT JOIN (`whitelabel_transaction`" . $additional_query . ") 
                ON `whitelabel_payment_method`.`id` = `whitelabel_transaction`.`whitelabel_payment_method_id` 
                AND `whitelabel_transaction`.`payment_method_type` = " .
                    Helpers_General::PAYMENT_TYPE_OTHER . " 
                AND `whitelabel_transaction`.`status` = " .
                    Helpers_General::STATUS_TRANSACTION_APPROVED . " 
                AND `whitelabel_transaction`.`type` = " .
                    $type . " 
                AND `whitelabel_transaction`.`whitelabel_id` = :whitelabel_id 
                AND `whitelabel_transaction`.`date` >= :date_start 
                AND `whitelabel_transaction`.`date` <= :date_end
                WHERE `whitelabel_payment_method`.`whitelabel_id` = :whitelabel_id
                GROUP BY `whitelabel_payment_method`.`name`, `payment_method`.`name`   
            UNION
            SELECT
                '" . Helpers_General::PAYMENT_TYPE_CC . "' AS method,
                `whitelabel_cc_method`.`id` AS payment,
                `whitelabel_cc_method`.`id` AS pname,
                COALESCE(SUM(`whitelabel_transaction`.`amount_manager`), 0.00) AS amount_manager,
                COALESCE(SUM(`whitelabel_transaction`.`income_manager`), 0.00) AS income_manager,
                COALESCE(SUM(`whitelabel_transaction`.`cost_manager`), 0.00) AS cost_manager,
                COUNT(`whitelabel_transaction`.`id`) AS total 
            FROM `whitelabel_cc_method`
            LEFT JOIN (`whitelabel_transaction`" . $additional_query . ") 
                ON `whitelabel_cc_method`.`id` = `whitelabel_transaction`.`whitelabel_cc_method_id` 
                AND `whitelabel_transaction`.`payment_method_type` = " .
                    Helpers_General::PAYMENT_TYPE_CC . " 
                AND `whitelabel_transaction`.`status` = " .
                    Helpers_General::STATUS_TRANSACTION_APPROVED . " 
                AND `whitelabel_transaction`.`type` = " .
                    $type . " 
                AND `whitelabel_transaction`.`whitelabel_id` = :whitelabel_id 
                AND `whitelabel_transaction`.`date` >= :date_start 
                AND `whitelabel_transaction`.`date` <= :date_end
                WHERE `whitelabel_cc_method`.`whitelabel_id` = :whitelabel_id
                GROUP BY `whitelabel_cc_method`.`id` ";
        
        if ($type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $query .= "UNION
                SELECT
                    '" . Helpers_General::PAYMENT_TYPE_BALANCE . "' AS method,
                    'Balance' AS payment,
                    '' AS pname,
                    COALESCE(SUM(`whitelabel_transaction`.`amount_manager`), 0.00) AS amount_manager,
                    COALESCE(SUM(`whitelabel_transaction`.`income_manager`), 0.00) AS income_manager,
                    COALESCE(SUM(`whitelabel_transaction`.`cost_manager`), 0.00) AS cost_manager,
                    COUNT(`whitelabel_transaction`.`id`) AS total 
                FROM
                  (`whitelabel_transaction`" . $additional_query . ")
                WHERE `whitelabel_transaction`.`payment_method_type` = " .
                        Helpers_General::PAYMENT_TYPE_BALANCE . " 
                    AND `whitelabel_transaction`.`status` = " .
                        Helpers_General::STATUS_TRANSACTION_APPROVED . " 
                    AND `whitelabel_transaction`.`type` = " .
                        $type . " 
                    AND `whitelabel_transaction`.`whitelabel_id` = :whitelabel_id 
                    AND `whitelabel_transaction`.`date` >= :date_start 
                    AND `whitelabel_transaction`.`date` <= :date_end ";
        }
        
        $query .= "ORDER BY `method` ASC, `payment` ASC";

        $db = DB::query($query);
        
        /*REPLACE PARAMETERS*/
        
        $db->param(":whitelabel_id", $whitelabel_id);
        $db->param(":date_start", $date_start);
        $db->param(":date_end", $date_end);
        
        /* ADD LANGUAGE FILTER VALUE AS A PARAM IF EXISTS */
        if (!empty($language)) {
            $db->param(":language", $language);
        }
        
        /* ADD COUNTRY FILTER VALUE AS A PARAM IF EXISTS */
        if (!empty($country)) {
            $db->param(":country", $country);
        }
        
        /*EXECUTE SQL QUERY*/
        
        $results = $db->execute()->as_array();

        /**
         * Remove method without transaction
         */
        foreach ($results as $key => $value) {
            $transactionNumberPerMethod = $value['total'] ?? '';
            if ($transactionNumberPerMethod === '0') {
                unset($results[$key]);
            }
        }

        return $results;
    }
    
    /**
     * Get all payment types report
     *
     * @param string $date_start
     * @param string $date_end
     * @param int $whitelabel_type whitelabel type could be null
     * @param int $whitelabel_id whitelabel id could be null
     * @param int $type Default is purchase 0 (Purchase), 1 - Deposit
     * @param string $language
     * @param string $country
     * @param bool $is_full_report For full report noting changed
     * @return array
     */
    public static function get_payment_types_admin_report(
        string $date_start,
        string $date_end,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        int $type = Helpers_General::TYPE_TRANSACTION_PURCHASE,
        string $language = null,
        string $country = null,
        bool $is_full_report = false
    ) {
        $additional_query = '';
        
        /* CHECK IF THERE ARE SOME FILTERS AND BUILD ADDITIONAL QUERY */
        if (!empty($language) || !empty($country)) {
            $additional_query .= " INNER JOIN `whitelabel_user` 
                ON `whitelabel_transaction`.`whitelabel_user_id` = `whitelabel_user`.`id`";
        }
        
        /* ADD LANGUAGE FILTER IF EXISTS */
        if (!empty($language)) {
            $additional_query .= " AND `whitelabel_user`.`language_id` = :language";
        }
        
        /* ADD COUNTRY FILTER IF EXISTS */
        if (!empty($country)) {
            $additional_query .= " AND `whitelabel_user`.`country` = :country";
        }
        
        $query_string = "
            SELECT
                '" . Helpers_General::PAYMENT_TYPE_OTHER . "' AS method,
                `whitelabel_payment_method`.`name` AS payment,
                `payment_method`.`name` AS pname, 
                COALESCE(SUM(`whitelabel_transaction`.`amount_manager`), 0.00) AS amount_manager,
                COALESCE(SUM(`whitelabel_transaction`.`amount_usd`), 0.00) AS amount_usd,
                COALESCE(SUM(`whitelabel_transaction`.`income_manager`), 0.00) AS income_manager,
                COALESCE(SUM(`whitelabel_transaction`.`income_usd`), 0.00) AS income_usd,
                COALESCE(SUM(`whitelabel_transaction`.`cost_manager`), 0.00) AS cost_manager,
                COALESCE(SUM(`whitelabel_transaction`.`cost_usd`), 0.00) AS cost_usd,
                COUNT(`whitelabel_transaction`.`id`) AS total 
            FROM `whitelabel_payment_method` 
            INNER JOIN `payment_method` ON `payment_method`.`id` = `whitelabel_payment_method`.`payment_method_id` 
            INNER JOIN `whitelabel` ON `whitelabel`.`id` = `whitelabel_payment_method`.`whitelabel_id` ";
        
        if (!empty($whitelabel_type)) {
            $query_string .= " AND `whitelabel`.`type` = :whitelabel_type ";
        }
        
        $query_string .= " INNER JOIN (`whitelabel_transaction`" . $additional_query . ") 
            ON `whitelabel_payment_method`.`id` = `whitelabel_transaction`.`whitelabel_payment_method_id` 
            AND `whitelabel_transaction`.`payment_method_type` = " .
                Helpers_General::PAYMENT_TYPE_OTHER . " 
            AND `whitelabel_transaction`.`status` = " .
                Helpers_General::STATUS_TRANSACTION_APPROVED . " 
            AND `whitelabel_transaction`.`type` = " .
                $type . " 
            AND `whitelabel_transaction`.`date` >= :date_start 
            AND `whitelabel_transaction`.`date` <= :date_end 
        ";
        
        $query_string .= "WHERE 1=1 ";
        
        if (!empty($whitelabel_id)) {
            $query_string .= " AND `whitelabel`.`id` = :whitelabel_id ";
        }

        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND `whitelabel`.`is_report` = 1 ";
        }
        
        $query_string .= "GROUP BY `whitelabel_payment_method`.`name`, `payment_method`.`name`  
            UNION
            SELECT
                '" . Helpers_General::PAYMENT_TYPE_CC . "' AS method,
                `whitelabel_cc_method`.`id` AS payment,
                `whitelabel_cc_method`.`id` AS pname,
                COALESCE(SUM(`whitelabel_transaction`.`amount_manager`), 0.00) AS amount_manager,
                COALESCE(SUM(`whitelabel_transaction`.`amount_usd`), 0.00) AS amount_usd,
                COALESCE(SUM(`whitelabel_transaction`.`income_manager`), 0.00) AS income_manager,
                COALESCE(SUM(`whitelabel_transaction`.`income_usd`), 0.00) AS income_usd,
                COALESCE(SUM(`whitelabel_transaction`.`cost_manager`), 0.00) AS cost_manager,
                COALESCE(SUM(`whitelabel_transaction`.`cost_usd`), 0.00) AS cost_usd,
                COUNT(`whitelabel_transaction`.`id`) AS total 
            FROM `whitelabel_cc_method` 
            INNER JOIN `whitelabel` ON `whitelabel`.`id` = `whitelabel_cc_method`.`whitelabel_id` ";
        
        if (!empty($whitelabel_type)) {
            $query_string .= " AND `whitelabel`.`type` = :whitelabel_type ";
        }
        
        $query_string .= " INNER JOIN (`whitelabel_transaction`" . $additional_query . ") 
            ON `whitelabel_cc_method`.`id` = `whitelabel_transaction`.`whitelabel_cc_method_id` 
            AND `whitelabel_transaction`.`payment_method_type` = " .
                Helpers_General::PAYMENT_TYPE_CC . " 
            AND `whitelabel_transaction`.`status` = " .
                Helpers_General::STATUS_TRANSACTION_APPROVED . " 
            AND `whitelabel_transaction`.`type` = " .
                $type . " 
            AND `whitelabel_transaction`.`date` >= :date_start 
            AND `whitelabel_transaction`.`date` <= :date_end 
        ";
        
        $query_string .= "WHERE 1=1 ";
        
        if (!empty($whitelabel_id)) {
            $query_string .= " AND `whitelabel`.`id` = :whitelabel_id ";
        }
        
        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND `whitelabel`.`is_report` = 1 ";
        }
        
        $query_string .= "GROUP BY `whitelabel_cc_method`.`id` ";
        
        if ($type === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
            $query_string .= "
                UNION
                SELECT
                    '" . Helpers_General::PAYMENT_TYPE_BALANCE . "' AS method,
                    'Balance' AS payment,
                    '' AS pname,
                    COALESCE(SUM(`whitelabel_transaction`.`amount_manager`), 0.00) AS amount_manager,
                    COALESCE(SUM(`whitelabel_transaction`.`amount_usd`), 0.00) AS amount_usd,
                    COALESCE(SUM(`whitelabel_transaction`.`income_manager`), 0.00) AS income_manager,
                    COALESCE(SUM(`whitelabel_transaction`.`income_usd`), 0.00) AS income_usd,
                    COALESCE(SUM(`whitelabel_transaction`.`cost_manager`), 0.00) AS cost_manager,
                    COALESCE(SUM(`whitelabel_transaction`.`cost_usd`), 0.00) AS cost_usd,
                    COUNT(`whitelabel_transaction`.`id`) AS total 
                FROM
                    (`whitelabel_transaction`" . $additional_query . ") 
                INNER JOIN `whitelabel` ON `whitelabel`.`id` = `whitelabel_transaction`.`whitelabel_id` ";

            if (!empty($whitelabel_type)) {
                $query_string .= " AND `whitelabel`.`type` = :whitelabel_type ";
            }

            $query_string .= "WHERE 1=1 ";

            if (!empty($whitelabel_id)) {
                $query_string .= " AND `whitelabel`.`id` = :whitelabel_id ";
            }

            if (!$is_full_report && is_null($whitelabel_id)) {
                $query_string .= " AND `whitelabel`.`is_report` = 1 ";
            }
        
            $query_string .= " AND `whitelabel_transaction`.`payment_method_type` = " .
                    Helpers_General::PAYMENT_TYPE_BALANCE . " 
                AND `whitelabel_transaction`.`status` = " .
                    Helpers_General::STATUS_TRANSACTION_APPROVED . " 
                AND `whitelabel_transaction`.`type` = " .
                    $type . " 
                AND `whitelabel_transaction`.`date` >= :date_start 
                AND `whitelabel_transaction`.`date` <= :date_end 
                GROUP BY `whitelabel_transaction`.`payment_method_type` ";
        }
        
        $query_string .= "
            ORDER BY `method` ASC, `payment` ASC";
        
        $db = DB::query($query_string);
        
        /*REPLACE PARAMETERS*/
        
        $db->param(":date_start", $date_start);
        $db->param(":date_end", $date_end);
        
        /* ADD LANGUAGE FILTER VALUE AS A PARAM IF EXISTS */
        if (!empty($language)) {
            $db->param(":language", $language);
        }
        
        /* ADD COUNTRY FILTER VALUE AS A PARAM IF EXISTS */
        if (!empty($country)) {
            $db->param(":country", $country);
        }
        
        if (!empty($whitelabel_type)) {
            $db->param(":whitelabel_type", $whitelabel_type);
        }
        
        if (!empty($whitelabel_id)) {
            $db->param(":whitelabel_id", $whitelabel_id);
        }
        
        /*EXECUTE SQL QUERY*/
        
        $result = $db->execute()->as_array();

        return $result;
    }
    
    /**
     *
     * @return array
     */
    public static function get_all_as_short_list(): array
    {
        $query_string = "SELECT 
            id, 
            name 
        FROM whitelabel
        ORDER BY id";
        
        // add non global params
        $params = [];
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     *
     * @return array
     */
    public static function get_all_as_array(): array
    {
        $query_string = "SELECT 
            * 
        FROM whitelabel
        ORDER BY id";
        
        // add non global params
        $params = [];
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     *
     * @access public
     * @param int $id
     * @return bool
     */
    public static function update_last_login_and_last_active($id)
    {
        $result = true;
        $whitelabel = Model_Whitelabel::find_by_pk($id);
        
        if ($whitelabel !== null) {
            $whitelabel->set([
                "last_login" => DB::expr("NOW()"),
                "last_active" => DB::expr("NOW()")
            ]);
            $whitelabel->save();
        } else {
            $result = false;
        }
        
        return $result;
    }

    /**
     *
     * @access public
     * @param int $id
     * @return bool
     */
    public static function update_last_active($id)
    {
        $result = true;
        $whitelabel = Model_Whitelabel::find_by_pk($id);
        
        if ($whitelabel !== null) {
            $whitelabel->set([
                "last_active" => DB::expr("NOW()")
            ]);
            $whitelabel->save();
        } else {
            $result = false;
        }
        
        return $result;
    }

    /**
     *
     */
    public static function get_default_user_groups_ids($whitelabel_id)
    {
        $ids = [];

        $query = DB::select('default_whitelabel_user_group_id')
        ->from('whitelabel')
        ->where('default_whitelabel_user_group_id', '!=', null);

        if ($whitelabel_id) {
            $query->and_where('id', '=', $whitelabel_id);
        }

        $result = $query->execute()
        ->as_array();

        foreach ($result as $res) {
            array_push($ids, $res['default_whitelabel_user_group_id']);
        }

        return $ids;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param int $group_id
     */
    public static function update_default_whitelabel_user_group($whitelabel_id, $group_id)
    {
        $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);
        
        $whitelabel->set([
            "default_whitelabel_user_group_id" => $group_id
        ]);
        $whitelabel->save();
    }
    
    /**
     *
     * @param string $username
     * @return array
     */
    public static function get_by_username_with_default_currency(string $username): array
    {
        return DB::select('wl.*', 'wdc.currency_id')
        ->from(['whitelabel', 'wl'])
        ->join(['whitelabel_default_currency', 'wdc'])
        ->on('wdc.whitelabel_id', '=', 'wl.id')
        ->where('wl.username', '=', $username)
        ->and_where('wdc.is_default_for_site', '=', 1)
        ->execute()
        ->as_array();
    }

    public static function get_by_username(string $username): array
    {
        $whitelabel = [];

        $query = DB::select('id', 'type', 'user_balance_change_limit', 'manager_site_currency_id', 'assert_unique_emails_for_users')
        ->from('whitelabel')
        ->where('username', '=', $username)
        ->limit(1);

        $result = $query->execute()->as_array();

        if (isset($result[0])) {
            $whitelabel = $result[0];
        }

        return $whitelabel;
    }

    public static function update_balance_limit(int $id, string $amount): int
    {
        return DB::update(self::$_table_name)
            ->set([
                'user_balance_change_limit' => $amount
            ])
            ->where('id', '=', $id)
            ->execute();
    }
}
