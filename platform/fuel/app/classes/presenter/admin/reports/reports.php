<?php

/**
 * Description of reports
 */
final class Presenter_Admin_Reports_Reports extends Presenter_Presenter
{
    use Presenter_Traits_Whitelabel_Reports_Reports;
    
    /**
     * This value is only as helper to pull keys from different tables
     * finished with manager or usd word at the end.
     * It is especially needed to finance data to discover which value
     * should be taken to Front end. When manager it will be taken manager
     * value with manager currency, when usd it will be global usd value with
     * currency of USD.
     *
     * @var string
     */
    private $key_to_pull_data = 'manager';
    
    /**
     *
     * @return void
     */
    public function view(): void
    {
        if (empty($this->whitelabel_id) && !$this->check_full_report) {
            $this->key_to_pull_data = 'usd';
        }

        $prepared_extended_list_of_whitelabels = $this->prepare_extended_list_of_whitelabels();
        
        $this->set('extended_list_of_whitelabels', $prepared_extended_list_of_whitelabels);
        $this->main_process();
        
        $report_data_prepared = $this->prepare_report_data();
        $this->set('report_data_prepared', $report_data_prepared);
    }
    
    /**
     *
     * @return array
     */
    protected function prepare_extended_list_of_whitelabels(): array
    {
        $prepared_whitelabels = [];
        
        $whitelabel_id_to_select = -1;
        if (Input::get("filter.whitelabels_extended") !== null) {
            $whitelabel_id_to_select = (string)Input::get("filter.whitelabels_extended");
        }

        foreach ($this->extended_list_of_whitelabels as $whitelabel_row) {
            $is_selected = '';
            if ((string)$whitelabel_id_to_select === (string)$whitelabel_row['id']) {
                $is_selected = ' selected="selected"';
            }
            $whitelabel_name = Security::htmlentities($whitelabel_row['name']);
            
            $single_whitelabel_row = [
                "id" => $whitelabel_row['id'],
                "name" => $whitelabel_name,
                "selected" => $is_selected
            ];
             
            $prepared_whitelabels[] = $single_whitelabel_row;
        }
        
        return $prepared_whitelabels;
    }
    
    /**
     *
     * @return array|null
     */
    protected function prepare_report_data():? array
    {
        $report_data_prepared = [];
        
        if (empty($this->report_data)) {
            return $report_data_prepared;
        }
        
        foreach ($this->report_data as $single_report_data) {
            $single_report_prepared['main_name'] = $single_report_data['main_name'];
            
            $single_report_prepared['main_info'] = $this->prepare_main_info_full(
                $single_report_data['main_info'],
                $single_report_data['currency_tab_for_process']
            );
            
            list(
                $finance_data,
                $finance_sums
            ) = $this->prepare_finance_data_full(
                $single_report_data['finance_data'],
                $single_report_data['currency_tab_for_process']
            );
            
            $single_report_prepared['finance_data'] = $finance_data;

            $single_report_prepared['sort'] = $single_report_data['sort'];

            $single_report_prepared['finance_sums'] = $finance_sums;
            
            list(
                $payment_methods_purchase_data,
                $payment_methods_purchase_sums
            ) = $this->prepare_payment_methods_data_full(
                $single_report_data['payment_methods_purchase_report'],
                $single_report_data['currency_tab_for_process']
            );
            
            $single_report_prepared['payment_methods_purchase_report'] = $payment_methods_purchase_data;

            $single_report_prepared['payment_methods_purchase_sums'] = $payment_methods_purchase_sums;

            list(
                $payment_methods_deposit_data,
                $payment_methods_deposit_sums
            ) = $this->prepare_payment_methods_data_full(
                $single_report_data['payment_methods_deposit_report'],
                $single_report_data['currency_tab_for_process']
            );
            
            $single_report_prepared['payment_methods_deposit_report'] = $payment_methods_deposit_data;

            $single_report_prepared['payment_methods_deposit_sums'] = $payment_methods_deposit_sums;
            
            $report_data_prepared[] = $single_report_prepared;
        }
        
        return $report_data_prepared;
    }
    
    /**
     *
     * @param array $main_info
     * @param array $currency_tab_for_process
     * @return array|null
     */
    private function prepare_main_info_full(
        array $main_info = [],
        array $currency_tab_for_process = []
    ):? array {
        $prepared_main_info = [];
        
        if (isset($main_info['reg_count'])) {
            $prepared_main_info['date_start'] = Lotto_View::format_date_without_timezone($main_info['date_start']);
            
            $prepared_main_info['date_end'] = Lotto_View::format_date_without_timezone($main_info['date_end']);
            
            $prepared_main_info['reg_count'] = Lotto_View::format_number($main_info['reg_count']);
            
            $prepared_main_info['active_count'] = Lotto_View::format_number($main_info['active_count']);
            
            $prepared_main_info['register_confirmed_count'] = Lotto_View::format_number($main_info['register_confirmed_count']);
        }
        
        // Prepare first time deposit counted variable to show
        if (isset($main_info['ftd_count'])) {
            $prepared_main_info['ftd_count'] = Lotto_View::format_number($main_info['ftd_count']);
        }
        
        // Prepare second time deposit counted variable to show
        if (isset($main_info['std_count'])) {
            $prepared_main_info['std_count'] = Lotto_View::format_number($main_info['std_count']);
        }
        
        // Prepare first time purchase counted variable to show
        if (isset($main_info['ftp_count'])) {
            $prepared_main_info['ftp_count'] = Lotto_View::format_number($main_info['ftp_count']);
        }
        
        // Prepare second time purchase counted variable to show
        if (isset($main_info['stp_count'])) {
            $prepared_main_info['stp_count'] = Lotto_View::format_number($main_info['stp_count']);
        }
        
        // Prepare counted tickets to show
        if (isset($main_info['tickets_count'])) {
            $prepared_main_info['tickets_count'] = Lotto_View::format_number($main_info['tickets_count']);
        }
        
        // Prepare number of lines to show
        if (isset($main_info['lines_count'])) {
            $prepared_main_info['lines_count'] = Lotto_View::format_number($main_info['lines_count']);
        }
        
        // Prepare counted bonus tickets to show
        if (isset($main_info['bonus_tickets_count'])) {
            $prepared_main_info['bonus_tickets_count'] = Lotto_View::format_number($main_info['bonus_tickets_count']);
        }
        
        // Prepare counted tickets win
        if (isset($main_info['tickets_win_count'])) {
            $prepared_main_info['tickets_win_count'] = Lotto_View::format_number($main_info['tickets_win_count']);
        }
        
        // Prepare win tickets sum in manager or in USD currency to show
        if (isset($main_info['tickets_win_sum_prize'])) {
            $prepared_main_info['tickets_win_sum_prize_value'] = Lotto_View::format_currency(
                $main_info['tickets_win_sum_prize'],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        // Prepare counted deposits to show
        if (isset($main_info['deposits_count'])) {
            $prepared_main_info['deposits_count'] = Lotto_View::format_number($main_info['deposits_count']);
        }
        
        // Prepare deposit sum in manager currency to show
        if (isset($main_info['deposit_amount_value'])) {
            $prepared_main_info['deposit_amount_value'] = Lotto_View::format_currency(
                $main_info['deposit_amount_value'],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        /**
         * Prepare different sums of purchase in manager currency
         */
        if (isset($main_info['sale_amounts']['amount_' . $this->key_to_pull_data])) {
            $prepared_main_info['sales_amount_value'] = Lotto_View::format_currency(
                $main_info['sale_amounts']['amount_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        if (isset($main_info['sale_amounts']['income_' . $this->key_to_pull_data])) {
            $prepared_main_info['sales_income_value'] = Lotto_View::format_currency(
                $main_info['sale_amounts']['income_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        if (isset($main_info['sale_amounts']['cost_' . $this->key_to_pull_data])) {
            $prepared_main_info['sales_cost_value'] = Lotto_View::format_currency(
                $main_info['sale_amounts']['cost_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        if (isset($main_info['sale_amounts']['payment_cost_' . $this->key_to_pull_data])) {
            $prepared_main_info['sales_payment_cost_value'] = Lotto_View::format_currency(
                $main_info['sale_amounts']['payment_cost_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        if (isset($main_info['sale_amounts']['margin_' . $this->key_to_pull_data])) {
            $prepared_main_info['sales_margin_value'] = Lotto_View::format_currency(
                $main_info['sale_amounts']['margin_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        if (isset($main_info['sale_amounts']['sum_bonus_cost_' . $this->key_to_pull_data])) {
            $prepared_main_info['sum_bonus_cost_value'] = Lotto_View::format_currency(
                $main_info['sale_amounts']['sum_bonus_cost_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
        }
        /**
         * Different sums of purchase in manager currency
         */
        
        /**
        * Prepare commissions sum in manager currency
        */
        if (isset($main_info['commissions_sum_value'])) {
            $prepared_main_info['commissions_sum_value'] = Lotto_View::format_currency(
                $main_info['commissions_sum_value'],
                $currency_tab_for_process['code'],
                true
            );
        }
        
        return $prepared_main_info;
    }
    
    /**
     * Prepare financial data based on available lotteries
     *
     * @param array $finance_data
     * @param array $currency_tab_for_process
     * @return array|null
     */
    private function prepare_finance_data_full(
        array $finance_data = [],
        array $currency_tab_for_process = []
    ):? array {
        $prepared_finance_raws = [];
        $prepared_finance_sums = [];
        
        if (empty($currency_tab_for_process)) {
            return [
                $prepared_finance_raws,
                $prepared_finance_sums
            ];
        }
        
        $sold_tickets_total = 0;
        $sold_lines_total = 0;
        $bonus_tickets_total = 0;
        $win_tickets_total = 0;
        $win_tickets_sum_total = 0;
        $amount_total = $cost_total = $income_total = 0;
        $bonus_total = 0;
        $margin_total = $uncovered_total = 0;
        
        foreach ($finance_data as $key => $finance_data_lottery) {
            $finance_lottery_data = [];

            $finance_lottery_data['name'] = _($finance_data_lottery['lottery_name']);

            $finance_lottery_data['sold_tickets_count'] = $finance_data_lottery['lottery_sold_tickets_count'];
            $sold_tickets_total += (int)$finance_data_lottery['lottery_sold_tickets_count'];
            
            $finance_lottery_data['sold_lines_count'] = $finance_data_lottery['lottery_sold_lines_count'];
            $sold_lines_total += (int)$finance_data_lottery['lottery_sold_lines_count'];
            
            $finance_lottery_data['bonus_tickets_count'] = $finance_data_lottery['lottery_bonus_tickets_count'];
            $bonus_tickets_total += (int)$finance_data_lottery['lottery_bonus_tickets_count'];
                
            $finance_lottery_data['win_tickets_count'] = $finance_data_lottery['lottery_win_tickets_count'];
                
            $win_tickets_total += (int)$finance_data_lottery['lottery_win_tickets_count'];
                
            $win_tickets_sum_total = round(
                $win_tickets_sum_total + $finance_data_lottery['lottery_win_' . $this->key_to_pull_data . '_sum'],
                4
            );
            $finance_lottery_data['win_tickets_prize'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_win_' . $this->key_to_pull_data . '_sum'],
                $currency_tab_for_process['code'],
                true
            );
                
            $amount_total = round(
                $amount_total + $finance_data_lottery['lottery_amount_' . $this->key_to_pull_data . '_sum'],
                4
            );
            $finance_lottery_data['sales'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_amount_' . $this->key_to_pull_data . '_sum'],
                $currency_tab_for_process['code'],
                true
            );

            $cost_total = round(
                $cost_total + $finance_data_lottery['lottery_cost_' . $this->key_to_pull_data . '_sum'],
                4
            );
            $finance_lottery_data['ticket_costs'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_cost_' . $this->key_to_pull_data . '_sum'],
                $currency_tab_for_process['code'],
                true
            );

            $income_total = round(
                $income_total + $finance_data_lottery['lottery_income_' . $this->key_to_pull_data . '_sum'],
                4
            );
            $finance_lottery_data['income'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_income_' . $this->key_to_pull_data . '_sum'],
                $currency_tab_for_process['code'],
                true
            );

            $bonus_total = round(
                $bonus_total + $finance_data_lottery['lottery_bonus_' . $this->key_to_pull_data . '_sum'],
                4
            );
            $finance_lottery_data['bonus'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_bonus_' . $this->key_to_pull_data . '_sum'],
                $currency_tab_for_process['code'],
                true
            );
            
            $margin_total = round(
                $margin_total + $finance_data_lottery['lottery_margin_' . $this->key_to_pull_data . '_sum'],
                4
            );
            $finance_lottery_data['maring'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_margin_' . $this->key_to_pull_data . '_sum'],
                $currency_tab_for_process['code'],
                true
            );

            $uncovered_total = round(
                $uncovered_total + $finance_data_lottery['lottery_uncovered_prize_' . $this->key_to_pull_data . '_sum'],
                4
            );
            $finance_lottery_data['uncovered'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_uncovered_prize_' . $this->key_to_pull_data . '_sum'],
                $currency_tab_for_process['code'],
                true
            );

            $prepared_finance_raws[] = $finance_lottery_data;
        }
        
        /**
         *  Prepare totals array
         */
        $prepared_finance_sums['sold_tickets_total'] = $sold_tickets_total;
        
        $prepared_finance_sums['sold_lines_total'] = $sold_lines_total;
        
        $prepared_finance_sums['bonus_tickets_total'] = $bonus_tickets_total;
        
        $prepared_finance_sums['win_tickets_total'] = $win_tickets_total;
        
        $prepared_finance_sums['win_tickets_prize_total'] = Lotto_View::format_currency(
            $win_tickets_sum_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $amount_total_formatted = round($amount_total, 2);
        $prepared_finance_sums['amount_total'] = Lotto_View::format_currency(
            $amount_total_formatted,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_finance_sums['cost_total'] = Lotto_View::format_currency(
            $cost_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_finance_sums['income_total'] = Lotto_View::format_currency(
            $income_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_finance_sums['bonus_total'] = Lotto_View::format_currency(
            $bonus_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_finance_sums['margin_total'] = Lotto_View::format_currency(
            $margin_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_finance_sums['uncovered_total'] = Lotto_View::format_currency(
            $uncovered_total,
            $currency_tab_for_process['code'],
            true
        );
        
        /**
         * Prepare totals
         */
        
        return [
            $prepared_finance_raws,
            $prepared_finance_sums
        ];
    }
    
    /**
     * Prepare payment data to show on view based on method of the payment
     * in manager currency
     *
     * @param array $payment_methods_report
     * @param array $currency_tab_for_process
     * @return array|null
     */
    private function prepare_payment_methods_data_full(
        array $payment_methods_report = [],
        array $currency_tab_for_process = []
    ):? array {
        $payment_amount_total = $payment_income_total = 0;
        $payment_ticket_total = $payment_transaction_total = 0;
        
        $prepared_payments_raws = [];
        $prepared_payments_sums = [];
        
        if (empty($currency_tab_for_process)) {
            return [
                $prepared_payments_raws,
                $prepared_payments_sums
            ];
        }
        
        // Walk on the list of payment methods
        foreach ($payment_methods_report as $row) {
            $payments_report = [];
            
            /** SUM ALL AMOUNTS **/
            $payment_amount_total = round(
                $payment_amount_total + $row['amount_' . $this->key_to_pull_data],
                2
            ); // TOTAL AMOUNT MANAGER
            
            $payment_income_total = round(
                $payment_income_total + $row['income_' . $this->key_to_pull_data],
                2
            ); // TOTAL INCOME AMOUNT MANAGER
            
            $payment_ticket_total = round(
                $payment_ticket_total + $row['cost_' . $this->key_to_pull_data],
                2
            ); // TOTAL TICKET AMOUNT MANAGER
            
            $payment_transaction_total = round(
                $payment_transaction_total + $row['total']
            ); // TOTAL TRANSACTIONS AMOUNT

            /** PAYMENT TYPE AND PROVIDER NAME **/
            switch ($row['method']) {
                case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                    $payments_report['payment_method'] = _("Bonus balance");
                    $payments_report['provider_name'] = _("Bonus balance");
                    break;
                case Helpers_General::PAYMENT_TYPE_BALANCE:
                    $payments_report['payment_method'] = _("User balance");
                    $payments_report['provider_name'] = _("User balance");
                    break;
                case Helpers_General::PAYMENT_TYPE_CC:
                    $payments_report['payment_method'] = _("Credit card");
                    $payments_report['provider_name'] = Lotto_View::get_gateway_name($row['payment']);
                    break;
                case Helpers_General::PAYMENT_TYPE_OTHER:
                    $payments_report['payment_method'] = _("External gateway");
                    $payments_report['provider_name'] = '';
                    
                    if (!empty($row['pname'])) {
                        $payments_report['provider_name'] = $row['payment'];
                        $payments_report['provider_name'] .= " (" . $row['pname'] . ")";
                    } else {
                        $payments_report['provider_name'] = $row['payment'];
                    }
                    
                    break;
            }
            
            $payments_report['amount_value'] = Lotto_View::format_currency(
                $row['amount_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
            
            $payments_report['income_value'] = Lotto_View::format_currency(
                $row['income_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
            
            $payments_report['cost_value'] = Lotto_View::format_currency(
                $row['cost_' . $this->key_to_pull_data],
                $currency_tab_for_process['code'],
                true
            );
            
            $payments_report['total'] = $row['total'];
            
            $prepared_payments_raws[] = $payments_report;
        }
        
        /**
         * Prepare totals
         */
        $prepared_payments_sums['amount_total'] = Lotto_View::format_currency(
            $payment_amount_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_payments_sums['income_total'] = Lotto_View::format_currency(
            $payment_income_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_payments_sums['ticket_total'] = Lotto_View::format_currency(
            $payment_ticket_total,
            $currency_tab_for_process['code'],
            true
        );
        
        $prepared_payments_sums['transaction_total'] = (int)$payment_transaction_total;
        /**
         * Prepare totals
         */
        
        return [
            $prepared_payments_raws,
            $prepared_payments_sums
        ];
    }
}
