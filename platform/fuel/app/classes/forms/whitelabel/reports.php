<?php

use Repositories\{
    Orm\TransactionRepository,
    WhitelabelRaffleTicketRepository
};

/**
 * Description of Forms_Whitelabel_Reports
 */
final class Forms_Whitelabel_Reports extends Forms_Report_Finance
{
	private TransactionRepository $transactionRepository;
    private WhitelabelRaffleTicketRepository $raffleTicketRepository;

    /**
     *
     * @param int $source
     * @param array $whitelabel
     */
    public function __construct(
        int $source = Helpers_General::SOURCE_WHITELABEL,
        array $whitelabel = null
    ) {
        $this->source = $source;
        
        if (!empty($whitelabel)) {
            $this->whitelabel = $whitelabel;
            $this->whitelabel_id = (int)$whitelabel['id'];
        }
     
        if (Input::get("filter.range_start") !== null &&
            Input::get("filter.range_end") !== null &&
            !empty($whitelabel)
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
            $path_to_view = "whitelabel/reports/reports";
            $this->inside = Presenter::forge($path_to_view);
        }
    }
    
    /**
     *
     * @param string $whitelabel_extended
     * @return \Forms_Whitelabel_Reports
     */
    public function set_whitelabel_type(
        string $whitelabel_extended = null
    ): Forms_Report_Finance {
        return $this;
    }
    
    /**
     *
     * @param string $whitelabel_extended Could be null
     * @return \Forms_Whitelabel_Reports
     */
    public function set_whitelabel_id(
        string $whitelabel_extended = null
    ): Forms_Report_Finance {
        return $this;
    }
    
    /**
     *
     * @return array
     */
    protected function get_extended_whitelabels_list(): array
    {
        return [];
    }
    
    /**
     *
     * @return array
     */
    public function get_currency_tab_for_process(): array
    {
        return [];
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

        if (isset($this->whitelabel) &&
            (int) $this->whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED
        ) {
            $filter_add_active .= " AND whitelabel_user.is_confirmed = :is_confirmed ";
            $params_active[] = [":is_confirmed", 1];
        }

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
        ) = $this->prepare_dates_based_on_column_name("date_register");

        $this->filter_add_register_count = $this->filter_add . $filter_dates_register;
        $this->params_register_count = array_merge($this->params, $params_dates_register);

        $whitelabel_id = $this->get_whitelabel_id();
        
        // Get reg user data
        $this->register_count = Model_Whitelabel_User::get_data_count_for_reports(
            $this->filter_add_register_count,
            $this->params_register_count,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("date_register");

        $filter_add_register_confirmed_count = $this->filter_add . $filter_dates_register_confirmed;
        $params_register_confirmed_count = array_merge($this->params, $params_dates_register_confirmed);

        $whitelabel_id = $this->get_whitelabel_id();
        
        // Get reg user data
        $this->register_confirmed_count = Model_Whitelabel_User::get_data_count_for_reports(
            $filter_add_register_confirmed_count,
            $params_register_confirmed_count,
            $whitelabel_id,
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
        
        $whitelabel_id = $this->get_whitelabel_id();

        // Get active users data
        $this->active_count = Model_Whitelabel_User::get_data_count_for_reports(
            $filter_add_active,
            $params_active,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("first_deposit");

        $filter_add_first_time_deposit_count = $this->filter_add .
            $filter_dates_first_time_deposit_count;
        $params_first_time_deposit_count = array_merge(
            $this->params,
            $params_dates_first_time_deposit_count
        );

        $whitelabel_id = $this->get_whitelabel_id();
        
        // Get data for first time deposit
        $this->first_time_deposit_count = Model_Whitelabel_User::get_data_count_for_reports(
            $filter_add_first_time_deposit_count,
            $params_first_time_deposit_count,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("second_deposit");

        $filter_add_second_time_deposit_count = $this->filter_add .
            $filter_dates_second_time_deposit_count;
        $params_second_time_deposit_count = array_merge(
            $this->params,
            $params_dates_second_time_deposit_count
        );

        $whitelabel_id = $this->get_whitelabel_id();
        
        // Get data for first time deposit
        $this->second_time_deposit_count = Model_Whitelabel_User::get_data_count_for_reports(
            $filter_add_second_time_deposit_count,
            $params_second_time_deposit_count,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("first_purchase");
        
        $filter_add_first_time_payment_count = $this->filter_add .
            $filter_dates_first_time_payment_count;
        $params_first_time_payment_count = array_merge(
            $this->params,
            $params_dates_first_time_payment_count
        );
        
        $whitelabel_id = $this->get_whitelabel_id();
        
        // Get data for first time purchase
        $this->first_time_purchase_count = Model_Whitelabel_User::get_data_count_for_reports(
            $filter_add_first_time_payment_count,
            $params_first_time_payment_count,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("second_purchase");

        $filter_add_second_time_payment_count = $this->filter_add .
            $filter_dates_second_time_payment_count;
        $params_second_time_payment_count = array_merge(
            $this->params,
            $params_dates_second_time_payment_count
        );

        $whitelabel_id = $this->get_whitelabel_id();
        
        // Get data for first time purchase
        $this->second_time_purchase_count = Model_Whitelabel_User::get_data_count_for_reports(
            $filter_add_second_time_payment_count,
            $params_second_time_payment_count,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("date");

        $filter_add_tickets_count = $this->filter_add . $filter_dates_tickets_count;
        $params_tickets_count = array_merge($this->params, $params_dates_tickets_count);

        $whitelabel_id = $this->get_whitelabel_id();
        
        $this->tickets_count = Model_Whitelabel_User_Ticket::get_data_count_for_reports(
            $filter_add_tickets_count,
            $params_tickets_count,
            $whitelabel_id,
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
        ) = $this->prepare_dates_based_on_column_name("date");

        $filter_add_lines_count = $this->filter_add . $filter_dates_lines_count;
        $params_lines_count = array_merge($this->params, $params_dates_lines_count);

        $whitelabel_id = $this->get_whitelabel_id();
        
        $this->lines_count = Model_Whitelabel_User_Ticket::get_line_sum_for_reports(
            $filter_add_lines_count,
            $params_lines_count,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("date");

        $filter_add_bonus_tickets_count = $this->filter_add . $filter_dates_bonus_tickets_count;
        $params_bonus_tickets_count = array_merge($this->params, $params_dates_bonus_tickets_count);
        
        $whitelabel_id = $this->get_whitelabel_id();
        
        $this->bonus_tickets_count = Model_Whitelabel_User_Ticket::get_data_count_for_reports(
            $filter_add_bonus_tickets_count,
            $params_bonus_tickets_count,
            $whitelabel_id,
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
        
        $whitelabel_id = $this->get_whitelabel_id();
        
        $win_data = Model_Whitelabel_User_Ticket::get_win_data_for_reports(
            $filter_add_tickets_win_count,
            $params_tickets_win_count,
            null,
            $whitelabel_id
        );
        
        $this->tickets_win_count = $win_data['count'];
        
        $tickets_win_sum_prize = "0";
        if (isset($win_data['sum_prize_manager'])) {
            $tickets_win_sum_prize = $win_data['sum_prize_manager'];
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
        
        $whitelabel_id = $this->get_whitelabel_id();
        
        $this->deposits_count = Model_Whitelabel_Transaction::get_counted_deposits_for_reports(
            $filter_add_transactions_count,
            $params_transactions_count,
            null,
            $whitelabel_id
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
        ) = $this->prepare_dates_based_on_column_name("date");

        $filter_add_deposit_amount = $this->filter_add . $filter_dates_deposit_amount;
        $params_deposit_amount = array_merge($this->params, $params_dates_deposit_amount);

        $whitelabel_id = $this->get_whitelabel_id();
        
        $deposit_amount_result = Model_Whitelabel_Transaction::get_sum_deposits_for_reports(
            $filter_add_deposit_amount,
            $params_deposit_amount,
            $whitelabel_id
        );

        $deposit_amount_value = 0;
        if (!empty($deposit_amount_result) &&
            isset($deposit_amount_result[0]['sum_manager'])
        ) {
            $deposit_amount_value = $deposit_amount_result[0]['sum_manager'];
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
        ) = $this->prepare_dates_based_on_column_name("date");

        $filter_add_deposit_details = $this->filter_add . $filter_dates_deposit_details;
        $params_deposit_details = array_merge($this->params, $params_dates_deposit_details);

        $whitelabel_id = $this->get_whitelabel_id();
        
        $this->transactions_deposit_details = Model_Whitelabel_Transaction::get_sum_deposits_by_currency_for_reports(
            $filter_add_deposit_details,
            $params_deposit_details,
            $whitelabel_id
        );
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
        ) = $this->prepare_dates_based_on_column_name("date");

        $filter_add_sale_amount = $this->filter_add . $filter_dates_sale_amount;
        $params_sale_amount = array_merge($this->params, $params_dates_sale_amount);

        $whitelabel_id = $this->get_whitelabel_id();
        
        $sale_amounts_result = Model_Whitelabel_Transaction::get_sums_sales_for_reports(
            $filter_add_sale_amount,
            $params_sale_amount,
            $whitelabel_id
        );

        $sale_amounts = [
            'amount_manager' => '0',
            'income_manager' => '0',
            'cost_manager' => '0',
            'margin_manager' => '0',
            'payment_cost_manager' => '0'
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
			null,
			null
		);
		// Add deposit payment_cost_manager
		$sale_amounts['payment_cost_manager'] += $paymentCostForDeposit['paymentCostManager'];

        $sumBonusCostManager = null;
        $bonusResultUser = Model_Whitelabel_User_Ticket::get_sums_bonuses_for_reports(
            $filter_add_sale_amount,
            $params_sale_amount,
            $whitelabel_id
        );

        $bonusResultRaffle = $this->raffleTicketRepository->getSumsBonusesForReports(
            $whitelabel_id,
            $dates,
            $this->language,
            $this->country
        );

        foreach ([$bonusResultUser, $bonusResultRaffle->as_array()] as $ticketBonusResults) {
            if (isset($ticketBonusResults[0]['sum_bonus_cost_manager'])) {
                $sumBonusCostManager += $ticketBonusResults[0]['sum_bonus_cost_manager'];
            }
        }

        $sale_amounts['sum_bonus_cost_manager'] = $sumBonusCostManager;

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

        $whitelabel_id = $this->get_whitelabel_id();
        
        $finances_data = Model_Whitelabel_User_Ticket::getOptimizedSumsPaidForFullReports(
            $filter_add_finance,
            $params_finance,
            $sort_data,
            null,
            $whitelabel_id
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

        $whitelabel_id = $this->get_whitelabel_id();
        
        $total_commissions = Model_Whitelabel_Aff_Commission::get_sums_for_whitelabel_for_reports(
            $filter_add_total_commissions,
            $params_total_commissions,
            $whitelabel_id
        );

        $commissions_sum_value = '';
        if (isset($total_commissions[0]['commission_manager_sum'])) {
            $commissions_sum_value = $total_commissions[0]['commission_manager_sum'];
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
        
        $whitelabel_id = $this->get_whitelabel_id();
        
        $language = $this->get_language();
        $country = $this->get_country();
        
        // Get all reports and set it to variable
        switch ($type) {
            case Helpers_General::TYPE_TRANSACTION_PURCHASE:
                $this->payment_methods_purchase_report = Model_Whitelabel::get_payment_types_report(
                    $whitelabel_id,
                    $date_start_value,
                    $date_end_value,
                    Helpers_General::TYPE_TRANSACTION_PURCHASE,
                    $language,
                    $country
                );
                break;
            case Helpers_General::TYPE_TRANSACTION_DEPOSIT:
                $this->payment_methods_deposit_report = Model_Whitelabel::get_payment_types_report(
                    $whitelabel_id,
                    $date_start_value,
                    $date_end_value,
                    Helpers_General::TYPE_TRANSACTION_DEPOSIT,
                    $language,
                    $country
                );
                break;
        }
    }
    
    /**
     *
     * @return void
     */
    public function process_form(): void
    {
        $this->prepare_inside();
        $this->prepare_countries();
        $this->prepare_languages();
        
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

            // Prepare dates filters as date_register value as key
            $this->get_register_count();

            // Prepare confirmed users
            $this->get_register_confirmed_count();
            
            // Prepare active filters
            $this->get_active_count();

            // Prepare first time deposit filters dates
            $this->get_first_time_deposit_count();
            
            // Prepare second time deposit filters dates
            $this->get_second_time_deposit_count();
            
            // Prepare first time purchanse filters dates
            $this->get_first_time_purchase_count();
            
            // Prepare second time purchanse filters dates
            $this->get_second_time_purchase_count();

            // Prepare tickets paid filters dates
            $this->get_tickets_paid_count();
            
            // Prepare tickets paid filters dates
            $this->get_lines_paid_sum();
            
            // Prepare bonus tickets filters dates
            $this->get_bonus_tickets_count();
            
            // Win tickets count
            $this->get_tickets_win_count();
            
            // Win tickets prize
            $this->get_tickets_win_sum_prize();
            
            // Prepare transactions deposit count
            $this->get_deposits_count();
            
            // Prepare deposit amount manager data
            $this->get_deposit_amount_value();

            // Prepare transactions deposits filters dates
            //$this->set_transactions_deposit_details();

            // Prepare transactions saleamount filters dates
            $this->get_sale_amounts();

            // Prepare finance filters dates
            $this->get_finance_data();

            // Prepare total commissions filters dates
            $this->get_commissions_sum_value();
            
            /** PAYMENT TYPES REPORTS * */
            // Get all reports and set it to variable
            $this->get_payment_methods_purchase_report();
            
            $this->get_payment_methods_deposit_report();
        }
    }
}
