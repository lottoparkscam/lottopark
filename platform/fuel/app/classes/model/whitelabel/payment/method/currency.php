<?php

/**
 * Description of Model_Whitelabel_Payment_Method_Currency
 *
 */
class Model_Whitelabel_Payment_Method_Currency extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_payment_method_currency';

    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Fetch count of existed entities
     *
     * @param int $whitelabel_payment_method_id
     * @param int $currency_id If null it will fetch all
     * @return int count of existed entities
     */
    public static function fetch_count_by_whitelabel_payment_method_id(
        int $whitelabel_payment_method_id,
        int $currency_id = null
    ): int {
        $params = [];
        // add non global params
        $params[] = [":whitelabel_payment_method_id", $whitelabel_payment_method_id];
        
        if (!empty($currency_id)) {
            $params[] = [":currency_id", $currency_id];
        }
        
        $query_string = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_payment_method_currency
        WHERE whitelabel_payment_method_id = :whitelabel_payment_method_id 
        AND is_enabled = 1 ";
        
        if (!empty($currency_id)) {
            $query_string .= " AND currency_id = :currency_id ";
        }
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     * Fetch full data for whitelabel_payment_method.
     *
     * @param int $whitelabel_payment_method_id whitelabel payment method id
     * @return array consists of the data of currencies which are enabled.
     */
    public static function get_all_for_whitelabel_payment_method(
        int $whitelabel_payment_method_id
    ):? array {
        $params = [];
        // add non global params
        $params[] = [":whitelabel_payment_method_id", $whitelabel_payment_method_id];
        
        $query_string = "SELECT 
            wpmc.*,
            payment_currency.id AS payment_currency_id,
            payment_currency.code AS payment_currency_code,
            payment_currency.rate AS payment_currency_rate 
        FROM whitelabel_payment_method_currency wpmc 
        INNER JOIN currency payment_currency ON wpmc.currency_id = payment_currency.id 
        WHERE wpmc.whitelabel_payment_method_id = :whitelabel_payment_method_id  
            AND wpmc.is_enabled = 1 
        ORDER BY wpmc.is_default DESC";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch single row for whitelabel_payment_method and user_currency and
     * if don't exist return default currency.
     *
     * @param int $whitelabel_id whitelabel id
     * @param int $whitelabel_payment_method_id payment method id for whitelabel
     * @param int $user_currency_id currency id set for user
     * @return array consists of the data of currencies which are enabled.
     */
    public static function get_single_row_for_whitelabel_payment_id(
        int $whitelabel_id,
        int $whitelabel_payment_method_id = null,
        int $user_currency_id = null
    ):? array {
        if (empty($whitelabel_payment_method_id)) {
            return null;
        }
        
        $params = [];
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":whitelabel_payment_method_id", $whitelabel_payment_method_id];
        if (!empty($user_currency_id)) {
            $params[] = [":user_currency_id", $user_currency_id];
        }
        
        $query_string = "";
        
        if (empty($user_currency_id)) {
            $query_string = "SELECT 
                    payment_currency.id,
                    payment_currency.code,
                    payment_currency.rate 
                FROM whitelabel_payment_method_currency wpmc 
                INNER JOIN currency payment_currency ON wpmc.currency_id = payment_currency.id 
                INNER JOIN whitelabel_payment_method wpm ON wpm.id = wpmc.whitelabel_payment_method_id 
                WHERE wpm.whitelabel_id = :whitelabel_id 
                    AND wpm.id = :whitelabel_payment_method_id 
                    AND wpmc.is_enabled = 1 
                    AND wpmc.is_default = 1
            LIMIT 1";
        } else {
            $query_string = "SELECT 
                    payment_currency.id,
                    payment_currency.code,
                    payment_currency.rate 
                FROM whitelabel_payment_method_currency wpmc 
                INNER JOIN currency payment_currency ON wpmc.currency_id = payment_currency.id 
                INNER JOIN whitelabel_payment_method wpm ON wpm.id = wpmc.whitelabel_payment_method_id 
                WHERE wpm.whitelabel_id = :whitelabel_id 
                    AND wpm.id = :whitelabel_payment_method_id 
                    AND wpmc.is_enabled = 1 
                    AND wpmc.currency_id = :user_currency_id 
            UNION ALL 
                SELECT 
                    payment_currency.id,
                    payment_currency.code,
                    payment_currency.rate 
                FROM whitelabel_payment_method_currency wpmc 
                INNER JOIN currency payment_currency ON wpmc.currency_id = payment_currency.id 
                INNER JOIN whitelabel_payment_method wpm ON wpm.id = wpmc.whitelabel_payment_method_id 
                WHERE wpm.whitelabel_id = :whitelabel_id 
                    AND wpm.id = :whitelabel_payment_method_id 
                    AND wpmc.is_enabled = 1 
                    AND wpmc.is_default = 1
            LIMIT 1";
        }
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     *
     * @param int $whitelabel_payment_method_id
     * @param int $currency_id
     * @return int
     */
    public static function get_zero_decimal_value(
        int $whitelabel_payment_method_id,
        int $currency_id
    ): int {
        $params = [];
        $params[] = [
            ":whitelabel_payment_method_id", $whitelabel_payment_method_id
        ];
        $params[] = [
            ":currency_id", $currency_id
        ];
        
        $query_string = "SELECT 
            is_zero_decimal 
        FROM whitelabel_payment_method_currency 
        WHERE whitelabel_payment_method_id = :whitelabel_payment_method_id 
            AND currency_id = :currency_id ";
        
        $result = parent::execute_query($query_string, $params);
        
        return parent::get_array_result_item($result, 0, 0, 'is_zero_decimal');
    }
}
