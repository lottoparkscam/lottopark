<?php

trait Traits_Checks_Block_IP_White
{
    /**
     * Check if user IP is allowed based on the user IP whitelist.
     * @param int $user_id
     * @return bool
     */
    private function is_ip_allowed($user_id): bool
    {
        $user_IP = Lotto_Security::get_IP();
        $list = Model_Whitelabel_User_Whitelisted_Ip::IP_list_by_user_id($user_id);

        if ((count($list) > 0) && (!in_array(Lotto_Security::get_IP(), $list))) {
            return false;
        }
        
        return true;
    }
}