<?php

/**
 * This class is the main class for Reports for admin and manager
 */
abstract class Forms_Report_Finance extends Forms_Main
{
    use Traits_Reports_Reports;
    
    /**
     *
     * @var int
     */
    protected $source = null;
    
    /**
     *
     * @var bool
     */
    protected $should_process = false;
    
    /**
     *
     * @var array
     */
    protected $whitelabel = [];
    
    /**
     *
     * @var null|int
     */
    protected $whitelabel_id = null;
    
    /**
     *
     * @var null|int
     */
    protected $whitelabel_type = null;
    
    /**
     *
     * @var bool
     */
    protected $is_full_report = false;

    /**
     *
     * @var Presenter_Presenter
     */
    protected $inside = null;
    
    /**
     *
     * @var bool
     */
    protected $should_set_inside = true;


    /**
     *
     * @var null|int
     */
    protected $register_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $filter_add_register_count = null;
    
    /**
     *
     * @var null|array
     */
    protected $params_register_count = null;
    
    
    /**
     *
     * @var null|int
     */
    protected $register_confirmed_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $active_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $first_time_purchase_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $second_time_purchase_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $first_time_deposit_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $second_time_deposit_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $tickets_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $lines_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $bonus_tickets_count = null;
    
    /**
     *
     * @var null|int
     */
    protected $tickets_win_count = null;
    
    /**
     *
     * @var bool
     */
    protected $tickets_win_data_pulled = false;
    
    /**
     *
     * @var bool
     */
    protected $tickets_win_sum_prize = null;
    
    /**
     *
     * @var null|int
     */
    protected $deposits_count = null;
    
    /**
     *
     * @var null|string
     */
    protected $deposit_amount_value = null;
    
    /**
     *
     * @var null|array
     */
    protected $transactions_deposit_details = null;
    
    /**
     *
     * @var null|array
     */
    protected $sale_amounts = null;
    
    /**
     *
     * @var null|array
     */
    protected $finance_data = null;
    
    /**
     *
     * @var null|string
     */
    protected $commissions_sum_value = null;
    
    /**
     *
     * @var null|array
     */
    protected $payment_methods_purchase_report = null;
    
    /**
     *
     * @var null|array
     */
    protected $payment_methods_deposit_report = null;
    
    /**
     *
     * @var array
     */
    protected $sort_data = [];
    
    /**
     *
     * @return bool
     */
    public function get_should_process(): bool
    {
        return $this->should_process;
    }
    
    /**
     *
     * @return bool
     */
    public function get_should_set_inside(): bool
    {
        return $this->should_set_inside;
    }

    /**
     *
     * @param bool $should_set_inside
     * @return \Forms_Report_Finance
     */
    public function set_should_set_inside(bool $should_set_inside): Forms_Report_Finance
    {
        $this->should_set_inside = $should_set_inside;
        return $this;
    }

    /**
     * Normally it should not be used, but this function is important for testing
     *
     * @param bool $should_process
     * @return \Forms_Report_Finance
     */
    public function set_should_process(bool $should_process): Forms_Report_Finance
    {
        $this->should_process = $should_process;
        return $this;
    }
    
    /**
     *
     * @return int|null
     */
    public function get_whitelabel_id():? int
    {
        if (!empty($this->whitelabel_id)) {
            return $this->whitelabel_id;
        }
        
        return null;
    }
    
    /**
     *
     * @return void
     */
    abstract public function prepare_inside(): void;
    
    /**
     *
     * @return Presenter_Presenter
     */
    public function get_inside(): Presenter_Presenter
    {
        if (empty($this->inside)) {
            $this->prepare_inside();
        }
        
        return $this->inside;
    }
    
    /**
     *
     * @param string $whitelabel_extended Could be null
     * @return \Forms_Report_Finance
     */
    abstract public function set_whitelabel_type(
        string $whitelabel_extended = null
    ): Forms_Report_Finance;
    
    /**
     *
     * @return int|null
     */
    public function get_whitelabel_type():? int
    {
        return $this->whitelabel_type;
    }
    
    /**
     *
     * @return bool
     */
    public function is_full_report(): bool
    {
        return $this->is_full_report;
    }
    
    /**
     *
     * @return \Forms_Report_Finance
     */
    public function set_is_full_report(): Forms_Report_Finance
    {
        $is_full_report = $this->is_full_report();
        $this->inside->set("check_full_report", $is_full_report);
        
        return $this;
    }
    
    /**
     *
     * @param string $whitelabel_extended Could be null
     * @return \Forms_Report_Finance
     */
    abstract public function set_whitelabel_id(
        string $whitelabel_extended = null
    ): Forms_Report_Finance;
    
    /**
     *
     * @return array
     */
    abstract public function get_currency_tab_for_process(): array;
    
    /**
     *
     * @return array
     */
    abstract protected function get_extended_whitelabels_list(): array;
    
    /**
     *
     * @param string $filter_add
     * @param array $params
     * @return array
     */
    abstract protected function prepare_filters_for_active(
        string $filter_add,
        array $params
    ): array;
    
    /**
     *
     * @return string|null
     */
    public function get_filter_add_register_count():? string
    {
        return $this->filter_add_register_count;
    }

    /**
     *
     * @return array|null
     */
    public function get_params_register_count():? array
    {
        return $this->params_register_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_register_count(): void;

    /**
     *
     * @return int|null
     */
    protected function get_register_count():? int
    {
        $this->prepare_register_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("reg_count", $this->register_count);
        }
        
        return $this->register_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_register_confirmed_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_register_confirmed_count():? int
    {
        $this->prepare_register_confirmed_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("register_confirmed_count", $this->register_confirmed_count);
        }
        
        return $this->register_confirmed_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_active_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_active_count():? int
    {
        $this->prepare_active_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("active_count", $this->active_count);
        }
        
        return $this->active_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_first_time_deposit_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_first_time_deposit_count():? int
    {
        $this->prepare_first_time_deposit_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("ftd_count", $this->first_time_deposit_count);
        }
        
        return $this->first_time_deposit_count;
    }

    /**
     *
     * @return void
     */
    abstract protected function prepare_second_time_deposit_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_second_time_deposit_count():? int
    {
        $this->prepare_second_time_deposit_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("std_count", $this->second_time_deposit_count);
        }
        
        return $this->second_time_deposit_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_first_time_purchase_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_first_time_purchase_count():? int
    {
        $this->prepare_first_time_purchase_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("ftp_count", $this->first_time_purchase_count);
        }
        
        return $this->first_time_purchase_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_second_time_purchase_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_second_time_purchase_count():? int
    {
        $this->prepare_second_time_purchase_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("stp_count", $this->second_time_purchase_count);
        }
        
        return $this->second_time_purchase_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_tickets_paid_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_tickets_paid_count():? int
    {
        $this->prepare_tickets_paid_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("tickets_count", $this->tickets_count);
        }
        
        return $this->tickets_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_lines_paid_sum(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_lines_paid_sum():? int
    {
        $this->prepare_lines_paid_sum();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("lines_count", $this->lines_count);
        }
        
        return $this->lines_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_bonus_tickets_count(): void;
    
    /**
     *
     * @return int|null
     */
    protected function get_bonus_tickets_count():? int
    {
        $this->prepare_bonus_tickets_count();

        if ($this->get_should_set_inside()) {
            $this->inside->set("bonus_tickets_count", $this->bonus_tickets_count);
        }
        
        return $this->bonus_tickets_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_tickets_win_data(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_tickets_win_count():? int
    {
        $this->prepare_tickets_win_data();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("tickets_win_count", $this->tickets_win_count);
        }
        
        return $this->tickets_win_count;
    }
    
    /**
     *
     * @return string|null
     */
    public function get_tickets_win_sum_prize():? string
    {
        $this->prepare_tickets_win_data();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("tickets_win_sum_prize", $this->tickets_win_sum_prize);
        }
        
        return $this->tickets_win_sum_prize;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_deposits_count(): void;
    
    /**
     *
     * @return int|null
     */
    public function get_deposits_count():? int
    {
        $this->prepare_deposits_count();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("deposits_count", $this->deposits_count);
        }
        
        return $this->deposits_count;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_deposit_amount_value(): void;
    
    /**
     *
     * @return string|null
     */
    public function get_deposit_amount_value():? string
    {
        $this->prepare_deposit_amount_value();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("deposit_amount_value", $this->deposit_amount_value);
        }
        
        return $this->deposit_amount_value;
    }
    
    /**
     * At this moment this function is unused but prepared.
     *
     * @return array|null
     */
    abstract protected function prepare_transactions_deposit_details(): void;

    /**
     * At this moment this function is unused but prepared, if it will be
     * used it should be prepared for return data for both manager currency and
     * USD currency.
     *
     * @return array|null
     */
    public function get_transactions_deposit_details():? array
    {
        $this->prepare_transactions_deposit_details();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("deposit_details", $this->transactions_deposit_details);
        }
        
        return $this->transactions_deposit_details;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_sale_amount(): void;
    
    /**
     *
     * @return array|null
     */
    public function get_sale_amounts():? array
    {
        $this->prepare_sale_amount();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("sale_amounts", $this->sale_amounts);
        }
        
        return $this->sale_amounts;
    }
    
    /**
     *
     * @return array
     */
    protected function get_sort_data(): array
    {
        $sort_columns = [
            'lottery_name' => 'asc',
            'lottery_sold_tickets_count' => 'desc',
            'lottery_sold_lines_count' => 'desc',
            'lottery_bonus_tickets_count' => 'desc',
            'lottery_win_tickets_count' => 'desc',
            'lottery_win_usd_sum' => 'desc',
            'lottery_amount_usd_sum' => 'desc',
            'lottery_cost_usd_sum' => 'desc',
            'lottery_income_usd_sum' => 'desc',
            'lottery_bonus_usd_sum' => 'desc',
            'lottery_margin_usd_sum' => 'desc',
            'lottery_uncovered_prize_usd_sum' => 'desc',
        ];
        
        $default = [
            'lottery_name',
            'asc'
        ];
        
        $link = '/reports';
        
        $this->sort_data = Lotto_Helper::get_sort(
            $sort_columns,
            $default,
            $link
        );
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("sort", $this->sort_data);
        }
        
        return $this->sort_data;
    }
    
    /**
     *
     * @return void
     */
    abstract protected function prepare_finance_data(): void;
    
    /**
     *
     * @return array|null
     */
    public function get_finance_data():? array
    {
        $this->get_sort_data();
        
        $this->prepare_finance_data();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("finance_data", $this->finance_data);
        }
        
        return $this->finance_data;
    }

    /**
     *
     * @return void
     */
    abstract protected function prepare_commissions_sum_value(): void;
    
    /**
     *
     * @return string|null
     */
    public function get_commissions_sum_value():? string
    {
        $this->prepare_commissions_sum_value();
        
        if ($this->get_should_set_inside()) {
            $this->inside->set("commissions_sum_value", $this->commissions_sum_value);
        }
        
        return $this->commissions_sum_value;
    }
    
    /**
     *
     * @param int $type
     * @return void
     */
    abstract protected function prepare_payment_methods_report(
        int $type = Helpers_General::TYPE_TRANSACTION_PURCHASE
    ): void;
    
    /**
     *
     * @return array|null
     */
    public function get_payment_methods_purchase_report():? array
    {
        $this->prepare_payment_methods_report(Helpers_General::TYPE_TRANSACTION_PURCHASE);
        
        if ($this->get_should_set_inside()) {
            $this->inside->set(
                "payment_methods_purchase_report",
                $this->payment_methods_purchase_report
            );
        }
        
        return $this->payment_methods_purchase_report;
    }
    
    /**
     *
     * @return array|null
     */
    public function get_payment_methods_deposit_report():? array
    {
        $this->prepare_payment_methods_report(Helpers_General::TYPE_TRANSACTION_DEPOSIT);
        
        if ($this->get_should_set_inside()) {
            $this->inside->set(
                "payment_methods_deposit_report",
                $this->payment_methods_deposit_report
            );
        }
        
        return $this->payment_methods_deposit_report;
    }
    
    /**
     *
     * @return void
     */
    abstract public function process_form(): void;
}
