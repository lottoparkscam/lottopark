<?php

use Services\Logs\FileLoggerService;

class Model_Whitelabel_User_Ticket_Slip extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_ticket_slip';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     *
     * @param int $id
     * @return null|array
     */
    public static function get_ticket_id_for_slip($id)
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
                wut.whitelabel_id, 
                wut.whitelabel_user_id, 
                wuts.ticket_scan_url 
            FROM whitelabel_user_ticket_slip wuts 
            JOIN whitelabel_user_ticket wut ON wut.id = wuts.whitelabel_user_ticket_id 
            WHERE 1=1";
        
        if (!empty($id)) {
            $query .= " AND wuts.id = :slip";
        }
        
        try {
            $db = DB::query($query);

            if (!empty($id)) {
                $db->param(":slip", $id);
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
}
