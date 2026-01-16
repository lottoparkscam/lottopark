<?php

/**
 * @deprecated
 */
class Model_Whitelabel_Aff_Payout extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_payout';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Prepare query string for count
     *
     * @return string
     */
    private static function get_count_query(): string
    {
        $query = "SELECT 
            COUNT(*) AS count 
        FROM whitelabel_aff_payout wap ";
        
        return $query;
    }

    /**
     * Fetch count of payout for wl.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @return int count of the payout for specified whitelabel.
     */
    public static function count_for_whitelabel_filtered(
        array $add,
        array $params,
        int $whitelabel_id
    ): int {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        
        $query_string = self::get_count_query() .
        "WHERE wap.whitelabel_id = :whitelabel_id " .
        implode(" ", $add);
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     * Fetch count of payout for user.
     *
     * @param int $whitelabel_aff_id user id
     * @return int count of the payout for specified whitelabel.
     */
    public static function count_for_user(int $whitelabel_aff_id): int
    {
        // add non global params
        $params = [];
        $params[] = [":whitelabel_aff_id", $whitelabel_aff_id];
        
        $query_string = self::get_count_query() .
        "WHERE wap.whitelabel_aff_id = :whitelabel_aff_id ";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     * Prepare main query of payouts with manager and affiliate
     * payout currency data returned within
     *
     * @return string
     */
    private static function get_data_query(): string
    {
        $query = "SELECT 
            wap.*,  
            aff_payout_currency.id AS aff_payout_currency_id,
            aff_payout_currency.code AS aff_payout_currency_code,
            aff_payout_currency.rate AS aff_payout_currency_rate,
            manager_currency.id AS manager_currency_id,
            manager_currency.code AS manager_currency_code,
            manager_currency.rate AS manager_currency_rate 
        FROM whitelabel_aff_payout wap
        INNER JOIN whitelabel wl ON wl.id = wap.whitelabel_id 
        INNER JOIN currency manager_currency ON manager_currency.id = wl.manager_site_currency_id 
        INNER JOIN currency aff_payout_currency ON aff_payout_currency.id = wap.currency_id ";
        
        return $query;
    }
    
    /**
     * Fetch payouts for whitelabel.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param object $pagination object.
     * @param int $whitelabel_id whitelabel id
     * @return array array of payouts for whitelabel.
     */
    public static function get_for_whitelabel_filtered(
        array $add,
        array $params,
        $pagination,
        int $whitelabel_id
    ): array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];
        
        $query_string = "SELECT 
            wap.*,  
            aff_payout_currency.id AS aff_payout_currency_id,
            aff_payout_currency.code AS aff_payout_currency_code,
            aff_payout_currency.rate AS aff_payout_currency_rate,
            manager_currency.id AS manager_currency_id,
            manager_currency.code AS manager_currency_code,
            manager_currency.rate AS manager_currency_rate,
            wa.email,
            wa.token,
            wa.name,
            wa.surname,
            wa.login,
            wa.is_active,
            wa.is_confirmed,
            wa.is_deleted,
            wa.withdrawal_data,
            wa.whitelabel_aff_withdrawal_id
        FROM whitelabel_aff_payout wap
        INNER JOIN whitelabel wl ON wl.id = wap.whitelabel_id 
        INNER JOIN currency manager_currency ON manager_currency.id = wl.manager_site_currency_id 
        INNER JOIN currency aff_payout_currency ON aff_payout_currency.id = wap.currency_id 
        INNER JOIN whitelabel_aff wa ON wa.id = wap.whitelabel_aff_id
        
        WHERE wap.whitelabel_id = :whitelabel_id " .
        implode(" ", $add) .
        " ORDER BY wap.id DESC 
        LIMIT :offset, :limit";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch payouts for user.
     *
     * @param object $pagination object.
     * @param int $whitelabel_aff_id whitelabel user id
     * @return array array of payouts for user.
     */
    public static function get_for_user($pagination, int $whitelabel_aff_id): array
    {
        // add non global params
        $params[] = [":whitelabel_aff_id", $whitelabel_aff_id];
        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];
        
        $query_string = self::get_data_query() .
        "WHERE wap.whitelabel_aff_id = :whitelabel_aff_id 
        ORDER BY wap.id DESC 
        LIMIT :offset, :limit";
      
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
}
