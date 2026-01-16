<?php

use Services\Logs\FileLoggerService;

class Model_Emerchantpay_User_CC extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'emerchant_user_cc';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     *
     * @param array $user
     * @return null|array
     */
    public static function get_full_data_for_user_rodo($user):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT * 
            FROM emerchant_user_cc 
            WHERE 1=1 ";
        
        if (!empty($user) && !empty($user['id'])) {
            $query .= " AND emerchant_user_cc.whitelabel_user_id = :user_id";
        }
        
        $query .= " ORDER BY emerchant_user_cc.id";
        
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
     * @param int $user_id
     * @return null|int
     */
    public static function update_last_used($user_id):? int
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "UPDATE emerchant_user_cc 
            SET is_lastused = 0 
            WHERE whitelabel_user_id = :userid";

        try {
            $db = DB::query($query);
            
            if (!empty($user_id)) { // This should be set
                $db->param(":userid", $user_id);
            }
            
            $result = $db->execute();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }
        
        return $result;
    }
}
