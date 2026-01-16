<?php

/**
 * Description of Presenter_Whitelabel_Reports_Reports
 */
final class Presenter_Whitelabel_Reports_Reports extends Presenter_Presenter
{
    use Presenter_Traits_Whitelabel_Reports_Reports;
    
    /**
     *
     * @var array
     */
    private $manager_currency_tab = [];
    
    /**
     *
     */
    public function view()
    {
        $this->manager_currency_tab = Helpers_Currency::get_mtab_currency(
            false,
            null,
            $this->whitelabel['manager_site_currency_id']
        );
        
        $this->main_process();
        
        $main_info = $this->prepare_main_info_data();
        $this->set("main_info", $main_info);
        
        // Prepare financial data (based on lotteries etc.) in manager currency
        if (isset($this->finance_data)) {
            list(
                $finance_data,
                $finance_sums
            ) = $this->prepare_finance_data();

            $this->set("finance_data", $finance_data);
            $this->set("finance_sums", $finance_sums);
        }
        
        // Prepare informations based on mathod of payment in manager currency
        // for PURCHASE
        if (isset($this->payment_methods_purchase_report)) {
            list(
                $payment_methods_purchase_data,
                $payment_methods_purchase_sums
            ) = $this->prepare_payment_methods_data(Helpers_General::TYPE_TRANSACTION_PURCHASE);

            $this->set("payment_methods_purchase_report", $payment_methods_purchase_data);
            $this->set("payment_methods_purchase_sums", $payment_methods_purchase_sums);
        }
        
        // Prepare informations based on mathod of payment in manager currency
        // for DEPOSIT
        if (isset($this->payment_methods_deposit_report)) {
            list(
                $payment_methods_deposit_data,
                $payment_methods_deposit_sums
            ) = $this->prepare_payment_methods_data(Helpers_General::TYPE_TRANSACTION_DEPOSIT);

            $this->set("payment_methods_deposit_report", $payment_methods_deposit_data);
            $this->set("payment_methods_deposit_sums", $payment_methods_deposit_sums);
        }
    }
    
    /**
     * Prepare counted values and sums informations within main block of the view
     *
     * @return array|null
     */
    private function prepare_main_info_data():? array
    {
        $main_info = [];
        
        if (empty($this->manager_currency_tab)) {
            return $main_info;
        }
        
        // Prepare dates to show and
        // registered and active counted variables
        if (isset($this->reg_count)) {
            $main_info['date_start'] = Lotto_View::format_date_without_timezone($this->date_start);
            
            $main_info['date_end'] = Lotto_View::format_date_without_timezone($this->date_end);
            
            $main_info['reg_count'] = Lotto_View::format_number($this->reg_count);
            
            $main_info['active_count'] = Lotto_View::format_number($this->active_count);
            
            $main_info['register_confirmed_count'] = Lotto_View::format_number($this->register_confirmed_count);
        }
        
        // Prepare first time deposit counted variable to show
        if (isset($this->ftd_count)) {
            $main_info['ftd_count'] = Lotto_View::format_number($this->ftd_count);
        }
        
        // Prepare second time deposit counted variable to show
        if (isset($this->std_count)) {
            $main_info['std_count'] = Lotto_View::format_number($this->std_count);
        }
        
        // Prepare first time purchase counted variable to show
        if (isset($this->ftp_count)) {
            $main_info['ftp_count'] = Lotto_View::format_number($this->ftp_count);
        }
        
        // Prepare second time purchase counted variable to show
        if (isset($this->stp_count)) {
            $main_info['stp_count'] = Lotto_View::format_number($this->stp_count);
        }
        
        // Prepare number of tickets to show
        if (isset($this->tickets_count)) {
            $main_info['tickets_count'] = Lotto_View::format_number($this->tickets_count);
        }
        
        // Prepare number of lines to show
        if (isset($this->lines_count)) {
            $main_info['lines_count'] = Lotto_View::format_number($this->lines_count);
        }
        
        // Prepare counted bonus tickets to show
        if (isset($this->bonus_tickets_count)) {
            $main_info['bonus_tickets_count'] = Lotto_View::format_number($this->bonus_tickets_count);
        }
        
        // Prepare counted tickets win
        if (isset($this->tickets_win_count)) {
            $main_info['tickets_win_count'] = Lotto_View::format_number($this->tickets_win_count);
        }
        
        // Prepare win tickets sum in manager or in USD currency to show
        if (isset($this->tickets_win_sum_prize)) {
            $main_info['tickets_win_sum_prize_value'] = Lotto_View::format_currency(
                $this->tickets_win_sum_prize,
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        // Prepare counted deposits to show
        if (isset($this->deposits_count)) {
            $main_info['deposits_count'] = Lotto_View::format_number($this->deposits_count);
        }
        
        // Prepare deposit sum in manager currency to show
        if (isset($this->deposit_amount_value)) {
            $main_info['deposit_amount_value'] = Lotto_View::format_currency(
                $this->deposit_amount_value,
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        /**
         * Prepare different sums of purchase in manager currency
         */
        if (isset($this->sale_amounts['amount_manager'])) {
            $main_info['sales_amount_manager'] = Lotto_View::format_currency(
                $this->sale_amounts['amount_manager'],
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        if (isset($this->sale_amounts['income_manager'])) {
            $main_info['sales_income_manager'] = Lotto_View::format_currency(
                $this->sale_amounts['income_manager'],
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        if (isset($this->sale_amounts['cost_manager'])) {
            $main_info['sales_cost_manager'] = Lotto_View::format_currency(
                $this->sale_amounts['cost_manager'],
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        if (isset($this->sale_amounts['payment_cost_manager'])) {
            $main_info['sales_payment_cost_manager'] = Lotto_View::format_currency(
                $this->sale_amounts['payment_cost_manager'],
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        if (isset($this->sale_amounts['margin_manager'])) {
            $main_info['sales_margin_manager'] = Lotto_View::format_currency(
                $this->sale_amounts['margin_manager'],
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        if (isset($this->sale_amounts['sum_bonus_cost_manager'])) {
            $main_info['sum_bonus_cost_manager'] = Lotto_View::format_currency(
                $this->sale_amounts['sum_bonus_cost_manager'],
                $this->manager_currency_tab['code'],
                true
            );
        }
        /**
         * Different sums of purchase in manager currency
         */
        
        /**
         * Prepare commissions sum in manager currency
         */
        if (isset($this->commissions_sum_value)) {
            $main_info['commissions_sum_value'] = Lotto_View::format_currency(
                $this->commissions_sum_value,
                $this->manager_currency_tab['code'],
                true
            );
        }
        
        
        return $main_info;
    }
    
    /**
     * Prepare financial data based on available lotteries
     *
     * @return array|null
     */
    private function prepare_finance_data():? array
    {
        $prepared_finance_raws = [];
        $prepared_finance_sums = [];
        
        if (empty($this->manager_currency_tab)) {
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

        foreach ($this->finance_data as $key => $finance_data_lottery) {
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
                $win_tickets_sum_total + $finance_data_lottery['lottery_win_manager_sum'],
                4
            );
            $finance_lottery_data['win_tickets_prize'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_win_manager_sum'],
                $this->manager_currency_tab['code'],
                true
            );
            
            $amount_total = round(
                $amount_total + $finance_data_lottery['lottery_amount_manager_sum'],
                4
            );
            $finance_lottery_data['sales'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_amount_manager_sum'],
                $this->manager_currency_tab['code'],
                true
            );

            $cost_total = round(
                $cost_total + $finance_data_lottery['lottery_cost_manager_sum'],
                4
            );
            $finance_lottery_data['ticket_costs'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_cost_manager_sum'],
                $this->manager_currency_tab['code'],
                true
            );

            $income_total = round(
                $income_total + $finance_data_lottery['lottery_income_manager_sum'],
                4
            );
            $finance_lottery_data['income'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_income_manager_sum'],
                $this->manager_currency_tab['code'],
                true
            );
            
            $bonus_total = round(
                $bonus_total + $finance_data_lottery['lottery_bonus_manager_sum'],
                4
            );
            $finance_lottery_data['bonus'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_bonus_manager_sum'],
                $this->manager_currency_tab['code'],
                true
            );

            $margin_total = round(
                $margin_total + $finance_data_lottery['lottery_margin_manager_sum'],
                4
            );
            $finance_lottery_data['maring'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_margin_manager_sum'],
                $this->manager_currency_tab['code'],
                true
            );

            $uncovered_total = round(
                $uncovered_total + $finance_data_lottery['lottery_uncovered_prize_manager_sum'],
                4
            );
            $finance_lottery_data['uncovered'] = Lotto_View::format_currency(
                $finance_data_lottery['lottery_uncovered_prize_manager_sum'],
                $this->manager_currency_tab['code'],
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
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_finance_sums['amount_total'] = Lotto_View::format_currency(
            $amount_total,
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_finance_sums['cost_total'] = Lotto_View::format_currency(
            $cost_total,
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_finance_sums['income_total'] = Lotto_View::format_currency(
            $income_total,
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_finance_sums['bonus_total'] = Lotto_View::format_currency(
            $bonus_total,
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_finance_sums['margin_total'] = Lotto_View::format_currency(
            $margin_total,
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_finance_sums['uncovered_total'] = Lotto_View::format_currency(
            $uncovered_total,
            $this->manager_currency_tab['code'],
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
     * @param int $type
     * @return array|null
     */
    private function prepare_payment_methods_data(
        int $type = Helpers_General::TYPE_TRANSACTION_PURCHASE
    ):? array {
        $payment_amount_total = $payment_income_total = 0;
        $payment_ticket_total = $payment_transaction_total = 0;
        
        $prepared_payments_raws = [];
        $prepared_payments_sums = [];
        
        if (empty($this->manager_currency_tab)) {
            return [
                $prepared_payments_raws,
                $prepared_payments_sums
            ];
        }
        
        $payment_methods_report = [];
        
        switch ($type) {
            case Helpers_General::TYPE_TRANSACTION_PURCHASE:
                $payment_methods_report = $this->payment_methods_purchase_report;
                break;

            case Helpers_General::TYPE_TRANSACTION_DEPOSIT:
                $payment_methods_report = $this->payment_methods_deposit_report;
                break;
        }
        
        // Walk on the list of payment methods
        foreach ($payment_methods_report as $row) {
            $payments_report = [];
            
            /** SUM ALL AMOUNTS **/
            $payment_amount_total = round($payment_amount_total + $row['amount_manager'], 2); // TOTAL AMOUNT MANAGER
            $payment_income_total = round($payment_income_total + $row['income_manager'], 2); // TOTAL INCOME AMOUNT MANAGER
            $payment_ticket_total = round($payment_ticket_total + $row['cost_manager'], 2); // TOTAL TICKET AMOUNT MANAGER
            $payment_transaction_total = round($payment_transaction_total + $row['total']); // TOTAL TRANSACTIONS AMOUNT

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
                    
                    if (!empty($row['pname'])) {
                        $payments_report['provider_name'] = $row['payment'];
                        $payments_report['provider_name'] .= " (" . $row['pname'] . ")";
                    } else {
                        $payments_report['provider_name'] = $row['payment'];
                    }
                    
                    break;
            }
            
            $payments_report['amount_manager'] = Lotto_View::format_currency(
                $row['amount_manager'],
                $this->manager_currency_tab['code'],
                true
            );
            
            $payments_report['income_manager'] = Lotto_View::format_currency(
                $row['income_manager'],
                $this->manager_currency_tab['code'],
                true
            );
            
            $payments_report['cost_manager'] = Lotto_View::format_currency(
                $row['cost_manager'],
                $this->manager_currency_tab['code'],
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
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_payments_sums['income_total'] = Lotto_View::format_currency(
            $payment_income_total,
            $this->manager_currency_tab['code'],
            true
        );
        
        $prepared_payments_sums['ticket_total'] = Lotto_View::format_currency(
            $payment_ticket_total,
            $this->manager_currency_tab['code'],
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
