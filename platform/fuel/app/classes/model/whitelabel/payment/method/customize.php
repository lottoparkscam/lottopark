<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_Payment_Method_Customize extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_payment_method_customize';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
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
            language.id AS language_id,
            language.code 
        FROM whitelabel_payment_method_customize wpmc 
        INNER JOIN whitelabel_language ON wpmc.whitelabel_language_id = whitelabel_language.id 
        INNER JOIN language ON whitelabel_language.language_id = language.id 
        WHERE wpmc.whitelabel_payment_method_id = :whitelabel_payment_method_id 
        ORDER BY wpmc.id ASC";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     *
     * @param int $id
     * @return null|integer
     */
    public static function delete_row(int $id):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        if (empty($id)) {
            $fileLoggerService->error("Lack of id to delete!");
            return $result;
        }
        
        $query = "DELETE FROM 
                whitelabel_payment_method_customize 
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
     * @param int $whitelabel_payment_method_id
     * @param int $language_id
     * @return array|null
     */
    public static function get_full_data(
        int $whitelabel_payment_method_id = null,
        int $language_id = null
    ): ?array {
        $params = [];
        // add non global params
        $params[] = [":whitelabel_payment_method_id", $whitelabel_payment_method_id];
        $params[] = [":language_id", $language_id];
        
        $query_string = "SELECT 
            wpmc.* 
        FROM whitelabel_payment_method_customize wpmc 
        INNER JOIN whitelabel_language wl ON wl.id = wpmc.whitelabel_language_id 
        WHERE wpmc.whitelabel_payment_method_id = :whitelabel_payment_method_id
        AND wl.language_id = :language_id 
        LIMIT 1";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result_row($result, [], 0);
    }
}
