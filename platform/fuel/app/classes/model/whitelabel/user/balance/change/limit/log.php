<?php

class Model_Whitelabel_User_Balance_Change_Limit_Log extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_balance_change_limit_log';


    /**
     * @param int $whitelabel_id
     * @param string $change
     */
    public static function add_log(
        $whitelabel_id,
        $change
    ) {
        $log = self::forge();
        $log->set([
            'whitelabel_id' => $whitelabel_id,
            'created_at' => DB::expr("NOW()"),
            'value' => $change
        ]);

        return $log->save();
    }
}
