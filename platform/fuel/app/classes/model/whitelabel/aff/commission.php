<?php

/**
 * @deprecated
 * Class responsible for interactions with database - table whitelabel_aff_commission.
 */
class Model_Whitelabel_Aff_Commission extends Model_Model
{
    /**
     *
     * @var string
     */
    protected static $_table_name = 'whitelabel_aff_commission';
    
    /**
     *
     * @var array
     */
    public static $cache_list = [];
    
    /**
     * Prepare main commissions query for reports
     * It consists different currencies of the payments
     *
     * @return string
     */
    private static function get_commissions_query(string $whereForWhitelabelAff = '', string $whereForWhitelabelTransaction = ''): string
    {

        $query =
        'SELECT 
            whitelabel_aff_commission.id, 
            whitelabel_aff_commission.tier,
            whitelabel_aff_commission.type, 
            whitelabel_aff_commission.commission, 
            whitelabel_aff_commission.commission_payment,
            whitelabel_aff_commission.commission_manager,
            whitelabel_aff_commission.whitelabel_aff_id, 
            whitelabel_aff_commission.is_accepted,

            whitelabel_transaction.amount,
            whitelabel_transaction.amount_payment, 
            whitelabel_transaction.amount_manager,
            whitelabel_transaction.payment_cost, 
            whitelabel_transaction.payment_cost_manager,
            whitelabel_transaction.cost, 
            whitelabel_transaction.cost_manager,
            whitelabel_transaction.income, 
            whitelabel_transaction.income_manager,
            whitelabel_transaction.type AS ttype, 
            whitelabel_transaction.token AS ttoken, 
            whitelabel_transaction.date_confirmed,

            whitelabel_user.token, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user.is_confirmed, 
            whitelabel_user.email, 

            payment_currency.id AS payment_currency_id,
            payment_currency.code AS payment_currency_code,
            payment_currency.rate AS payment_currency_rate,

            user_currency.id AS user_currency_id,
            user_currency.code AS user_currency_code,
            user_currency.rate AS user_currency_rate,

            manager_currency.id AS manager_currency_id,
            manager_currency.code AS manager_currency_code,
            manager_currency.rate AS manager_currency_rate,

            whitelabel_aff.email AS aff_email,
            whitelabel_aff.name AS aff_name,
            whitelabel_aff.surname AS aff_surname,
            whitelabel_aff.login AS aff_login,
            whitelabel_aff.is_confirmed AS aff_is_confirmed,
            whitelabel_aff.token AS aff_token
            
            FROM whitelabel_aff_commission 
            LEFT JOIN (
                SELECT
                    whitelabel_transaction.id,
                    whitelabel_transaction.amount,
                    whitelabel_transaction.amount_payment, 
                    whitelabel_transaction.amount_manager,
                    whitelabel_transaction.payment_cost, 
                    whitelabel_transaction.payment_cost_manager,
                    whitelabel_transaction.cost, 
                    whitelabel_transaction.cost_manager,
                    whitelabel_transaction.income, 
                    whitelabel_transaction.income_manager,
                    whitelabel_transaction.date_confirmed,
                    whitelabel_transaction.type,
                    whitelabel_transaction.token,
                    whitelabel_transaction.currency_id,
                    whitelabel_transaction.payment_currency_id,
                    whitelabel_transaction.whitelabel_user_id 
                FROM whitelabel_transaction
            ) AS whitelabel_transaction ON whitelabel_transaction.id = whitelabel_aff_commission.whitelabel_transaction_id 
            LEFT JOIN (
                SELECT
                    whitelabel_user.id, 
                    whitelabel_user.token, 
                    whitelabel_user.name, 
                    whitelabel_user.surname, 
                    whitelabel_user.is_confirmed, 
                    whitelabel_user.email,
                    whitelabel_user.country 
                FROM whitelabel_user
            ) AS whitelabel_user ON whitelabel_transaction.whitelabel_user_id = whitelabel_user.id 
            
            INNER JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_aff_commission.whitelabel_aff_id 
            INNER JOIN whitelabel ON whitelabel.id = whitelabel_aff.whitelabel_id 
            INNER JOIN currency manager_currency ON manager_currency.id = whitelabel.manager_site_currency_id 
            LEFT JOIN currency payment_currency ON payment_currency.id = whitelabel_transaction.payment_currency_id 
            LEFT JOIN currency user_currency ON user_currency.id = whitelabel_transaction.currency_id 
            LEFT JOIN whitelabel_user_aff ON whitelabel_aff_commission.whitelabel_user_aff_id = whitelabel_user_aff.id
            WHERE whitelabel_aff.whitelabel_id = :whitelabel
         ' . $whereForWhitelabelAff . $whereForWhitelabelTransaction;

        return $query;
    }

    /**
     * Fetch count of commissions for whitelabel.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @return int count of the commissions for specified user.
     */
    public static function fetch_count_for_commissions_for_whitelabel(
        $add,
        $params,
        $whitelabel_id
    ): int {
        $params[] = [":whitelabel_id", $whitelabel_id];

        $getCountQuery = 'SELECT COUNT(whitelabel_aff_commission.id) AS count FROM whitelabel_aff_commission
        INNER JOIN (select id, whitelabel_user_id, date_confirmed from whitelabel_transaction
        WHERE
                type = ' . Helpers_General::TYPE_TRANSACTION_PURCHASE . '
                AND status = ' . Helpers_General::STATUS_TRANSACTION_APPROVED .
        '
        ) as whitelabel_transaction ON whitelabel_aff_commission.whitelabel_transaction_id = whitelabel_transaction.id
        INNER JOIN (
            SELECT id, token, email, name, surname, country FROM whitelabel_user
        ) as whitelabel_user on whitelabel_transaction.whitelabel_user_id = whitelabel_user.id
        AND whitelabel_aff_id IN (
            SELECT id from whitelabel_aff
            WHERE
                is_deleted = 0
                AND is_active = 1
                AND is_accepted = 1
        )
        AND whitelabel_user_aff_id IN (
            SELECT id from whitelabel_user_aff
            WHERE
                is_accepted = 1
                AND whitelabel_id = :whitelabel_id
        )' . implode(" ", $add);

        // execute safe query
        $result = parent::execute_query($getCountQuery, $params);
        // safely retrieve value
        return parent::get_array_result_item($result, 0, 0, 'count');
    }
    
    /**
     * Fetch commissions for whitelabel id.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param object $pagination pagination object.
     * @param int $whitelabel_id whitelabel id
     * @param int $export should be exported data?
     * @return array array of commissions.
     */
    public static function fetch_commissions_for_whitelabel(
        array $add,
        array $params,
        $pagination,
        int $whitelabel_id,
        $export = 0
    ):? array {
        $add_limits = " LIMIT :offset, :limit";
        if ($export == 1) {
            $add_limits = "";
        }

        $whereForWhitelabelAff =
        ' AND whitelabel_aff.is_deleted = 0 
        AND whitelabel_aff.is_active = 1 
        AND whitelabel_aff.is_accepted = 1';

        $commissionQuery = self::get_commissions_query($whereForWhitelabelAff) .
            implode(" ", $add) . $add_limits;

        // add non global params
        $params[] = [":whitelabel", $whitelabel_id];
        $params[] = [":offset", $pagination->offset];
        $params[] = [":limit", $pagination->per_page];

        $result = parent::execute_query($commissionQuery, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }

    /**
     * Fetch commissions for reports based on whitelabel id.
     *
     * Todo: dates and query, dates should be set to add and params
     * before this function is called.
     *
     * @param array $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @param date $date_start start date of payout
     * @param date $date_end end date of payout
     * @return array array of commissions.
     */
    public static function fetch_commissions_for_whitelabel_report(
        array $add,
        array $params,
        int $whitelabel_id,
        $date_start,
        $date_end
    ):? array {
        
        // add non global params
        $params[] = [":whitelabel", $whitelabel_id];
        $params[] = [":date_start", $date_start];
        $params[] = [":date_end", $date_end];

        $whereForWhitelabelAff = ' AND whitelabel_user_aff.is_accepted = 1 ';
        $whereForWhitelabelTransaction = ' AND date_confirmed >= :date_start 
        AND date_confirmed <= :date_end ';

        $commissionQuery = self::get_commissions_query($whereForWhitelabelAff, $whereForWhitelabelTransaction) .
        '
        AND whitelabel_aff_commission.is_accepted = 1' .
            implode(" ", $add) .
        ' ORDER BY date_confirmed, type';

        // execute safe query
        $result = parent::execute_query($commissionQuery, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch commissions for payout.
     *
     * @param date $date_start start date of payout
     * @param date $date_end end date of payout
     * @return array array of commissions.
     */
    public static function fetch_commissions_for_payout($date_start, $date_end)
    {
        $params = [];
        // add non global params
        $params[] = [":date_start", $date_start];
        $params[] = [":date_end", $date_end];
        
        // todo: this query could and should be extracted into parts,
        // e.g get_comissions_query_from which will return from block,
        // which is constant for all queries (linked to system architecture).
        $query_string = "SELECT 
            whitelabel_aff.whitelabel_id,
            whitelabel_aff_commission.whitelabel_aff_id,
            whitelabel_aff.currency_id AS wa_currency_id,
            COUNT(whitelabel_aff_commission.id) AS count,
            COALESCE(SUM(commission), 0.00) AS sum_commission,
            COALESCE(SUM(commission_usd), 0.00) AS sum_commission_usd,
            COALESCE(SUM(commission_manager), 0.00) AS sum_commission_manager 
        FROM whitelabel_aff_commission 
        INNER JOIN whitelabel_aff ON whitelabel_aff.id = whitelabel_aff_commission.whitelabel_aff_id 
        INNER JOIN whitelabel ON whitelabel.id = whitelabel_aff.whitelabel_id 
        INNER JOIN whitelabel_transaction ON whitelabel_transaction.id = whitelabel_aff_commission.whitelabel_transaction_id 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = whitelabel_transaction.whitelabel_user_id 
        LEFT JOIN whitelabel_user_aff ON whitelabel_user_aff.whitelabel_user_id = whitelabel_user.id 
        AND whitelabel_aff_commission.whitelabel_aff_id = whitelabel_user_aff.whitelabel_aff_id 
        WHERE whitelabel_user_aff.is_accepted = 1 
        AND whitelabel_aff_commission.is_accepted = 1 
        AND whitelabel_transaction.date_confirmed >= :date_start 
        AND whitelabel_transaction.date_confirmed <= :date_end 
        GROUP BY whitelabel_aff_id 
        ORDER BY whitelabel_aff_id";
      
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch sums of approved transactions and in fact commissions for
     * them grouped by whitelabel_id filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_id whitelabel id
     * @return array consists of the sums of approved commissions for specified whitelabel.
     */
    public static function get_sums_for_whitelabel_for_reports(
        string $add,
        array $params,
        int $whitelabel_id
    ):? array {
        // add non global params
        $params[] = [":whitelabel_id", $whitelabel_id];
        
        $query_string = "SELECT 
            wt.whitelabel_id, 
            COALESCE(SUM(commission), 0.00) AS commission_sum, 
            COALESCE(SUM(commission_usd), 0.00) AS commission_usd_sum, 
            COALESCE(SUM(commission_manager), 0.00) AS commission_manager_sum 
        FROM whitelabel_aff_commission wac
        LEFT JOIN whitelabel_aff wa ON wa.id = wac.whitelabel_aff_id
        LEFT JOIN whitelabel_transaction wt ON wt.id = wac.whitelabel_transaction_id
        LEFT JOIN whitelabel_user ON whitelabel_user.id = wt.whitelabel_user_id
        WHERE wt.whitelabel_id = :whitelabel_id 
            AND wa.is_accepted = 1 
            AND wac.is_accepted = 1 
            AND wt.type = " . Helpers_General::TYPE_TRANSACTION_PURCHASE . "
            AND wt.status = " . Helpers_General::STATUS_TRANSACTION_APPROVED .
            " " . $add . "
            GROUP BY wt.whitelabel_id";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
    
    /**
     * Fetch sums of approved transactions and in fact commissions for
     * them grouped by whitelabel_id filtered.
     *
     * @param string $add filter adds.
     * @param array $params filter params.
     * @param int $whitelabel_type whitelabel type could be null
     * @param int $whitelabel_id whitelabel id could be null
     * @param bool $is_full_report For full report noting changed
     * @return array consists of the sums of approved commissions
     */
    public static function get_sums_for_whitelabel_for_admin_reports(
        string $add,
        array $params,
        int $whitelabel_type = null,
        int $whitelabel_id = null,
        bool $is_full_report = false
    ):? array {
        if (!empty($whitelabel_type)) {
            $params[] = [":whitelabel_type", $whitelabel_type];
        }
        
        if (!empty($whitelabel_id)) {
            $params[] = [":whitelabel_id", $whitelabel_id];
        }
        
        $query_string = "SELECT 
            wt.whitelabel_id, 
            COALESCE(SUM(commission), 0.00) AS commission_sum, 
            COALESCE(SUM(commission_usd), 0.00) AS commission_usd_sum, 
            COALESCE(SUM(commission_manager), 0.00) AS commission_manager_sum 
        FROM whitelabel_aff_commission wac
        LEFT JOIN whitelabel_aff wa ON wa.id = wac.whitelabel_aff_id
        LEFT JOIN whitelabel_transaction wt ON wt.id = wac.whitelabel_transaction_id
        LEFT JOIN whitelabel_user ON whitelabel_user.id = wt.whitelabel_user_id 
        INNER JOIN whitelabel ON whitelabel_user.whitelabel_id = whitelabel.id ";
        
        if (!empty($whitelabel_type)) {
            $query_string .= " AND whitelabel.type = :whitelabel_type ";
        }
        
        $query_string .= "WHERE wa.is_accepted = 1 
            AND wac.is_accepted = 1 
            AND wt.type = " . Helpers_General::TYPE_TRANSACTION_PURCHASE . "
            AND wt.status = " . Helpers_General::STATUS_TRANSACTION_APPROVED . " ";
        
        if (!empty($whitelabel_id)) {
            $query_string .= " AND whitelabel.id = :whitelabel_id ";
        }
        
        if (!$is_full_report && is_null($whitelabel_id)) {
            $query_string .= " AND whitelabel.is_report = 1 ";
        }
        
        $query_string .= $add . "
            GROUP BY wt.whitelabel_id";
        
        // execute safe query
        $result = parent::execute_query($query_string, $params);
        // safely retrieve value
        return parent::get_array_result($result, []);
    }
}
