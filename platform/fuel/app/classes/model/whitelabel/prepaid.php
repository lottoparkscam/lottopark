<?php

use Fuel\Core\Pagination;

class Model_Whitelabel_Prepaid extends \Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_prepaid';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Fetch count of prepaids for whitelabel
     *
     * @param int $whitelabel_id whitelabel id
     * @param Pagination $pagination
     * @return array count of the payout for specified whitelabel.
     */
    public static function fetch_for_whitelabel(
        int $whitelabel_id,
        Pagination $pagination
    ): array {
        $params = [];
        
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];
        
        $query_string = "SELECT 
            wp.*,
            wt.id AS transaction_id,
            wt.token AS transaction_token 
        FROM whitelabel_prepaid wp 
        LEFT JOIN whitelabel_transaction wt ON wp.whitelabel_transaction_id = wt.id 
        WHERE wp.whitelabel_id = :whitelabel_id
        ORDER BY wp.date DESC 
        LIMIT :offset, :limit";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     *
     * @param int $whitelabel_id
     * @return mixed
     */
    public static function get_sum_by_whitelabel(int $whitelabel_id)
    {
        // add non global params
        $params = [];
        $params[] = [":whitelabel_id", $whitelabel_id];
            
        $query_string = "SELECT 
        SUM(`amount`) AS sum_amount
        FROM whitelabel_prepaid
        WHERE whitelabel_id = :whitelabel_id";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        
        $defaults = 0;
        
        // safely retrieve value
        return parent::get_array_result_item($result, $defaults, 0, "sum_amount");
    }

    public static function get_whitelabels_under_limit(): array
    {
        $queryString = "
            SELECT 
                wl.id,
                wl.name,
                wl.prepaid,
                wl.prepaid_alert_limit,
                wl.email,
                c.code AS manager_currency_code
            FROM whitelabel wl 
            INNER JOIN currency c ON wl.manager_site_currency_id = c.id 
            WHERE wl.type = " . Helpers_General::WHITELABEL_TYPE_V2 . "
            AND wl.prepaid < prepaid_alert_limit
            AND wl.is_active = 1
            ORDER BY wl.id
        ";

        $result = parent::execute_query($queryString, []);

        return parent::get_array_result($result, []);
    }
}
