<?php

use Fuel\Core\DB;

class Model_Whitelabel_Campaign extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_campaign';


    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function get_all_campaigns($whitelabel_id)
    {
        $res = [];

        $subquery = DB::select(
            'whitelabel_campaign_id',
            DB::expr('count(*) as count')
        )
        ->from('whitelabel_promo_code')
        ->group_by('whitelabel_campaign_id');

        $subquery_codes_used = DB::select(
            'whitelabel_promo_code.whitelabel_campaign_id',
            DB::expr('count(whitelabel_promo_code.id) as num')
        )
        ->from('whitelabel_promo_code')
        ->join('whitelabel_user_promo_code')->on('whitelabel_user_promo_code.whitelabel_promo_code_id', '=', 'whitelabel_promo_code.id')
        ->group_by('whitelabel_promo_code.id');

        $subquery_used_times = DB::select('s.whitelabel_campaign_id', DB::expr('sum(s.num) as sum'))
        ->from([$subquery_codes_used, 's'])
        ->group_by('s.whitelabel_campaign_id');

        $subquery_used_code = DB::select('s.whitelabel_campaign_id', DB::expr('count(s.num) as count'))
        ->from([$subquery_codes_used, 's'])
        ->group_by('s.whitelabel_campaign_id');

        $query = DB::select(
            'whitelabel_campaign.*',
            ['whitelabel_aff.login', 'aff_login'],
            ['whitelabel_aff.email', 'aff_email'],
            ['whitelabel_aff.name', 'aff_name'],
            ['whitelabel_aff.surname', 'aff_surname'],
            ['lottery.name', 'lottery_name'],
            ['s.count', 'codes_count'],
            ['s2.sum', 'used_times'],
            ['s3.count', 'used_codes_count']
        )
        ->from('whitelabel_campaign')
        ->join('whitelabel_aff', 'LEFT')->on('whitelabel_campaign.whitelabel_aff_id', '=', 'whitelabel_aff.id')
        ->join('lottery', 'LEFT')->on('whitelabel_campaign.lottery_id', '=', 'lottery.id')
        ->join([$subquery, 's'], 'LEFT')->on('s.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->join([$subquery_used_times, 's2'], 'LEFT')->on('s2.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->join([$subquery_used_code, 's3'], 'LEFT')->on('s3.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->where('whitelabel_campaign.whitelabel_id', '=', $whitelabel_id)
        ->order_by('whitelabel_campaign.date_start', 'DESC');

        $res = $query->execute()->as_array();
        return $res;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function is_active_register($whitelabel_id)
    {
        $is_active = false;

        $query = DB::select(DB::expr('count(*) as count'))
        ->from('whitelabel_campaign')
        ->where('is_active', '=', 1)
        ->and_where(function ($query) {
            $query->and_where('type', '=', Helpers_General::PROMO_CODE_TYPE_REGISTER)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
        })
        ->and_where('date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('date_end', '>=', DB::expr('CURDATE()'))
        ->and_where('whitelabel_id', '=', $whitelabel_id);

        $res = $query->execute()->as_array();

        if (isset($res[0])) {
            if ($res[0]['count'] > 0) {
                $is_active = true;
            }
        }

        return $is_active;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function is_active_deposit($whitelabel_id)
    {
        $is_active = false;

        $query = DB::select(DB::expr('count(*) as count'))
        ->from('whitelabel_campaign')
        ->where('is_active', '=', 1)
        ->and_where(function ($query) {
            $query->and_where('type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
        })
        ->and_where('date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('date_end', '>=', DB::expr('CURDATE()'))
        ->and_where('whitelabel_id', '=', $whitelabel_id);

        $res = $query->execute()->as_array();

        if (isset($res[0])) {
            if ($res[0]['count'] > 0) {
                $is_active = true;
            }
        }

        return $is_active;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @return array
     */
    public static function is_active_purchase($whitelabel_id)
    {
        $is_active = false;

        $query = DB::select(DB::expr('count(*) as count'))
        ->from('whitelabel_campaign')
        ->where('is_active', '=', 1)
        ->and_where(function ($query) {
            $query->and_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER)
                ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
        })
        ->and_where('date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('date_end', '>=', DB::expr('CURDATE()'))
        ->and_where('whitelabel_id', '=', $whitelabel_id);

        $res = $query->execute()->as_array();

        if (isset($res[0])) {
            if ($res[0]['count'] > 0) {
                $is_active = true;
            }
        }

        return $is_active;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param int $type
     * @return array
     */
    public static function get_all_active_with_codes($whitelabel_id, $type)
    {
        $res = [];

        $query = DB::select('whitelabel_campaign.*', ['whitelabel_promo_code.id', 'code_id'], ['whitelabel_promo_code.token', 'code'])
        ->from('whitelabel_campaign')
        ->join('whitelabel_promo_code')
        ->on('whitelabel_promo_code.whitelabel_campaign_id', '=', 'whitelabel_campaign.id')
        ->where('whitelabel_id', '=', $whitelabel_id)
        ->and_where('is_active', '=', 1)
        ->and_where('date_start', '<=', DB::expr('CURDATE()'))
        ->and_where('date_end', '>=', DB::expr('CURDATE()'));

        if ($type === 0) {
            $query->and_where(function ($query) {
                $query->and_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
            });
        } elseif ($type === 1) {
            $query->and_where(function ($query) {
                $query->and_where('type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
            });
        } elseif ($type === 2) {
            $query->and_where(function ($query) {
                $query->and_where('type', '=', Helpers_General::PROMO_CODE_TYPE_REGISTER)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_REGISTER)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_DEPOSIT_REGISTER)
                    ->or_where('type', '=', Helpers_General::PROMO_CODE_TYPE_PURCHASE_DEPOSIT_REGISTER);
            });
        }

        $res = $query->execute()->as_array();
        return $res;
    }

    /**
     *
     * @param int $user_id
     * @param int $transaction_id
     * @return null|array
     */
    public static function get_by_user_and_transaction_id($user_id, $transaction_id):? array
    {
        $res = null;
        $query = DB::select('wc.*')
        ->from(['whitelabel_user_promo_code', 'wupc'])
        ->join(['whitelabel_promo_code', 'wpc'])->on('wupc.whitelabel_promo_code_id', '=', 'wpc.id')
        ->join(['whitelabel_campaign', 'wc'])->on('wpc.whitelabel_campaign_id', '=', 'wc.id')
        ->where('wupc.whitelabel_transaction_id', '=', $transaction_id)
        ->and_where('wupc.whitelabel_user_id', '=', $user_id)
        ->limit(1);

        $result = $query->execute()->as_array();

        if (isset($result[0])) {
            $res = $result[0];
        }

        return $res;
    }

    /**
     *
     * @param int $token
     * @return bool
     */
    public static function is_used($token): bool
    {
        $res = false;
        $result = DB::select('*')
        ->from(['whitelabel_user_promo_code', 'wupc'])
        ->join(['whitelabel_promo_code', 'wpc'])->on('wupc.whitelabel_promo_code_id', '=', 'wpc.id')
        ->join(['whitelabel_campaign', 'wc'])->on('wpc.whitelabel_campaign_id', '=', 'wc.id')
        ->where('wc.token', '=', $token)
        ->execute()->as_array();

        if (isset($result) && count($result) > 0) {
            $res = true;
        }

        return $res;
    }
}
