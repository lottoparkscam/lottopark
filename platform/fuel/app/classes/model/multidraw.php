<?php

use Carbon\Carbon;
use Services\Logs\FileLoggerService;

/**
 * @deprecated
 */
class Model_Multidraw extends Model_Model
{

    /**
     *
     * @var string
     */
    protected static $_table_name = 'multi_draw';

    /**
     *
     * @var array
     */
    public static $cache_list = [];

    /**
     *
     * @param array $whitelabel
     * @param Object $pagination
     * @param array $sort
     * @param array $params
     * @param string $filter_add
     * @return null|array
     */
    public static function get_full_data_paid_for_whitelabel(
        $whitelabel,
        $pagination,
        $sort,
        $params,
        $filter_add = ""
    ):? array {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            multi_draw.*, 
            whitelabel_user.token AS utoken, 
            whitelabel_user.name, 
            whitelabel_user.surname, 
            whitelabel_user.email,
            whitelabel_user.login AS user_login
        FROM multi_draw  
        INNER JOIN lottery ON lottery.id = multi_draw.lottery_id 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = multi_draw.whitelabel_user_id
        LEFT JOIN whitelabel_transaction wt ON wt.id = multi_draw.whitelabel_transaction_id
        WHERE wt.status = 1";

        if (!empty($whitelabel) && !empty($whitelabel["id"])) {
            $query .= " AND multi_draw.whitelabel_id = :whitelabel_id ";
        }

        $query .= " " . $filter_add;

        $query .= " ORDER BY id DESC ";

        if (!empty($sort) && !empty($sort["db"])) {
            // $query .= " ORDER BY :order ";
        }

        $query .= " LIMIT :offset, :limit";

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel["id"])) {
                $db->param(":whitelabel_id", $whitelabel["id"]);
            }

            $db->param(":offset", $pagination->offset);
            $db->param(":limit", $pagination->per_page);

            if (!empty($sort) && !empty($sort["db"])) {
                $db->param(":order", DB::expr($sort["db"]));
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param int    $lottery_id
     * @param int $range_from
     *
     * @return array|null
     */
    public static function get_multidraws_for_cancellation(int $lottery_id, int $range_from):? array
    {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $result = null;
        $query = "SELECT 
            multi_draw.*, 
            count(whitelabel_user_ticket.id) as unprocessed_tickets, 
            whitelabel_transaction.amount_usd as transaction_amount_usd, 
            whitelabel_transaction.amount as transaction_amount
        FROM multi_draw 
		INNER JOIN whitelabel_user_ticket ON whitelabel_user_ticket.multi_draw_id = multi_draw.id 
		INNER JOIN whitelabel_transaction ON whitelabel_transaction.id = whitelabel_user_ticket.whitelabel_transaction_id 
		WHERE is_finished = '0' 
        AND whitelabel_transaction.status = 1
		AND whitelabel_user_ticket.draw_date >= :range_from 
		AND whitelabel_user_ticket.date_processed IS NULL 
		AND multi_draw.lottery_id = :lottery_id
		GROUP BY multi_draw.id";

        try {
            $proper_date = Carbon::createFromTimestamp($range_from);
            $db = DB::query($query);

            $db->param(":range_from", $proper_date->format(Helpers_Time::DATETIME_FORMAT));
            $db->param(":lottery_id", $lottery_id);

            $result = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        return $result;
    }

    /**
     *
     * @param array $whitelabel
     * @param array $params
     * @param string $filter_add
     * @return null|int
     */
    public static function get_counted_by_whitelabel_filtered(
        $whitelabel,
        $params = [],
        $filter_add = ""
    ):? int {
        $fileLoggerService = Container::get(FileLoggerService::class);

        $res = null;
        $result = null;

        $query = "SELECT 
            COUNT(*) AS count 
        FROM multi_draw  
        INNER JOIN lottery ON lottery.id = multi_draw.lottery_id 
        LEFT JOIN whitelabel_user ON whitelabel_user.id = multi_draw.whitelabel_user_id
        LEFT JOIN whitelabel_transaction wt ON wt.id = multi_draw.whitelabel_transaction_id
        WHERE wt.status = 1 ";

        if (!empty($whitelabel) && !empty($whitelabel['id'])) {
            $query .= " AND multi_draw.whitelabel_id = :whitelabel_id ";
        }

        $query .= " " . $filter_add;

        try {
            $db = DB::query($query);

            if (!empty($whitelabel) && !empty($whitelabel['id'])) {
                $db->param(":whitelabel_id", $whitelabel['id']);
            }

            foreach ($params as $param) {
                $db->param($param[0], $param[1]);
            }

            $res = $db->execute()->as_array();
        } catch (Exception $e) {
            $fileLoggerService->error(
                $e->getMessage()
            );
        }

        if (is_null($res) || is_null($res[0])) {
            return $result;
        }

        $result = $res[0]['count'];

        return $result;
    }

    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param array $filters
     * @param int $page
     * @param int $items_per_page
     * @param string $sort_by
     * @param string $order
     * @return array
     */
    public static function get_full_data_for_crm(
        ?int $whitelabel_id,
        array $filters,
        ?int $page = null,
        ?int $items_per_page = null,
        ?string $sort_by = null,
        ?string $order = null,
        bool $is_cache_disabled = false
    ): array {
        $res = [];

        $query = DB::select(
            'multi_draw.*',
            'whitelabel.prefix',
            ['lottery.name', 'lname'],
            ['wu.token', 'utoken'],
            'wu.name',
            'wu.surname',
            'wu.email',
            ['wu.login', 'user_login']
        )
        ->from('multi_draw')
        ->join('whitelabel')->on('multi_draw.whitelabel_id', '=', 'whitelabel.id')
        ->join(['whitelabel_user', 'wu'], 'LEFT')->on('multi_draw.whitelabel_user_id', '=', 'wu.id')
        ->join('lottery')->on('multi_draw.lottery_id', '=', 'lottery.id')
        ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('multi_draw.whitelabel_transaction_id', '=', 'wt.id')
        ->where('wt.status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED);

        if ($whitelabel_id) {
            $query->and_where('multi_draw.whitelabel_id', '=', $whitelabel_id);
        }

        $query = self::prepare_filters($filters, $query, $whitelabel_id);

        if ($sort_by) {
            $query->order_by($sort_by, $order);
        }

        if ($items_per_page) {
            $query->limit($items_per_page)->offset($items_per_page * ($page - 1));
        }

        if ($is_cache_disabled) {
            $query->caching(false);
        }

        $res = $query->execute()->as_array();
        
        return $res;
    }
    
    /**
     *
     * @access public
     * @param int $whitelabel_id
     * @param array $filters
     * @return int
     */
    public static function get_tickets_count_for_crm(
        ?int $whitelabel_id,
        array $filters
    ): int {
        $res = [];

        $query = DB::select(
            DB::expr('COUNT(*) as count')
        )
        ->from('multi_draw')
        ->join('whitelabel')->on('multi_draw.whitelabel_id', '=', 'whitelabel.id')
        ->join(['whitelabel_user', 'wu'], 'LEFT')->on('multi_draw.whitelabel_user_id', '=', 'wu.id')
        ->join('lottery')->on('multi_draw.lottery_id', '=', 'lottery.id')
        ->join(['whitelabel_transaction', 'wt'], 'LEFT')->on('multi_draw.whitelabel_transaction_id', '=', 'wt.id')
        ->where('wt.status', '=', Helpers_General::STATUS_TRANSACTION_APPROVED);

        if ($whitelabel_id) {
            $query->and_where('multi_draw.whitelabel_id', '=', $whitelabel_id);
        }

        $query = self::prepare_filters($filters, $query, $whitelabel_id);

        $result = $query->execute()->as_array();
        if (isset($result[0])) {
            $res = $result[0]['count'];
        }
        
        return $res;
    }

    /**
     *
     * @param array $filters
     * @param Database_Query_Builder_Select $query
     * @param int $whitelabel
     * @return Database_Query_Builder_Select
     */
    private static function prepare_filters($filters, $query, $whitelabel)
    {
        foreach ($filters as $filter) {
            if ($filter['column'] === 'token') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('multi_draw.token', 'LIKE', $value)
                        ->or_where('lottery.name', 'LIKE', $value);
            }
            if ($filter['column'] === 'utoken') {
                $value = '%' . $filter['value'] . '%';
                $query->and_where('wu.name', 'LIKE', $value)
                        ->or_where('wu.login', 'LIKE', $value)
                        ->or_where('wu.token', 'LIKE', $value)
                        ->or_where('wu.surname', 'LIKE', $value)
                        ->or_where('wu.email', 'LIKE', $value);
            }
            if ($filter['column'] === 'tickets') {
                if ($filter['start'] > 0 && $filter['end'] == 0) {
                    $query->and_where('multi_draw.tickets', '>=', intval($filter['start']));
                } elseif ($filter['start'] > 0 && $filter['end'] > 0) {
                    $query->and_where('multi_draw.tickets', '>=', intval($filter['start']))
                    ->and_where('multi_draw.tickets', '<=', intval($filter['end']));
                } elseif ($filter['start'] == 0 && $filter['end'] > 0) {
                    $query->and_where('multi_draw.tickets', '<=', intval($filter['end']));
                }
            }
            if ($filter['column'] === 'date') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('multi_draw.date', '>=', $start);
                $query->and_where('multi_draw.date', '<=', $end);
            }
            if ($filter['column'] == 'first_draw') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('multi_draw.first_draw', '>=', $start);
                $query->and_where('multi_draw.first_draw', '<=', $end);
            }
            if ($filter['column'] === 'valid_to_draw') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('multi_draw.valid_to_draw', '>=', $start);
                $query->and_where('multi_draw.valid_to_draw', '<=', $end);
            }
            if ($filter['column'] === 'current_draw') {
                $start = Helpers_Crm_General::prepare_start_date($filter['startDate']);
                $end = Helpers_Crm_General::prepare_end_date($filter['endDate']);
                $query->and_where('multi_draw.current_draw', '>=', $start);
                $query->and_where('multi_draw.current_draw', '<=', $end);
            }
        }

        return $query;
    }
}
