<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Default_Currency extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_default_currency';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Get all defined currencies for given whitelabel
     *
     * @param array $whitelabel
     * @param string $order_by
     * @return array
     */
    public static function get_all_by_whitelabel($whitelabel, $order_by = ""):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            wdc.id,
            wdc.currency_id,
            wdc.is_default_for_site,
            c.code AS currency_code,
            wdc.default_deposit_first_box AS first_box,
            wdc.default_deposit_second_box AS second_box,
            wdc.default_deposit_third_box AS third_box,
            wdc.min_purchase_amount,
            wdc.min_deposit_amount,
            wdc.min_withdrawal,
            wdc.max_order_amount,
            wdc.max_deposit_amount 
        FROM whitelabel_default_currency wdc
        INNER JOIN currency c ON wdc.currency_id = c.id
        WHERE 1=1 AND is_visible = true ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wdc.whitelabel_id = :whitelabel_id";
        }
        
        if (empty($order_by)) {
            $query .= " ORDER BY wdc.is_default_for_site DESC, c.code";
        } else {
            $query .= " ORDER BY " . $order_by;
        }
        
        try {
            $db = DB::query($query);
            
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
     * Get default site currency row for whitelabel
     *
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_default_for_whitelabel($whitelabel):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                wdc.*,
                c.code AS currency_code
            FROM whitelabel_default_currency wdc 
            INNER JOIN currency c ON wdc.currency_id = c.id
            WHERE wdc.is_default_for_site = 1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wdc.whitelabel_id = :whitelabel_id";
        }
        
        $query .= " LIMIT 1";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }

            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        if (isset($res[0])) {
            $result = $res[0];
        }
        
        return $result;
    }

    /**
     *
     * @param int $id
     * @return null|array
     */
    public static function get_row_with_currency_data($id):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            wdc.id,
            wdc.currency_id,
            wdc.is_default_for_site,
            wdc.default_deposit_first_box,
            wdc.default_deposit_second_box,
            wdc.default_deposit_third_box,
            wdc.min_purchase_amount,
            wdc.min_deposit_amount,
            wdc.min_withdrawal,
            wdc.max_order_amount, 
            wdc.max_deposit_amount, 
            c.code AS currency_code,
            c.rate
        FROM whitelabel_default_currency wdc
        INNER JOIN currency c ON wdc.currency_id = c.id
        WHERE 1=1 ";
        
        if (!empty($id)) {
            $query .= "AND wdc.id = :id";
        }
        
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
        
        if (isset($res[0])) {
            $result = $res[0];
        }
        
        return $result;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param int $currency_id
     * @return null|int
     */
    public static function count_for_whitelabel($whitelabel, $currency_id):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_default_currency 
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id ";
        }
        
        if (!empty($currency_id)) {
            $query .= " AND currency_id = :currency_id";
        }
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($currency_id)) {
                $db->param(":currency_id", $currency_id);
            }
            
            $res = $db->execute()->as_array();
            
            if (isset($res[0]) && isset($res[0]['count'])) {
                $result = intval($res[0]['count']);
            }
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        return $result;
    }
    
    /**
     *
     * @param int $id
     * @return null|integer
     */
    public static function delete_row($id):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        if (empty($id)) {
            $fileLoggerService->error(
                "Lack of id to delete!"
            );
            return $result;
        }
        
        $query = "DELETE FROM 
                whitelabel_default_currency 
            WHERE id = :id 
            AND is_default_for_site != 1";
        
        try {
            $db = DB::query($query);
            $db->param(":id", $id);
            $result = $db->execute();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        return $result;
    }
    
    /**
     * Get row of defined deposits boxes (at this moment)
     * based on whitelabel and currency_id pulled from user data
     *
     * @param array $whitelabel
     * @param int $currency_id
     * @param boolean $default_for_site
     * @return null|array
     */
    public static function get_for_user(
        $whitelabel,
        $currency_id,
        $default_for_site = false
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
            wdc.id,
            wdc.is_default_for_site,
            wdc.currency_id,
            c.code AS currency_code,
            wdc.default_deposit_first_box,
            wdc.default_deposit_second_box,
            wdc.default_deposit_third_box,
            wdc.min_purchase_amount,
            wdc.min_deposit_amount,
            wdc.min_withdrawal,
            wdc.max_order_amount,
            wdc.max_deposit_amount 
        FROM whitelabel_default_currency wdc
        INNER JOIN currency c ON wdc.currency_id = c.id
        WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wdc.whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($currency_id)) {
            $query .= " AND wdc.currency_id = :currency_id";
        }
        
        if ($default_for_site) {
            $query .= " AND is_default_for_site = 1";
        }
        
        $query .= " LIMIT 1";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }
            
            if (!empty($currency_id)) {
                $db->param(":currency_id", $currency_id);
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
}
