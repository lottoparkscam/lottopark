<?php

use Fuel\Core\View;
use Helpers\TransactionHelper;

/**
 * Description of list
 */
class Forms_Whitelabel_Transactions_List extends Forms_Main
{
    /**
     * Get Trait for date range preparation
     */
    use Traits_Gets_Date;
    
    /**
     *
     * @var type
     */
    private $whitelabel = [];
    
    /**
     *
     * @var int
     */
    private $source;
    
    /**
     *
     * @var View
     */
    private $inside;
    
    /**
     *
     * @var string
     */
    private $rparam = "";
    
    /**
     *
     * @var int
     */
    private $items_per_page = 25;
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     * @param string $rparam
     */
    public function __construct(
        int $source,
        array $whitelabel = [],
        string $rparam = "transactions"
    ) {
        if (!empty($source) && $source == Helpers_General::SOURCE_ADMIN) {
            if (Input::get("filter.whitelabel") != null &&
                Input::get("filter.whitelabel") != "a"
            ) {
                $whitelabel = [];
                $whitelabel['id'] = intval(Input::get("filter.whitelabel"));
            }
        }
        
        $this->source = $source;
        $this->whitelabel = $whitelabel;
        $this->rparam = $rparam;
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
     * @return Presenter_Whitelabel_Transactions_List
     */
    public function get_inside(): Presenter_Whitelabel_Transactions_List
    {
        return $this->inside;
    }
    
    /**
     *
     * @return string
     */
    public function get_rparam(): string
    {
        return $this->rparam;
    }
    
    /**
     * This function is to filter data pulled from different tables from DB
     *
     * @param array $whitelabel_payment_methods_indexed
     * @return array
     */
    private function prepare_filters(array $whitelabel_payment_methods_indexed): array
    {
        $filter_add = [];
        $params = [];
        $whitelabel = $this->get_whitelabel();
        $rparam = $this->get_rparam();
        
        if (Input::get("filter.id") != null) {
            $filter_add[] = " AND whitelabel_transaction.token = :token";
            $trans_token_pre = $whitelabel['prefix'] . ($rparam == "deposits" ? 'D' : 'P');
            $trans_token = str_ireplace($trans_token_pre, "", Input::get("filter.id"));
            $params[] = [":token", intval($trans_token)];
        }
        
        if (Input::get("filter.userid") != null) {
            $filter_add[] = " AND whitelabel_user.token = :utoken";
            $user_token_pre = $whitelabel['prefix'] . 'U';
            $user_token = str_ireplace($user_token_pre, "", Input::get("filter.userid"));
            $params[] = [":utoken", intval($user_token)];
        }
        
        if (Input::get("filter.status") != null &&
            Input::get("filter.status") != "a"
        ) {
            $filter_add[] = " AND whitelabel_transaction.status = :status";
            $params[] = [":status", intval(Input::get("filter.status"))];
        }
        
        if (Input::get("filter.method") != null &&
            Input::get("filter.method") != "a"
        ) {
            $whitelabel_payment_methods_index = (int)Input::get("filter.method");
            if (in_array(Input::get("filter.method"), ['g1'])) {
                $filter_add[] = " AND whitelabel_transaction.payment_method_type = :method";
                $method_temp = Input::get("filter.method");
                $method = $method_temp[1];
                $params[] = [":method", intval($method)];
            } elseif (isset($whitelabel_payment_methods_indexed[$whitelabel_payment_methods_index - 1])) {
                $filter_add[] = " AND whitelabel_transaction.payment_method_type = " .
                    Helpers_General::PAYMENT_TYPE_OTHER .
                    " AND whitelabel_transaction.whitelabel_payment_method_id = :method";
                $params[] = [":method", $whitelabel_payment_methods_indexed[$whitelabel_payment_methods_index - 1]['id']];
            }
        }
        
        if (Input::get("filter.email") != null) {
            $filter_add[] = " AND whitelabel_user.email LIKE :email";
            $params[] = [":email", '%' . Input::get("filter.email") . '%'];
        }

        /*DATE RANGE*/
        if (Input::get("filter.range_start") != '') {
            // get date ranges
            $dates = $this->prepare_dates();

            $filter_add[] = " AND whitelabel_transaction.date >= :date_start";
            $params[] = [":date_start", $dates['date_start']];

            $filter_add[] = " AND whitelabel_transaction.date <= :date_end";
            $params[] = [":date_end", $dates['date_end']];
        }
        
        $filter_add_whole = implode("", $filter_add);

        return [$filter_add_whole, $params];
    }
    
    /**
     * This function is for prepare data shown in the view
     *
     * @return array
     */
    private function prepare_filters_data(): array
    {
        $filters_data = [];
        
        $transaction_id = '';
        if (!is_null(Input::get("filter.id"))) {
            $transaction_id = Input::get("filter.id");
        }
        $filters_data['transaction_id'] = Security::htmlentities($transaction_id);
        
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
        $whitelabel = $this->get_whitelabel();
        $transaction_statuses = TransactionHelper::getStatuses();
        $rparam = $this->get_rparam();
        
        $user_currency = null;
        
        $whitelabel_payment_methods_without_currency = Model_Whitelabel_Payment_Method::get_payment_methods_for_whitelabel($whitelabel);
        $whitelabel_payment_methods_with_currency = Helpers_Currency::get_whitelabel_payment_methods_with_currency(
            $whitelabel,
            $whitelabel_payment_methods_without_currency,
            $user_currency
        );
        
        $whitelabel_payment_methods_indexed = array_values($whitelabel_payment_methods_with_currency);

        $filters_data = $this->prepare_filters_data();
        
        list(
            $filter_add,
            $params
        ) = $this->prepare_filters($whitelabel_payment_methods_indexed);
        
        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);
        $prepared_languages = Lotto_Helper::prepare_languages($whitelabel_languages);

        $type = Helpers_General::TYPE_TRANSACTION_PURCHASE;
        if ($rparam == "deposits") {
            $type = Helpers_General::TYPE_TRANSACTION_DEPOSIT;
        }

        $whitelabel_transactions_counted = Model_Whitelabel_Transaction::get_count_filtered_for_whitelabel_by_type(
            $whitelabel,
            $type,
            $params,
            $filter_add
        );
        
        if (is_null($whitelabel_transactions_counted)) {
            return self::RESULT_NULL_COUNTED;
        }
        
        $count = $whitelabel_transactions_counted['count'];

        $config = [
            'pagination_url' => '/' . $rparam . '?' . http_build_query(Input::get()),
            'total_items' => $count,
            'per_page' => $this->items_per_page,
            'uri_segment' => 'page'
        ];
        $pagination = Pagination::forge('transactionspagination', $config);

        $sort = Lotto_Helper::get_sort(
            [
                'id' => 'desc',
                'amount' => 'desc',
                'date_confirmed' => 'desc'
            ],
            [
                'id',
                'desc'
            ],
            '/' . $rparam
        );

        $whitelabel_transactions = Model_Whitelabel_Transaction::get_full_data_for_whitelabel_by_type(
            $whitelabel,
            $pagination,
            $sort,
            $params,
            $type,
            $filter_add
        );

        if (is_null($whitelabel_transactions)) { // If that situation happend it will be a problem
            return self::RESULT_NULL_DATA;
        }

        $this->inside = Presenter::forge($view_template);
        $this->inside->set("langs", $prepared_languages);
        $this->inside->set("type", $rparam);
        $this->inside->set("transaction_statuses", $transaction_statuses);
        $this->inside->set("filters_data", $filters_data);
        
        $this->inside->set("pages", $pagination);
        $this->inside->set("sort", $sort);
        $this->inside->set("methods", $whitelabel_payment_methods_with_currency);
        
        $this->inside->set("transactions", $whitelabel_transactions);
        
        return self::RESULT_OK;
    }
}
