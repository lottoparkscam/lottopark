<?php

class Model_Whitelabel_User_Whitelisted_Ip extends Model_Model
{
	protected static $_table_name = 'whitelabel_user_whitelisted_ip';

    /**
     * @access public
     * @param int $user_id
     * @return array
     */
    public static function IP_list_by_user_id($user_id)
    {
        $ip_list = [];

        /** @var object $query */
        $query = DB::select('ip')->from('whitelabel_user_whitelisted_ip')->where('whitelabel_user_id', '=', $user_id);
        $res = $query->execute()->as_array();

        foreach ($res as $row) {
            array_push($ip_list, $row['ip']);
        }
        
        return $ip_list;
    }
}
