<?php

class Model_Whitelabel_Promo_Code extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_promo_code';

    /**
     *
     * @access public
     * @param int $token
     * @return array
     */
    public static function get_promo_codes_for_campaign($token): array
    {
        $codes = [];

        $query = DB::select('whitelabel_campaign.prefix', 'whitelabel_promo_code.token')
        ->from('whitelabel_promo_code')
        ->join('whitelabel_campaign')->on('whitelabel_promo_code.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->where('whitelabel_campaign.token', '=', $token);

        $codes = $query->execute()->as_array();

        return $codes;
    }

    /**
     *
     * @access public
     * @param int $campaign_id
     * @return array
     */
    public static function get_promo_codes_count_for_campaign($campaign_id): array
    {
        $count = 0;
        $length = 0;

        $query = DB::select(
            DB::expr('length(token) as length')
        )
        ->from('whitelabel_promo_code')
        ->where('whitelabel_campaign_id', '=', $campaign_id);

        $res = $query->execute()->as_array();

        if (isset($res[0])) {
            $count = DB::count_last_query();
            $length = $res[0]['length'];
        }

        return [
            $count,
            $length
        ];
    }
    
    /**
     *
     * @access public
     * @param int $token
     * @return array
     */
    public static function get_users_promo_codes_for_campaign($token): array
    {
        $codes = [];

        $query = DB::select(
            ['whitelabel_campaign.prefix', 'cprefix'],
            ['whitelabel_promo_code.token', 'ctoken'],
            ['whitelabel_transaction.token', 'ttoken'],
            ['whitelabel_transaction.type', 'ttype'],
            ['whitelabel_user.token', 'utoken'],
            'whitelabel_user.name',
            'whitelabel_user.surname',
            'whitelabel_user.email',
            'whitelabel_user.is_deleted',
            'whitelabel_user.is_active'
        )
        ->from('whitelabel_user_promo_code')
        ->join('whitelabel_promo_code')->on('whitelabel_user_promo_code.whitelabel_promo_code_id', '=', 'whitelabel_promo_code.id')
        ->join('whitelabel_user')->on('whitelabel_user_promo_code.whitelabel_user_id', '=', 'whitelabel_user.id')
        ->join('whitelabel_transaction', 'LEFT')->on('whitelabel_user_promo_code.whitelabel_transaction_id', '=', 'whitelabel_transaction.id')
        ->join('whitelabel_campaign')->on('whitelabel_promo_code.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->where('whitelabel_campaign.token', '=', $token);

        $codes = $query->execute()->as_array();

        return $codes;
    }

    /**
     *
     * @access public
     * @param int $campaign_id
     */
    public static function delete_promo_codes_for_campaign($campaign_id): void
    {
        $query = DB::delete('whitelabel_promo_code')
        ->where('whitelabel_campaign_id', '=', $campaign_id);

        $res = $query->execute();
    }

    /**
     *
     * @access public
     * @param int $code_id
     * @param int $campaign_id
     * @return array
     */
    public static function get_usage_counts($code_id, $campaign_id): array
    {
        $code_used = null;
        $campaign_used = null;

        $code_used_res = DB::select(DB::expr('count(*) as count'))
        ->from('whitelabel_user_promo_code')
        ->where('whitelabel_promo_code_id', '=', $code_id)
        ->execute()->as_array();
        if (isset($code_used_res[0])) {
            $code_used = $code_used_res[0]['count'];
        }

        $campaign_used_res = DB::select(DB::expr('count(*) as count'))
        ->from('whitelabel_user_promo_code')
        ->join('whitelabel_promo_code')->on('whitelabel_user_promo_code.whitelabel_promo_code_id', '=', 'whitelabel_promo_code.id')
        ->join('whitelabel_campaign')->on('whitelabel_promo_code.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->where('whitelabel_campaign.id', '=', $campaign_id)
        ->execute()->as_array();
        if (isset($campaign_used_res[0])) {
            $campaign_used = $campaign_used_res[0]['count'];
        }

        return [
            $code_used,
            $campaign_used
        ];
    }

    /**
     *
     * @access public
     * @param int $campaign_id
     * @param int $user_id
     * @return int|null
     */
    public static function get_user_usage_counts($campaign_id, $user_id):? int
    {
        $user_codes_used = null;

        $user_codes_used_res = DB::select(DB::expr('count(*) as count'))
        ->from('whitelabel_user_promo_code')
        ->join('whitelabel_promo_code')->on('whitelabel_user_promo_code.whitelabel_promo_code_id', '=', 'whitelabel_promo_code.id')
        ->join('whitelabel_campaign')->on('whitelabel_promo_code.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->where('whitelabel_campaign.id', '=', $campaign_id)
        ->and_where('whitelabel_user_promo_code.whitelabel_user_id', '=', $user_id)
        ->execute()->as_array();
        if (isset($user_codes_used_res[0])) {
            $user_codes_used = $user_codes_used_res[0]['count'];
        }

        return $user_codes_used;
    }

    /**
     *
     * @access public
     * @param int $user_id
     * @return array|null
     */
    public static function get_register_bonus_for_user_id($user_id):? array
    {
        $res = null;

        $query = DB::select(['wpc.id', 'code_id'], ['wupc.id', 'assign_id'], ['wpc.token', 'code'], 'wc.*')->from(['whitelabel_promo_code', 'wpc'])
        ->join(['whitelabel_user_promo_code', 'wupc'])->on('wupc.whitelabel_promo_code_id', '=', 'wpc.id')
        ->join(['whitelabel_campaign', 'wc'])->on('wpc.whitelabel_campaign_id', '=', 'wc.id')
        ->where('wupc.whitelabel_user_id', '=', $user_id)
        ->and_where('wupc.whitelabel_transaction_id', '=', null)
        ->and_where('wc.is_active', '=', 1)
        ->and_where(function ($query) {
            $query->and_where('wc.bonus_type', '=', Helpers_General::PROMO_CODE_BONUS_TYPE_FREE_LINE)
                ->or_where(function ($query) {
                    $query->and_where('wc.bonus_type', '=', Helpers_General::PROMO_CODE_BONUS_TYPE_DISCOUNT)
                    ->and_where('wc.discount_amount', '!=', '0.00');
                });
        })
        ->and_where('wc.date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('wc.date_end', '>=', DB::expr('CURDATE()'))
        ->and_where(function ($query) {
            $query->and_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_REGISTER)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
        })
        ->and_where(function ($query) {
            $query->and_where('wupc.type', '=', null)
                ->or_where('wupc.type', '=', Helpers_General::PROMO_CODE_TYPE_REGISTER);
        })
        ->limit(1);

        $result = $query->execute()->as_array();
        if (isset($result[0])) {
            $res = $result[0];
        }

        return $res;
    }

    /**
     *
     * @access public
     * @param int $user_id
     * @return array|null
     */
    public static function get_deposit_bonus_for_user_id($user_id):? array
    {
        $res = null;

        $query = DB::select(['wpc.id', 'code_id'], ['wupc.id', 'assign_id'], ['wpc.token', 'code'], 'wc.*')->from(['whitelabel_promo_code', 'wpc'])
        ->join(['whitelabel_user_promo_code', 'wupc'])->on('wupc.whitelabel_promo_code_id', '=', 'wpc.id')
        ->join(['whitelabel_campaign', 'wc'])->on('wpc.whitelabel_campaign_id', '=', 'wc.id')
        ->where('wupc.whitelabel_user_id', '=', $user_id)
        ->and_where('wupc.whitelabel_transaction_id', '=', null)
        ->and_where('wc.is_active', '=', 1)
        ->and_where('wc.date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('wc.date_end', '>=', DB::expr('CURDATE()'))
        ->and_where(function ($query) {
            $query->and_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
        })
        ->and_where(function ($query) {
            $query->and_where('wupc.type', '=', null)
                ->or_where('wupc.type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT);
        })
        ->limit(1);

        $result = $query->execute()->as_array();
        if (isset($result[0])) {
            $res = $result[0];
        }

        return $res;
    }

    /**
     *
     * @access public
     * @param int $user_id
     * @return array|null
     */
    public static function get_purchase_bonus_for_user_id($user_id):? array
    {
        $res = null;

        $query = DB::select(['wpc.id', 'code_id'], ['wupc.id', 'assign_id'], ['wpc.token', 'code'], 'wc.*')->from(['whitelabel_promo_code', 'wpc'])
        ->join(['whitelabel_user_promo_code', 'wupc'])->on('wupc.whitelabel_promo_code_id', '=', 'wpc.id')
        ->join(['whitelabel_campaign', 'wc'])->on('wpc.whitelabel_campaign_id', '=', 'wc.id')
        ->where('wupc.whitelabel_user_id', '=', $user_id)
        ->and_where('wupc.whitelabel_transaction_id', '=', null)
        ->and_where('wc.is_active', '=', 1)
        ->and_where('wc.date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('wc.date_end', '>=', DB::expr('CURDATE()'))
        ->and_where(function ($query) {
            $query->and_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER)
                ->or_where('wc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
        })
        ->and_where(function ($query) {
            $query->and_where('wupc.type', '=', null)
                ->or_where('wupc.type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE);
        })
        ->limit(1);

        $result = $query->execute()->as_array();
        if (isset($result[0])) {
            $res = $result[0];
        }

        return $res;
    }
}
