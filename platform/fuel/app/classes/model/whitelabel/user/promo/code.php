<?php

use Carbon\Carbon;

class Model_Whitelabel_User_Promo_Code extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_user_promo_code';

    /**
     *
     * @param int $transaction_id
     * @return bool
     */
    public static function is_free_ticket_transaction($transaction_id): bool
    {
        $is_free_ticket = false;

        $query = DB::select('wc.bonus_type', 'wc.lottery_id')
        ->from(['whitelabel_user_promo_code', 'wupc'])
        ->join(['whitelabel_promo_code', 'wpc'])->on('wupc.whitelabel_promo_code_id', '=', 'wpc.id')
        ->join(['whitelabel_campaign', 'wc'])->on('wpc.whitelabel_campaign_id', '=', 'wc.id')
        ->where('wupc.whitelabel_transaction_id', '=', $transaction_id)
        ->and_where('wc.is_active', '=', 1)
        ->and_where('wc.date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('wc.date_end', '>=', DB::expr('CURDATE()'))
        ->limit(1);

        $res = $query->execute()->as_array();

        if (isset($res[0])) {
            if (((int)$res[0]['bonus_type'] === 0) && ($res[0]['lottery_id'] != null)) {
                $is_free_ticket = true;
            }
        }

        return $is_free_ticket;
    }

    public static function set_bonus_used(int $code_id, int $transaction_id, int $user_id, int $type = null): bool
    {
        $query = DB::update('whitelabel_user_promo_code')
        ->value('whitelabel_transaction_id', $transaction_id)
        ->value('type', $type)
        ->value('used_at', Carbon::now())
        ->where('whitelabel_promo_code_id', $code_id)
        ->and_where('whitelabel_user_id', $user_id)
        ->execute();

        return true;
    }

    /**
     *
     * @access public
     * @param int $user_id
     * @param int $code_id
     * @return bool
     */
    public static function is_code_used_by_user($user_id, $code_id): bool
    {
        $query = DB::select('*')
        ->from('whitelabel_user_promo_code')
        ->where('whitelabel_promo_code_id', $code_id)
        ->and_where('whitelabel_user_id', $user_id);

        $res = $query->execute()->as_array();

        if (isset($res[0])) {
            return true;
        }

        return false;
    }
}
