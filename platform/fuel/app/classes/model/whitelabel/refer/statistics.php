<?php

class Model_Whitelabel_Refer_Statistics extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_refer_statistics';
    
    /**
     *
     * @param int $user_token
     * @param int $whitelabel_id
     * @param bool $is_unique
     * @return void
     */
    public static function add_clicks(int $user_token, int $whitelabel_id, bool $is_unique = true): void
    {
        $entry = Model_Whitelabel_Refer_Statistics::find_one_by([
            'token' => $user_token,
            'whitelabel_id' => $whitelabel_id
        ]);
        
        if (empty($entry)) {
            $user = Model_Whitelabel_User::get_existing_user_by_token($user_token, $whitelabel_id);
            
            if (empty($user)) {
                return;
            }
            
            $entry = Model_Whitelabel_Refer_Statistics::forge([
                'whitelabel_user_id' => $user['id'],
                'token' => $user_token,
                'whitelabel_id' => $whitelabel_id,
                'clicks' => 1,
                'unique_clicks' => 1
            ]);
            $entry->save();
            return;
        }
        
        $entry->clicks++;
        if ($is_unique) {
            $entry->unique_clicks++;
        }
        $entry->save();
    }
    
    /**
     *
     * @param int $user_id
     * @return void
     */
    public static function add_register(int $user_id): void
    {
        $entry = Model_Whitelabel_Refer_Statistics::find_one_by('whitelabel_user_id', $user_id);
        
        if (empty($entry)) {
            return;
        }
        
        $entry->registrations++;
        $entry->save();
    }
    
    /**
     *
     * @param int $user_id
     * @return void
     */
    public static function add_free_tickets(int $user_id): void
    {
        $entry = Model_Whitelabel_Refer_Statistics::find_one_by('whitelabel_user_id', $user_id);
        
        if (empty($entry)) {
            return;
        }
        
        $entry->free_tickets++;
        $entry->save();
    }
}
