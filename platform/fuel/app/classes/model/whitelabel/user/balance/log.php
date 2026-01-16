<?php

class Model_Whitelabel_User_Balance_Log extends Model_Model
{
    const USER_BALANCE_LOG_STATUS_SUCCESS = 0;
    const USER_BALANCE_LOG_STATUS_FAILURE = 1;
    
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_balance_log';


    /**
     *
     *
     * @param int $whitelabel_user_id
     * @param string $session_datetime
     * @param string $message
     * @param int $level
     * @param int $is_bonus
     * @param float $balance_change
     * @param string $balance_change_currency_code
     * @param float $balance_change_import
     * @param string $balance_change_import_currency_code
     * @return bool
     * @throws Exception
     */
    public static function add_whitelabel_user_balance_log(
        $whitelabel_user_id,
        $session_datetime,
        $message,
        $level,
        $is_bonus,
        $balance_change,
        $balance_change_currency_code,
        $balance_change_import,
        $balance_change_import_currency_code
    ) {
        $is_bonus_flag = 0;
        if ($is_bonus) {
            $is_bonus_flag = 1;
        }

        $log = self::forge();
        $log->set([
            'whitelabel_user_id' => $whitelabel_user_id,
            'created_at' => DB::expr("NOW()"),
            'session_datetime' => $session_datetime,
            'message' => $message,
            'level' => $level,
            'is_bonus' => $is_bonus_flag,
            'balance_change' => $balance_change,
            'balance_change_currency_code' => $balance_change_currency_code,
            'balance_change_import' => $balance_change_import,
            'balance_change_import_currency_code' => $balance_change_import_currency_code
        ]);

        return $log->save();
    }
}
