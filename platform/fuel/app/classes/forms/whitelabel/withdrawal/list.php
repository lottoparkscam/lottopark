<?php

/**
 * Description of Forms_Whitelabel_Withdrawal_List
 */
final class Forms_Whitelabel_Withdrawal_List extends Forms_Main
{
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];
    
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var int
     */
    private $items_per_page = 25;
    
    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     * @param int $source
     * @param array $whitelabel
     */
    public function __construct(
        int $source = Helpers_General::SOURCE_WHITELABEL,
        array $whitelabel = []
    ) {
        if (!empty($source) && $source === Helpers_General::SOURCE_ADMIN) {
            if (Input::get("filter.whitelabel") != null &&
                Input::get("filter.whitelabel") != "a"
            ) {
                $whitelabel = [];
                $whitelabel['id'] = intval(Input::get("filter.whitelabel"));
            }
        }
        
        $this->source = $source;
        $this->whitelabel = $whitelabel;
    }
    
    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }
    
    /**
     *
     * @return int
     */
    public function get_source(): int
    {
        return $this->source;
    }
    
    /**
     *
     * @return \View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     * This function is for prepare different data to filter data taken from DB tables
     *
     * @param array $kmethods
     * @return array
     */
    private function prepare_filters(array $kmethods = null): array
    {
        $filter_add = [];
        $params = [];
        $whitelabel = $this->get_whitelabel();

        if (Input::get("filter.id") != null) {
            $filter_add[] = " AND withdrawal_request.token = :token";
            $token_withdrawal_temp = $whitelabel['prefix'] . 'W';
            $token_withdrawal = str_ireplace($token_withdrawal_temp, "", Input::get("filter.id"));
            $params[] = [":token", intval($token_withdrawal)];
        }
        
        if (Input::get("filter.userid") != null) {
            $filter_add[] = " AND whitelabel_user.token = :utoken";
            $token_user_temp = $whitelabel['prefix'] . 'U';
            $token_user = str_ireplace($token_user_temp, "", Input::get("filter.userid"));
            $params[] = [":utoken", intval($token_user)];
        }
        
        if (Input::get("filter.status") != null && Input::get("filter.status") != "a") {
            $filter_add[] = " AND withdrawal_request.status = :status";
            $params[] = [":status", intval(Input::get("filter.status"))];
        }
        
        if (Input::get("filter.method") != null && Input::get("filter.method") != "a") {
            if (isset($kmethods[intval(Input::get("filter.method")) - 1])) {
                $filter_add[] = " AND withdrawal_request.withdrawal_id = :withdrawal_id";
                $params[] = [":withdrawal_id", $kmethods[intval(Input::get("filter.method")) - 1]['id']];
            }
        }
        
        if (Input::get("filter.email") != null) {
            $filter_add[] = " AND whitelabel_user.email LIKE :email";
            $params[] = [":email", '%' . Input::get("filter.email") . '%'];
        }
        
        if (Input::get("filter.range_start") != '') {
            // get date ranges
            $dates = $this->prepare_dates();

            $filter_add[] = " AND withdrawal_request.date >= :date_start";
            $params[] = [":date_start", $dates['date_start']];

            $filter_add[] = "  AND  withdrawal_request.date <= :date_end";
            $params[] = [":date_end", $dates['date_end']];
        }
            
        $filter_add_whole = implode("", $filter_add);

        return [
            $filter_add_whole,
            $params
        ];
    }
    
    /**
     * This function is for prepare data for filters for view
     *
     * @return array
     */
    private function prepare_filters_data(): array
    {
        $filters_data = [];
        
        $withdrawal_id = '';
        if (!is_null(Input::get("filter.id"))) {
            $withdrawal_id = Input::get("filter.id");
        }
        $filters_data['withdrawal_request_token'] = Security::htmlentities($withdrawal_id);
        
        $user_id = '';
        if (!is_null(Input::get("filter.userid"))) {
            $user_id = Input::get("filter.userid");
        }
        $filters_data['user_id'] = Security::htmlentities($user_id);

        $email = '';
        if (!is_null(Input::get("filter.email"))) {
            $email = Input::get("filter.email");
        }
        $filters_data['email'] = Security::htmlentities($email);
        
        $range_start = '';
        if (!empty(Input::get("filter.range_start"))) {
            $range_start = Input::get("filter.range_start");
        }
        $filters_data['range_start'] = Security::htmlentities($range_start);
        
        $range_end = '';
        if (!empty(Input::get("filter.range_end"))) {
            $range_end = Input::get("filter.range_end");
        }
        $filters_data['range_end'] = Security::htmlentities($range_end);
        
        return $filters_data;
    }
    
    /**
     *
     * @param string $view_template
     * @return int
     */
    public function process_form(string $view_template): int
    {
        $result = 0;
        $whitelabel = $this->get_whitelabel();
        
        if (empty($whitelabel)) {
            return self::RESULT_NULL_DATA;
        }
        
        $withdrawals_statuses = Helpers_Withdrawal_Method::get_withdrawals_statuses();
        
        $currencies = Lotto_Settings::getInstance()->get("currencies");
        
        $methods = Model_Whitelabel_Withdrawal::get_whitelabel_withdrawals($whitelabel);
        $kmethods = array_values($methods);
        
        $filters_data = $this->prepare_filters_data();
        
        list(
            $filter_add,
            $params
        ) = $this->prepare_filters($kmethods);
        
        $result_withdrawal_request = Model_Withdrawal_Request::count_for_whitelabel_filtered($whitelabel);
        
        if (is_null($result_withdrawal_request)) {
            return self::RESULT_NULL_COUNTED;
        }
        
        $count = $result_withdrawal_request['count'];
        
        $config = [
            'pagination_url' => '/withdrawals' . '?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('withdrawalspagination', $config);

        $columns = [
            'id' => 'desc',
            'amount' => 'desc',
            'date_confirmed' => 'desc'
        ];
        $defaults = [
            'id',
            'desc'
        ];
        $sort = Lotto_Helper::get_sort($columns, $defaults, '/withdrawals');
        
        $withdrawals = Model_Withdrawal_Request::get_full_data_for_whitelabel_filtered(
            $whitelabel,
            $pagination,
            $sort,
            $params,
            $filter_add
        );
        
        $source = Session::get("source");

        $show_cancel = true;

        if ($source != "admin" && Helpers_Whitelabel::is_V1((int)$whitelabel['type'])) {
            $show_cancel = false;
        }
        
        $inside = View::forge($view_template);
        $inside->set("withdrawals_statuses", $withdrawals_statuses);
        $inside->set("filters_data", $filters_data);
        $inside->set("pages", $pagination);
        $inside->set("sort", $sort);
        $inside->set("currencies", $currencies);
        $inside->set("withdrawals", $withdrawals);
        $inside->set("methods", $methods);
        $inside->set("show_cancel", $show_cancel);
        
        $this->inside = $inside;
        
        return self::RESULT_OK;
    }
    
    /**
     *
     * @param array $whitelabel
     * @param array $withdrawal
     * @return array
     */
    public static function prepare_single_to_show($whitelabel, $withdrawal): array
    {
        $withdrawal_data = [];
                                
        $withdrawal_data['token'] = $whitelabel['prefix'] . 'W' . $withdrawal['token'];

        $user_token = $whitelabel['prefix'] . 'U' . $withdrawal['utoken'];
        $user_data_temp = $user_token . ' &bull; ';
        if (!empty($withdrawal['name']) ||
            !empty($withdrawal['surname'])
        ) {
            $user_data_temp .= $withdrawal['name'] . ' ' . $withdrawal['surname'];
        } else {
            $user_data_temp .= _("anonymous");
        }
        $withdrawal_data['user_data_full'] = Security::htmlentities($user_data_temp);

        $withdrawal_data['email'] = Security::htmlentities($withdrawal['email']);

        $user_balance_danger_class = '';
        if ($withdrawal['balance'] < $withdrawal['amount'] &&
            (int)$withdrawal['status'] !== Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED
        ) {
            $user_balance_danger_class = ' class="text-danger"';
        }
        $withdrawal_data['balance_danger_class'] = $user_balance_danger_class;

        $withdrawal_data['amount_manager'] = Lotto_View::format_currency(
            $withdrawal['amount_manager'],
            $withdrawal['manager_currency_code'],
            true
        );

        $withdrawal_data['user_amount_show'] = false;
        if ($withdrawal['withdrawal_currency_code'] !== $withdrawal['manager_currency_code']) {
            $withdrawal_data['user_amount_show'] = true;
            $user_amount_text = Lotto_View::format_currency(
                $withdrawal['amount'],
                $withdrawal['withdrawal_currency_code'],
                true
            );
            $withdrawal_data['user_amount'] = _("User currency") .
                ": " . $user_amount_text;
        }

        $balance_currency_tab = [
            'id' => $withdrawal['user_currency_id'],
            'code' => $withdrawal['user_currency_code'],
            'rate' => $withdrawal['user_currency_rate'],
        ];
        $balance_in_manager_curr = Helpers_Currency::get_recalculated_to_given_currency(
            $withdrawal['balance'],
            $balance_currency_tab,
            $withdrawal['manager_currency_code']
        );
        $withdrawal_data['balance_in_manager'] = Lotto_View::format_currency(
            $balance_in_manager_curr,
            $withdrawal['manager_currency_code'],
            true
        );

        $withdrawal_data['user_balance_show'] = false;
        if ($withdrawal['user_currency_code'] !== $withdrawal['manager_currency_code']) {
            $withdrawal_data['user_balance_show'] = true;
            $user_balance_text = Lotto_View::format_currency(
                $withdrawal['balance'],
                $withdrawal['user_currency_code'],
                true
            );
            $withdrawal_data['user_balance'] = _("User currency") .
                ": " . $user_balance_text;
        }

        $withdrawal_data['date'] = Lotto_View::format_date(
            $withdrawal['date'],
            IntlDateFormatter::MEDIUM,
            IntlDateFormatter::SHORT
        );

        if (!empty($withdrawal['date_confirmed'])) {
            $withdrawal_data['date_confirmed'] = Lotto_View::format_date(
                $withdrawal['date_confirmed'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );
        }

        $status_span_class = "";
        $status_text = "";
        switch ($withdrawal['status']) {
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_PENDING:
                $status_span_class = "";
                $status_text = _("Pending");
                break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_APPROVED:
                $status_span_class = "text-success";
                $status_text = _("Approved");
                break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_DECLINED:
                $status_span_class = "text-danger";
                $status_text = _("Declined");
                break;
            case Helpers_Withdrawal_Method::STATUS_WITHDRAWAL_CANCELED:
                $status_span_class = "text-warning";
                $status_text = _("Canceled");
                break;
        }
        $withdrawal_data['status_span_class'] = $status_span_class;
        $withdrawal_data['status_text'] = $status_text;
        
        return $withdrawal_data;
    }
}
