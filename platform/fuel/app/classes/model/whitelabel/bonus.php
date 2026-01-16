<?php

/**
 *
 */
class Model_Whitelabel_Bonus extends \Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_bonus';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Get single row of bonus based on whitelabel ID and bonus ID
     *
     * @param int $whitelabel_id
     * @param int $bonus_id
     * @return null|array
     */
    public static function get_single_row(
        int $whitelabel_id,
        int $bonus_id
    ): ?array {
        // add non global params
        $params = [];
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":bonus_id", $bonus_id];
        
        $query_string = "SELECT 
            whitelabel_bonus.* 
        FROM whitelabel_bonus 
        WHERE whitelabel_bonus.whitelabel_id = :whitelabel_id 
            AND whitelabel_bonus.bonus_id = :bonus_id 
        LIMIT 1";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_row($result, [], 0);
    }
    
    /**
     * Get single row of bonus based on whitelabel ID and bonus ID
     *
     * @param int $whitelabel_id
     * @param int $bonus_id
     * @return array|null
     */
    public static function get_bonus_with_lottery_name_and_timezone(
        int $whitelabel_id,
        int $bonus_id
    ): ?array {
        // add non global params
        $params = [];
        $params[] = [":whitelabel_id", $whitelabel_id];
        $params[] = [":bonus_id", $bonus_id];
        
        $query_string = "SELECT 
            wb.*,
            l.id AS lottery_id,
            l.name AS lottery_name,
            l.timezone AS lottery_timezone
        FROM whitelabel_bonus wb 
        INNER JOIN lottery l ON wb.purchase_lottery_id = l.id 
        LEFT JOIN whitelabel_lottery wl ON wl.lottery_id = l.id
        WHERE
            l.is_enabled = 1
            AND l.is_temporarily_disabled = 0 
            AND wl.is_enabled = 1 
            AND wb.whitelabel_id = :whitelabel_id 
            AND wb.bonus_id = :bonus_id 
        LIMIT 1";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_row($result, [], 0);
    }
}
