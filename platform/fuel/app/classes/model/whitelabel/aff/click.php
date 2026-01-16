<?php

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_aff_click.
 */
class Model_Whitelabel_Aff_Click extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_click';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $user_id
     * @param int $parent_id
     * @return array
     */
    public static function fetch_clicks(
        array $add,
        array $params,
        int $user_id = null,
        int $parent_id = null
    ): array {
        // add non global params
        $user_add = '';
        if (!empty($user_id)) {
            $params[] = [":user_id", $user_id];
            $user_add = " AND whitelabel_aff_id = :user_id ";
        }
        $parent_add = '';
        if (!empty($parent_id)) {
            $params[] = [":parent_id", $parent_id];
            $parent_add = " AND whitelabel_aff.whitelabel_aff_parent_id = :parent_id ";
        }

        $query_string = "SELECT 
            COALESCE(SUM(`all`), 0) AS count_all, 
            COALESCE(SUM(`unique`), 0) AS count_unique 
        FROM whitelabel_aff_click 
        JOIN whitelabel_aff ON whitelabel_aff_click.whitelabel_aff_id = whitelabel_aff.id 
        WHERE 1=1 " .
            $user_add .
            $parent_add .
            implode(" ", $add) . " 
            AND date >= :date_start 
            AND date <= :date_end ";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        
        $defaults = [
            "count_all" => 0,
            "count_unique" => 0
        ];
        
        // safely retrieve value
        return parent::get_array_result_row($result, $defaults, 0);
    }
    
    /**
     *
     * @param array $add
     * @param array $params
     * @param int $whitelabel_id
     * @return array
     */
    public static function fetch_clicks_for_whitelabel(
        array $add,
        array $params,
        int $whitelabel_id
    ): array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        
        $query_string = "SELECT 
            SUM(`all`) AS count_all, 
            SUM(`unique`) AS count_unique 
        FROM whitelabel_aff_click 
        LEFT JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_aff_click.whitelabel_aff_id 
        WHERE whitelabel_aff.whitelabel_id = :whitelabel_id 
        AND whitelabel_aff.is_deleted = 0 
            AND whitelabel_aff.is_active = 1 
            AND whitelabel_aff.is_accepted = 1 "
        . implode(" ", $add) . " 
        AND date >= :date_start 
        AND date <= :date_end ";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        
        $defaults = [
            "count_all" => 0,
            "count_unique" => 0
        ];
        // safely retrieve value
        return parent::get_array_result_row($result, $defaults, 0);
    }
}
