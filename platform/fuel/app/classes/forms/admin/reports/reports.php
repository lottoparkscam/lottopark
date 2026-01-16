<?php

use Repositories\{
    Orm\TransactionRepository,
    WhitelabelRaffleTicketRepository
};

/**
 * Description of Forms_Admin_Reports_Reports
 */
final class Forms_Admin_Reports_Reports extends Forms_Report_Finance
{
	private TransactionRepository $transactionRepository;
    private WhitelabelRaffleTicketRepository $raffleTicketRepository;

    /**
     *
     * @var array
     */
    private $additional_rows_for_whitelabel_list = [];
    
    /**
     *
     * @var array
     */
    private $currency_tab_for_process = [];
    
    /**
     *
     * @var array
     */
    private $reports_data = [];

    /**
     *
     * @param int $source
     */
    public function __construct(
        int $source = Helpers_General::SOURCE_WHITELABEL
    ) {
        $this->source = $source;
        
        if (Input::get("filter.whitelabels_extended") !== null &&
            (string)Input::get("filter.whitelabels_extended") !== "a" &&
            Input::get("filter.range_start") !== null
        ) {
            $this->should_process = true;
        }

		$this->transactionRepository = Container::get(TransactionRepository::class);
        $this->raffleTicketRepository = Container::get(WhitelabelRaffleTicketRepository::class);
    }
    
    /**
     *
     * @return void
     */
    public function prepare_inside(): void
    {
        if (empty($this->inside)) {
            $path_to_view = "admin/reports/reports";
            $this->inside = Presenter::forge($path_to_view);
        }
    }
    
    /**
     *
     * @return array
     */
    public function get_additional_rows_for_whitelabel_list(): array
    {
        $this->additional_rows_for_whitelabel_list = [
            "full" => _("Full report"),
            "all" => _("All WhiteLabels"),
            "all_v1" => _("All WhiteLabels V1"),
            "all_v2" =>  _("All WhiteLabels V2"),
        ];
        
        return $this->additional_rows_for_whitelabel_list;
    }
    
    /**
     *
     * @param string $whitelabel_extended Could be null
     * @return \Forms_Admin_Reports_Reports
     */
    public function set_whitelabel_type(
        string $whitelabel_extended = null
    ): Forms_Report_Finance {
        if ($whitelabel_extended === "a") {
            // For this option form should not be processed
            $this->whitelabel_type = null;
            $this->should_process = false;
        } elseif ($whitelabel_extended === "full") {
            $this->whitelabel_type = null;
            $this->is_full_report = true;
        } elseif ($whitelabel_extended === "all") {
            $this->whitelabel_type = null;
        } elseif ($whitelabel_extended === "all_v1") {
            $this->whitelabel_type = Helpers_General::WHITELABEL_TYPE_V1;
        } elseif ($whitelabel_extended === "all_v2") {
            $this->whitelabel_type = Helpers_General::WHITELABEL_TYPE_V2;
        } else {
            $this->whitelabel_type = null;
        }
        
        return $this;
    }
    
    /**
     *
     * @param int $whitelabel_id
     * @return array
     */
    public function get_manager_currency_tab(int $whitelabel_id = null): array
    {
        $manager_currency_tab = [];
        if (!empty($whitelabel_id)) {
            $whitelabel = Model_Whitelabel::find_by_pk($whitelabel_id);

            if (!empty($whitelabel)) {
                $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                    true,
                    "",
                    $whitelabel->manager_site_currency_id
                );
            }
        } else {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(true, "USD");
        }
        
        return $manager_currency_tab;
    }
    
    /**
     *
     * @return void
     */
    private function prepare_and_set_currency_tab_for_process(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        if (empty($this->currency_tab_for_process)) {
            $this->currency_tab_for_process = [];

            $whitelabel_id = $this->get_whitelabel_id();

            if (!empty($whitelabel_id)) {
                $this->currency_tab_for_process = $this->get_manager_currency_tab($whitelabel_id);
            } else {
                $this->currency_tab_for_process = Helpers_Currency::get_mtab_currency(true, "USD");
            }
        }
    }
    
    /**
     *
     * @return array
     */
    public function get_currency_tab_for_process(): array
    {
        $this->prepare_and_set_currency_tab_for_process();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("currency_tab_for_process", $this->currency_tab_for_process);
        }
        
        return $this->currency_tab_for_process;
    }
    
    /**
     *
     * @param string $whitelabel_extended Could be null
     * @return \Forms_Admin_Reports_Reports
     */
    public function set_whitelabel_id(
        string $whitelabel_extended = null
    ): Forms_Report_Finance {
        if (!empty($whitelabel_extended)) {
            $additional_whitelabel_list = $this->get_additional_rows_for_whitelabel_list();
            $whitelabel_types_to_ignore = array_keys($additional_whitelabel_list);

            if (!in_array($whitelabel_extended, $whitelabel_types_to_ignore)) {
                $this->whitelabel_id = (int) $whitelabel_extended;
                //if ($this->get_should_set_inside()) {
                $this->inside->set("whitelabel_id", $this->whitelabel_id);
                //}
            }
        }
        
        return $this;
    }
    
    /**
     *
     * @param int $whitelabel_id
     * @return \Forms_Admin_Reports_Reports
     */
    public function set_whitelabel_id_for_full(
        int $whitelabel_id = null
    ): Forms_Admin_Reports_Reports {
        if (!empty($whitelabel_id)) {
            $this->whitelabel_id = $whitelabel_id;
        }
        
        return $this;
    }
    
    /**
     * This returns needed entrance for filter for admin reports
     *
     * @return array
     */
    public function get_additional_rows_for_filters(): array
    {
        $additional_rows_for_filters = [];
        
        $additional_rows_for_whitelabel = $this->get_additional_rows_for_whitelabel_list();
        
        foreach ($additional_rows_for_whitelabel as $key => $value) {
            $single_row = [
                "id" => $key,
                "name" => $value,
            ];
            $additional_rows_for_filters[] = $single_row;
        }
        
        return $additional_rows_for_filters;
    }
    
    /**
     *
     * @return array
     */
    protected function get_extended_whitelabels_list(): array
    {
        $additional_rows_for_filters = $this->get_additional_rows_for_filters();
        $result_of_short_list = Model_Whitelabel::get_all_as_short_list();

        $extended_list_of_whitelabels = array_merge(
            $additional_rows_for_filters,
            $result_of_short_list
        );

        if (!empty($extended_list_of_whitelabels)) {
            $this->inside->set("extended_list_of_whitelabels", $extended_list_of_whitelabels);
        }
        
        return $extended_list_of_whitelabels;
    }

    /**
     *
     * @param string $filter_add
     * @param array $params
     * @return array
     */
    protected function prepare_filters_for_active(
        string $filter_add,
        array $params
    ): array {
        $params_active = $params;
        $filter_add_active = $filter_add;

        $filter_add_active .= " AND whitelabel_user.is_deleted = :is_deleted ";
        $params_active[] = [":is_deleted", 0];

        $filter_add_active .= " AND whitelabel_user.is_active = :is_active ";
        $params_active[] = [":is_active", 1];

//        if (isset($this->whitelabel) &&
//            (int) $this->whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED
//        ) {
//            $filter_add_active .= " AND whitelabel_user.is_confirmed = :is_confirmed ";
//            $params_active[] = [":is_confirmed", 1];
//        }

        return [
            $filter_add_active,
            $params_active
        ];
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_register_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_register,
            $params_dates_register
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user.date_register");

        $this->filter_add_register_count = $this->filter_add . $filter_dates_register;
        $this->params_register_count = array_merge($this->params, $params_dates_register);

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        // Get reg user data
        $this->register_count = Model_Whitelabel_User::get_counted_data_for_admin_reports(
            $this->filter_add_register_count,
            $this->params_register_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_register_confirmed_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_register_confirmed,
            $params_dates_register_confirmed
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user.date_register");

        $filter_add_register_confirmed_count = $this->filter_add . $filter_dates_register_confirmed;
        $params_register_confirmed_count = array_merge($this->params, $params_dates_register_confirmed);

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        // Get reg user data
        $this->register_confirmed_count = Model_Whitelabel_User::get_counted_data_for_admin_reports(
            $filter_add_register_confirmed_count,
            $params_register_confirmed_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report,
            true
        );
    }
    
    
    
    /**
     *
     * @return void
     */
    protected function prepare_active_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        $filter_add_register_count = $this->get_filter_add_register_count();
        $params_register_count = $this->get_params_register_count();
        
        list(
            $filter_add_active,
            $params_active
        ) = $this->prepare_filters_for_active(
            $filter_add_register_count,
            $params_register_count
        );
        
        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
            
        // Get active users data
        $this->active_count = Model_Whitelabel_User::get_counted_data_for_admin_reports(
            $filter_add_active,
            $params_active,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_first_time_deposit_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_first_time_deposit_count,
            $params_dates_first_time_deposit_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user.first_deposit");

        $filter_add_first_time_deposit_count = $this->filter_add .
            $filter_dates_first_time_deposit_count;
        $params_first_time_deposit_count = array_merge(
            $this->params,
            $params_dates_first_time_deposit_count
        );

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        // Get data for first time deposit
        $this->first_time_deposit_count = Model_Whitelabel_User::get_counted_data_for_admin_reports(
            $filter_add_first_time_deposit_count,
            $params_first_time_deposit_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_second_time_deposit_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_second_time_deposit_count,
            $params_dates_second_time_deposit_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user.second_deposit");

        $filter_add_second_time_deposit_count = $this->filter_add .
            $filter_dates_second_time_deposit_count;
        $params_second_time_deposit_count = array_merge(
            $this->params,
            $params_dates_second_time_deposit_count
        );

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        // Get data for first time deposit
        $this->second_time_deposit_count = Model_Whitelabel_User::get_counted_data_for_admin_reports(
            $filter_add_second_time_deposit_count,
            $params_second_time_deposit_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_first_time_purchase_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_first_time_payment_count,
            $params_dates_first_time_payment_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user.first_purchase");

        $filter_add_first_time_payment_count = $this->filter_add .
            $filter_dates_first_time_payment_count;
        $params_first_time_payment_count = array_merge(
            $this->params,
            $params_dates_first_time_payment_count
        );

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        // Get data for first time purchase
        $this->first_time_purchase_count = Model_Whitelabel_User::get_counted_data_for_admin_reports(
            $filter_add_first_time_payment_count,
            $params_first_time_payment_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_second_time_purchase_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_second_time_payment_count,
            $params_dates_second_time_payment_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user.second_purchase");

        $filter_add_second_time_payment_count = $this->filter_add .
            $filter_dates_second_time_payment_count;
        $params_second_time_payment_count = array_merge(
            $this->params,
            $params_dates_second_time_payment_count
        );

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        // Get data for first time purchase
        $this->second_time_purchase_count = Model_Whitelabel_User::get_counted_data_for_admin_reports(
            $filter_add_second_time_payment_count,
            $params_second_time_payment_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_tickets_paid_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_tickets_count,
            $params_dates_tickets_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user_ticket.date");

        $filter_add_tickets_count = $this->filter_add . $filter_dates_tickets_count;
        $params_tickets_count = array_merge($this->params, $params_dates_tickets_count);
        
        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $this->tickets_count = Model_Whitelabel_User_Ticket::get_counted_data_for_admin_reports(
            $filter_add_tickets_count,
            $params_tickets_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report,
            false
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_lines_paid_sum(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_lines_count,
            $params_dates_lines_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user_ticket.date");

        $filter_add_lines_count = $this->filter_add . $filter_dates_lines_count;
        $params_lines_count = array_merge($this->params, $params_dates_lines_count);

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $this->lines_count = Model_Whitelabel_User_Ticket::get_line_sum_for_admin_reports(
            $filter_add_lines_count,
            $params_lines_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_bonus_tickets_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_bonus_tickets_count,
            $params_dates_bonus_tickets_count
        ) = $this->prepare_dates_based_on_column_name('whitelabel_user_ticket.date');

        $filter_add_bonus_tickets_count = $this->filter_add . $filter_dates_bonus_tickets_count;
        $params_bonus_tickets_count = array_merge($this->params, $params_dates_bonus_tickets_count);
        
        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $this->bonus_tickets_count = Model_Whitelabel_User_Ticket::get_counted_data_for_admin_reports(
            $filter_add_bonus_tickets_count,
            $params_bonus_tickets_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report,
            true
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_tickets_win_data(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        if ($this->tickets_win_data_pulled) {
            return ;
        }
        
        list(
            $filter_dates_tickets_win_count,
            $params_dates_tickets_win_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user_ticket.date");

        $filter_add_tickets_win_count = $this->filter_add . $filter_dates_tickets_win_count;
        $params_tickets_win_count = array_merge($this->params, $params_dates_tickets_win_count);
        
        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $win_data = Model_Whitelabel_User_Ticket::get_win_data_for_reports(
            $filter_add_tickets_win_count,
            $params_tickets_win_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
        
        $this->tickets_win_count = $win_data['count'];
        
        $tickets_win_sum_prize = "0";
        if (!empty($win_data)) {
            if (empty($whitelabel_id) &&
                isset($win_data['sum_prize_usd'])
            ) {
                $tickets_win_sum_prize = $win_data['sum_prize_usd'];
            } elseif (isset($win_data['sum_prize_manager'])) {
                $tickets_win_sum_prize = $win_data['sum_prize_manager'];
            }
        }
        
        $this->tickets_win_sum_prize = $tickets_win_sum_prize;
        
        $this->tickets_win_data_pulled = true;
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_deposits_count(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_transactions_count,
            $params_dates_transactions_count
        ) = $this->prepare_dates_based_on_column_name("whitelabel_transaction.date");

        $filter_add_transactions_count = $this->filter_add . $filter_dates_transactions_count;
        $params_transactions_count = array_merge($this->params, $params_dates_transactions_count);
        
        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $this->deposits_count = Model_Whitelabel_Transaction::get_counted_deposits_for_reports(
            $filter_add_transactions_count,
            $params_transactions_count,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_deposit_amount_value(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_deposit_amount,
            $params_dates_deposit_amount
        ) = $this->prepare_dates_based_on_column_name("whitelabel_transaction.date");

        $filter_add_deposit_amount = $this->filter_add . $filter_dates_deposit_amount;
        $params_deposit_amount = array_merge($this->params, $params_dates_deposit_amount);
        
        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $deposit_amount_result = Model_Whitelabel_Transaction::get_sum_deposits_for_admin_reports(
            $filter_add_deposit_amount,
            $params_deposit_amount,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );

        $deposit_amount_value = "0";
        if (!empty($deposit_amount_result)) {
            if (empty($whitelabel_id) &&
                isset($deposit_amount_result[0]['sum_usd'])
            ) {
                $deposit_amount_value = $deposit_amount_result[0]['sum_usd'];
            } elseif (isset($deposit_amount_result[0]['sum_manager'])) {
                $deposit_amount_value = $deposit_amount_result[0]['sum_manager'];
            }
        }
        
        $this->deposit_amount_value = $deposit_amount_value;
    }
    
    /**
     * At this moment this function is unused but prepared.
     *
     * @return void
     */
    protected function prepare_transactions_deposit_details(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_deposit_details,
            $params_dates_deposit_details
        ) = $this->prepare_dates_based_on_column_name("whitelabel_transaction.date");

        $filter_add_deposit_details = $this->filter_add . $filter_dates_deposit_details;
        $params_deposit_details = array_merge($this->params, $params_dates_deposit_details);

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $this->transactions_deposit_details = Model_Whitelabel_Transaction::get_sum_deposits_by_currency_for_admin_reports(
            $filter_add_deposit_details,
            $params_deposit_details,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );
    }
    
    /**
     *
     * @return array
     */
    public function prepare_and_get_bonus_cost_sum(): array
    {
        $sale_amounts = [];
        if (!$this->get_should_process()) {
            return $sale_amounts;
        }

        $dates = [
            $this->date_start_value,
            $this->date_end_value
        ];
        
        list(
            $filter_add_dates_user_ticket,
            $params_dates_user_ticket
        ) = $this->prepare_dates_based_on_column_name("whitelabel_user_ticket.date");

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();

        $sumBonusCostUSD = null;
        $sumBonusCostManager = null;
        $bonusResultUser = Model_Whitelabel_User_Ticket::get_sums_bonuses_for_admin_reports(
            $filter_add_dates_user_ticket,
            $params_dates_user_ticket,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );

        $bonusResultRaffle = $this->raffleTicketRepository->getSumsBonusesForAdminReports(
            $dates,
            $whitelabel_id,
            $whitelabel_type,
            $this->language,
            $this->country,
            $is_full_report
        );

        foreach ([$bonusResultUser, $bonusResultRaffle->as_array()] as $ticketBonusResults) {
            if (isset($ticketBonusResults[0]['sum_bonus_cost_manager'])) {
                $sumBonusCostUSD += $ticketBonusResults[0]['sum_bonus_cost_usd'];
                $sumBonusCostManager += $ticketBonusResults[0]['sum_bonus_cost_manager'];
            }
        }

        $sale_amounts['sum_bonus_cost_usd'] = $sumBonusCostUSD;
        $sale_amounts['sum_bonus_cost_manager'] = $sumBonusCostManager;

        return $sale_amounts;
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_sale_amount(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_sale_amount,
            $params_dates_sale_amount
        ) = $this->prepare_dates_based_on_column_name('date');

        $filter_add_sale_amount = $this->filter_add . $filter_dates_sale_amount;
        $params_sale_amount = array_merge($this->params, $params_dates_sale_amount);

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $sale_amounts_result = Model_Whitelabel_Transaction::get_sums_sales_for_admin_reports(
            $filter_add_sale_amount,
            $params_sale_amount,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );

        $sale_amounts = [
            'amount_manager' => '0',
            'amount_usd' => '0',
            'income_manager' => '0',
            'income_usd' => '0',
            'cost_manager' => '0',
            'cost_usd' => '0',
            'margin_manager' => '0',
            'margin_usd' => '0',
            'payment_cost_manager' => '0',
            'payment_cost_usd' => '0'
        ];
        if (!empty($sale_amounts_result) &&
            isset($sale_amounts_result[0])
        ) {
            $sale_amounts = $sale_amounts_result[0];
        }

		$dates = [
			$this->date_start_value,
			$this->date_end_value
		];

		$paymentCostForDeposit = $this->transactionRepository->getPaymentCostForDepositOnly(
			$dates,
			$this->get_language(),
			$this->get_country(),
			$whitelabel_id,
			$whitelabel_type,
			$is_full_report
		);
		// Add deposit payment_cost_manager and payment_cost_usd
		$sale_amounts['payment_cost_manager'] += $paymentCostForDeposit['paymentCostManager'];
		$sale_amounts['payment_cost_usd'] += $paymentCostForDeposit['paymentCostUsd'];

        $bonus_cost_sums = $this->prepare_and_get_bonus_cost_sum();
        
        $sale_amounts = array_merge($sale_amounts, $bonus_cost_sums);
        
        $this->sale_amounts = $sale_amounts;
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_finance_data(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        $sort_data = $this->get_sort_data();
        
        list(
            $filter_dates_finance,
            $params_dates_finance
        ) = $this->prepare_dates_based_on_column_name("wut.date");

        $filter_add_finance = $this->filter_add . $filter_dates_finance;
        $params_finance = array_merge($this->params, $params_dates_finance);

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $finances_data = Model_Whitelabel_User_Ticket::getOptimizedSumsPaidForFullReports(
            $filter_add_finance,
            $params_finance,
            $sort_data,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );

        $finance_data = [];
        foreach ($finances_data as $lottery_item) {
            $finance_data[$lottery_item['id']] = $lottery_item;
        }
        
        $this->finance_data = $finance_data;
    }
    
    /**
     *
     * @return void
     */
    protected function prepare_commissions_sum_value(): void
    {
        if (!$this->get_should_process()) {
            return ;
        }
        
        list(
            $filter_dates_total_commissions,
            $params_dates_total_commissions
        ) = $this->prepare_dates_based_on_column_name("wt.date");

        $filter_add_total_commissions = $this->filter_add . $filter_dates_total_commissions;
        $params_total_commissions = array_merge($this->params, $params_dates_total_commissions);

        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $total_commissions = Model_Whitelabel_Aff_Commission::get_sums_for_whitelabel_for_admin_reports(
            $filter_add_total_commissions,
            $params_total_commissions,
            $whitelabel_type,
            $whitelabel_id,
            $is_full_report
        );

        $commissions_sum_value = '0';
        if (!empty($total_commissions[0])) {
            if (empty($whitelabel_id) &&
                isset($total_commissions[0]['commission_usd_sum'])
            ) {
                $commissions_sum_value = $total_commissions[0]['commission_usd_sum'];
            } elseif (isset($total_commissions[0]['commission_manager_sum'])) {
                $commissions_sum_value = $total_commissions[0]['commission_manager_sum'];
            }
        }
        
        $this->commissions_sum_value = $commissions_sum_value;
    }
    
    /**
     *
     * @param int $type
     * @return void
     */
    protected function prepare_payment_methods_report(
        int $type = Helpers_General::TYPE_TRANSACTION_PURCHASE
    ): void {
        if (!$this->get_should_process()) {
            return ;
        }
        
        $date_start_value = $this->get_date_start_value();
        $date_end_value = $this->get_date_end_value();
        
        $whitelabel_type = $this->get_whitelabel_type();
        $whitelabel_id = $this->get_whitelabel_id();
        
        $is_full_report = $this->is_full_report();
        
        $language = $this->get_language();
        $country = $this->get_country();
        
        // Get all reports and set it to variable
        switch ($type) {
            case Helpers_General::TYPE_TRANSACTION_PURCHASE:
                $this->payment_methods_purchase_report = Model_Whitelabel::get_payment_types_admin_report(
                    $date_start_value,
                    $date_end_value,
                    $whitelabel_type,
                    $whitelabel_id,
                    Helpers_General::TYPE_TRANSACTION_PURCHASE,
                    $language,
                    $country,
                    $is_full_report
                );
                break;
            case Helpers_General::TYPE_TRANSACTION_DEPOSIT:
                $this->payment_methods_deposit_report = Model_Whitelabel::get_payment_types_admin_report(
                    $date_start_value,
                    $date_end_value,
                    $whitelabel_type,
                    $whitelabel_id,
                    Helpers_General::TYPE_TRANSACTION_DEPOSIT,
                    $language,
                    $country,
                    $is_full_report
                );
                break;
        }
    }
    
    /**
     *
     * @param string $filter_whitelabels_extended
     * @return bool
     */
    private function check_whitelabels_extended(
        string $filter_whitelabels_extended = null
    ): bool {
        if ($filter_whitelabels_extended === "full") {
            return true;
        }
        return false;
    }
    
    /**
     *
     * @return array|null
     */
    private function get_list_of_whitelabels():? array
    {
        $list_of_whitelabels = Model_Whitelabel::get_all_as_array();
        return $list_of_whitelabels;
    }
    
    /**
     *
     * @return array
     */
    private function get_main_info(): array
    {
        $main_info = [];
        
        $main_info['date_start'] = $this->get_date_start_value();
        $main_info['date_end'] = $this->get_date_end_value();
        $main_info['reg_count'] = $this->get_register_count();
        $main_info['active_count'] = $this->get_active_count();
        $main_info['register_confirmed_count'] = $this->get_register_confirmed_count();
        $main_info['ftd_count'] = $this->get_first_time_deposit_count();
        $main_info['std_count'] = $this->get_second_time_deposit_count();
        $main_info['ftp_count'] = $this->get_first_time_purchase_count();
        $main_info['stp_count'] = $this->get_second_time_purchase_count();
        $main_info['tickets_count'] = $this->get_tickets_paid_count();
        $main_info['lines_count'] = $this->get_lines_paid_sum();
        $main_info['bonus_tickets_count'] = $this->get_bonus_tickets_count();
        $main_info['tickets_win_count'] = $this->get_tickets_win_count();
        $main_info['tickets_win_sum_prize'] = $this->get_tickets_win_sum_prize();
        $main_info['deposits_count'] = $this->get_deposits_count();
        $main_info['deposit_amount_value'] = $this->get_deposit_amount_value();
        $main_info['sale_amounts'] = $this->get_sale_amounts();
        $main_info['commissions_sum_value'] = $this->get_commissions_sum_value();
        
        return $main_info;
    }
    
    /**
     *
     * @param array $single_whitelabel If null it means that is not for full report
     * @return array
     */
    private function get_single_whitelabel_data_report(array $single_whitelabel = null): array
    {
        $main_name = "";
        if ($this->is_full_report && !empty($single_whitelabel)) {
            $main_name = $single_whitelabel['name'];
        }

        $currency_tab_for_process = $this->get_currency_tab_for_process();
        // Only in that case currency_tab_for_process has to be reset to null
        // because it is needed to check currency for all whitelabels
        $this->currency_tab_for_process = null;

        $main_info = $this->get_main_info();

        $finance_data = $this->get_finance_data();

        $sort_data = $this->get_sort_data();

        $payment_methods_purchase_report = $this->get_payment_methods_purchase_report();

        $payment_methods_deposit_report = $this->get_payment_methods_deposit_report();

        $single_whitelabel_data_report = [
            'main_name' => $main_name,
            'currency_tab_for_process' => $currency_tab_for_process,
            'main_info' => $main_info,
            'finance_data' => $finance_data,
            'sort' => $sort_data,
            'payment_methods_purchase_report' => $payment_methods_purchase_report,
            'payment_methods_deposit_report' => $payment_methods_deposit_report,
        ];
        
        return $single_whitelabel_data_report;
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $filter_whitelabels_extended = Input::get('filter.whitelabels_extended');
        
        $this->set_whitelabel_type($filter_whitelabels_extended);
        
        $this->prepare_inside();
        $this->prepare_countries();
        $this->prepare_languages();
        $this->get_extended_whitelabels_list();
        
        $this->set_is_full_report();
        
        if ($this->get_should_process()) {
            // Prepare dates (start and end based on data from Front-end)
            $this->prepare_dates(
                Input::get('filter.range_start'),
                Input::get('filter.range_end')
            );

            // Prepare base filters (language, coutry)
            $this->prepare_filters(
                Input::get("filter.language"),
                Input::get("filter.country")
            );

            if (!is_null($this->language)) {
                $this->inside->set("language", $this->language);
            }
            
            // For full report!
            if ($this->check_whitelabels_extended($filter_whitelabels_extended)) {
                $this->set_should_set_inside(false);
                
                $list_of_whitelabel_to_process = $this->get_list_of_whitelabels();
        
                if (empty($list_of_whitelabel_to_process)) {
                    return ;
                }
                
                foreach ($list_of_whitelabel_to_process as $single_whitelabel) {
                    $this->set_whitelabel_id_for_full((int)$single_whitelabel['id']);
                    $single_whitelabel_data_report = $this->get_single_whitelabel_data_report($single_whitelabel);
                    $this->reports_data[] = $single_whitelabel_data_report;
                    $this->tickets_win_data_pulled = false;
                }
            } else {
                $this->set_whitelabel_id($filter_whitelabels_extended);

                $single_whitelabel_data_report = $this->get_single_whitelabel_data_report();
                $this->reports_data[] = $single_whitelabel_data_report;
            }
            
            $this->inside->set('report_data', $this->reports_data);
        }
    }
}
