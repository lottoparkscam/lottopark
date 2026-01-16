<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Country_Currency extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_country_currency';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     *
     * @param array $whitelabel
     * @return null|array
     */
    public static function get_all_by_whitelabel($whitelabel)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                wcc.id,
                wcc.country_code,
                c.code AS currency_code 
            FROM whitelabel_country_currency wcc
            LEFT JOIN whitelabel_default_currency wdc ON wcc.whitelabel_default_currency_id = wdc.id
            INNER JOIN currency c ON wdc.currency_id = c.id
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wcc.whitelabel_id = :whitelabel_id";
        }
        
        $query .= "ORDER BY wcc.country_code";
        
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
     * Check and get currency row for whitelabel by country code
     *
     * @param array $whitelabel
     * @param string $country_code
     * @return null|int
     */
    public static function get_for_whitelabel_and_country($whitelabel, $country_code)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $res = null;
        $query = "SELECT 
                wcc.id AS wccid,
                wcc.country_code,
                wdc.*,
                c.code AS currency_code 
            FROM whitelabel_country_currency wcc
            LEFT JOIN whitelabel_default_currency wdc ON wcc.whitelabel_default_currency_id = wdc.id
            INNER JOIN currency c ON wdc.currency_id = c.id
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND wcc.whitelabel_id = :whitelabel_id";
        }
        
        if (!empty($country_code)) {
            $query .= " AND wcc.country_code = :country_code";
        }
        
        $query .= " LIMIT 1";
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }
            
            if (!empty($country_code)) {
                $db->param(":country_code", $country_code);
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
     * @param int $id
     * @return null|integer
     */
    public static function delete_row($id)
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
                whitelabel_country_currency 
            WHERE id = :id";
        
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
     *
     * @param int $default_currency_id
     * @return null|integer
     */
    public static function delete_row_by_default_currency_id($default_currency_id)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        if (empty($default_currency_id)) {
            $fileLoggerService->error(
                "Lack of default_currency_id to delete!"
            );
            return $result;
        }
        
        $query = "DELETE FROM 
                whitelabel_country_currency 
            WHERE whitelabel_default_currency_id = :whitelabel_default_currency_id ";
        
        try {
            $db = DB::query($query);
            $db->param(":whitelabel_default_currency_id", $default_currency_id);
            $result = $db->execute();
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
     * @return null|integer
     */
    public static function delete_all_for_whitelabel($whitelabel)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        if (empty($whitelabel)) {
            $fileLoggerService->error(
                "Lack of id to delete!"
            );
            return $result;
        }
        
        $query = "DELETE FROM 
                whitelabel_country_currency 
            WHERE whitelabel_id = :whitelabel_id";
        
        try {
            $db = DB::query($query);
            $db->param(":whitelabel_id", $whitelabel['id']);
            $result = $db->execute();
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
     * @param string $country_code
     * @param int|null $whitelabel_default_currency_id
     * @return null|int
     */
    public static function count_for_whitelabel_by_countrycode(
        $whitelabel,
        $country_code,
        $whitelabel_default_currency_id = null
    ) {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                COUNT(*) AS count 
            FROM whitelabel_country_currency 
            WHERE 1=1 ";
        
        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND whitelabel_id = :whitelabel_id ";
        }
        
        if (!empty($country_code)) {
            $query .= " AND country_code = :country_code";
        }
        
        if (isset($whitelabel_default_currency_id) &&
            intval($whitelabel_default_currency_id) > 0
        ) {
            $query .= " AND whitelabel_default_currency_id = :whitelabel_default_currency_id";
        }
        
        try {
            $db = DB::query($query);
            
            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }
            
            if (!empty($country_code)) {
                $db->param(":country_code", $country_code);
            }
            
            if (isset($whitelabel_default_currency_id) &&
                intval($whitelabel_default_currency_id) > 0
            ) {
                $db->param(":whitelabel_default_currency_id", $whitelabel_default_currency_id);
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
}
