<?php

/**
 * Description of Presenter_Whitelabel_Transactions_List
 */
class Presenter_Whitelabel_Transactions_List extends Presenter_Presenter
{
    /**
     * The default view method Should set all expected variables upon itself
     */
    public function view(): void
    {
        $main_help_block_text = "";
        if ((string)$this->type === "transactions") {
            $main_help_block_text = _("Here you can view and manage your users' non-deposit transactions.");
        } else {
            $main_help_block_text = _("Here you can view and manage your users' deposit transactions.");
        }
        
        $this->set("main_help_block_text", $main_help_block_text);
        
        $prepared_transactions = $this->prepare_transactions();
        $this->set("transactions", $prepared_transactions);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_transactions(): array
    {
        $prepared_transactions = [];
        
        $is_empire = Helpers_General::is_empire();
        
        foreach ($this->transactions as $item) {
            $transaction = [];
            
            /**
             * Prepare URLs
             */
            $user_view_url = "/inactive?filter[id]=";
            if ($item['is_deleted']) {
                $user_view_url = "/deleted?filter[id]=";
            } elseif (((int)$this->whitelabel['user_activation_type'] === Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                            $item['is_active'] &&
                            $item['is_confirmed']) ||
                        ((int)$this->whitelabel['user_activation_type'] !== Helpers_General::ACTIVATION_TYPE_REQUIRED &&
                            $item['is_active'])
            ) {
                $user_view_url = "/users?filter[id]=";
            }
            $user_view_url .= $item['utoken'];
            $transaction["user_view_url"] = $user_view_url;
            
            $ticket_url = "/tickets?filter[transactionid]=" . $item['token'];
            $transaction["ticket_url"] = $ticket_url;
            
            $view_url = '/' . $this->type . '/view/';
            $view_url .= $item['token'];
            $view_url .= Lotto_View::query_vars();
            $transaction["view_url"] = $view_url;
            
            $accept_url = '/' . $this->type . '/accept/';
            $accept_url .= $item['token'];
            $accept_url .= Lotto_View::query_vars();
            $transaction["accept_url"] = $accept_url;
            /*
             * END of preparing URLs
             */
            
            $trans_token = $this->whitelabel['prefix'];
            if ((int)$item['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE) {
                $trans_token .= 'P';
            } else {
                $trans_token .= 'D';
            }
            $trans_token .= $item['token'];
            
            $transaction["full_token"] = $trans_token;

            $user_token = $this->whitelabel['prefix'] . 'U' .
                $item['utoken'];

            $user_name_t = _("anonymous");
            if (!empty($item['name']) ||
                !empty($item['surname'])
            ) {
                $user_name_t = $item['name'] . ' ' . $item['surname'];
            }
            $user_name = Security::htmlentities($user_name_t);

            $transaction["user_data"] = $user_token . " &bull; " . $user_name;
            
            $transaction["email"] = Security::htmlentities($item['email']);

            $login = "-";
            if (isset($item['user_login'])) {
                $login = $item['user_login'];
            }
            $transaction["user_login"] = $login;
            
            $transaction["is_deleted"] = $item['is_deleted'];
            
            $show_view_button = false;
            if ((int)$item['type'] === Helpers_General::TYPE_TRANSACTION_PURCHASE &&
                (int)$item['status'] === Helpers_General::STATUS_TRANSACTION_APPROVED
            ) {
                $show_view_button = true;
            }
            $transaction["show_view_button"] = $show_view_button;
            
            $payment_cost_manager = Lotto_View::format_currency(
                $item['payment_cost_manager'],
                $item['manager_currency_code'],
                true
            );
            $payment_cost_manager = (float)str_replace(',', '.', $payment_cost_manager);

            $payment_cost_manager_text = _("Payment cost") . ": " .
                $payment_cost_manager;
            $transaction['payment_cost_manager_text'] = Security::htmlentities($payment_cost_manager_text);
            
            if ($item['transaction_currency_code'] !== $item['manager_currency_code']) {
                $payment_cost_text = Lotto_View::format_currency(
                    $item['payment_cost'],
                    $item['transaction_currency_code'],
                    true
                );
                $payment_cost = "<br>" . _("User currency") . ": " .
                    $payment_cost_text;
                $transaction['payment_cost'] = Security::htmlentities($payment_cost);
            }
            
            $show_payment_cost = false;
            if (round($payment_cost_manager) != 0) {
                $show_payment_cost = true;
            }
            $transaction["show_payment_cost"] = $show_payment_cost;
            
            $transaction["payment_method_name"] = $this->get_prepared_payment_method_name($item);
            
            $amount_payment_text = "";
            $amount_manager_text = "";
            $other_amounts = [];
            $show_user_amount = false;

            $bonus_amount_payment_text = "";
            $bonus_amount_manager_text = "";
            $other_bonus_amounts = [];

            $amount_text = Lotto_View::format_currency(
                $item['amount'],
                $item['transaction_currency_code'],
                true
            );

            $bonus_amount_text = Lotto_View::format_currency(
                $item['bonus_amount'],
                $item['transaction_currency_code'],
                true
            );

            // If transaction currency is different than manager currency
            if ((string)$item['manager_currency_code'] !== (string)$item['transaction_currency_code']) {
                $amount_manager_text = Lotto_View::format_currency(
                    $item['amount_manager'],
                    $item['manager_currency_code'],
                    true
                );
                $bonus_amount_manager_text = Lotto_View::format_currency(
                    $item['bonus_amount_manager'],
                    $item['manager_currency_code'],
                    true
                );
                $show_user_amount = true;
            } else {
                $amount_manager_text = $amount_text;
                $bonus_amount_manager_text = $bonus_amount_text;
            }
            $transaction["amount_manager_text"] = $amount_manager_text;
            $transaction["bonus_amount_manager_text"] = $bonus_amount_manager_text;
            
            // If transaction currency is different than payment currency
            if ((string)$item['manager_currency_code'] !== (string)$item['payment_currency_code'] &&
                (string)$item['payment_currency_code'] !== (string)$item['transaction_currency_code']
            ) {
                $amount_payment_text = Lotto_View::format_currency(
                    $item['amount_payment'],
                    $item['payment_currency_code'],
                    true
                );
                $bonus_amount_payment_text = Lotto_View::format_currency(
                    $item['bonus_amount_payment'],
                    $item['payment_currency_code'],
                    true
                );
            }

            if ($show_user_amount) {
                $other_amounts[] = _("User currency") .
                    ": " . $amount_text;
                $other_bonus_amounts[] = _("User currency") .
                        ": " . $bonus_amount_text;
            }
            if (!empty($amount_payment_text)) {
                $other_amounts[] = _("Payment currency") .
                    ": " . $amount_payment_text;
            }
            if (!empty($bonus_amount_payment_text)) {
                $other_bonus_amounts[] = _("Payment currency") .
                    ": " . $bonus_amount_payment_text;
            }
            $transaction["user_amounts"] = implode("<br>", $other_amounts);
            $transaction["bonus_amounts"] = implode("<br>", $other_bonus_amounts);
            
            $transaction["date"] = Lotto_View::format_date(
                $item['date'],
                IntlDateFormatter::MEDIUM,
                IntlDateFormatter::SHORT
            );
            
            $date_confirmed = "";
            if (!empty($item['date_confirmed'])) {
                $date_confirmed = Lotto_View::format_date(
                    $item['date_confirmed'],
                    IntlDateFormatter::MEDIUM,
                    IntlDateFormatter::SHORT
                );
            }
            $transaction["date_confirmed"] = $date_confirmed;
            
            switch ((int)$item['status']) {
                case Helpers_General::STATUS_TRANSACTION_PENDING:
                    $transaction['status_show_class'] = "";
                    $transaction['status'] = _("Pending");
                    break;
                case Helpers_General::STATUS_TRANSACTION_APPROVED:
                    $transaction['status_show_class'] = "text-success";
                    $transaction['status'] = _("Approved");
                    break;
                case Helpers_General::STATUS_TRANSACTION_ERROR:
                    $transaction['status_show_class'] = "text-danger";
                    $transaction['status'] = _("Error");
                    break;
            }
            
            if ((string)$this->type === "transactions") {
                $counted_text = $item['count'];
                $counted_text .= "/";
                $counted_text .= $item['count_processed'];
                $transaction['counted_text'] = $counted_text;
            }
            
            $show_accept_button = false;
            if ($is_empire &&
                    (int)$item['count_processed'] === 0 &&
                    (int)$item['status'] !== Helpers_General::STATUS_TRANSACTION_APPROVED
            ) {
                $show_accept_button = true;
            }
            $transaction["show_accept_button"] = $show_accept_button;
                
            $prepared_transactions[] = $transaction;
        }
        
        return $prepared_transactions;
    }
    
    /**
     *
     * @param array $item
     * @return string
     */
    private function get_prepared_payment_method_name(array $item): string
    {
        $payment_method_name = "";
        
        switch ((int)$item['payment_method_type']) {
            case Helpers_General::PAYMENT_TYPE_BONUS_BALANCE:
                $payment_method_name = _("Bonus balance");
                break;
            case Helpers_General::PAYMENT_TYPE_BALANCE:
                $payment_method_name = _("Balance");
                break;
            case Helpers_General::PAYMENT_TYPE_CC:
                $payment_method_name = _("Credit Card");
                break;
            case Helpers_General::PAYMENT_TYPE_OTHER:
                $name_text = "";
                $item_pmethod_id = null;
                
                if (!empty($item['whitelabel_payment_method_id'])) {
                    $item_pmethod_id = (int)$item['whitelabel_payment_method_id'];
                }
                
                if (!empty($item_pmethod_id) &&
                    !empty($this->methods) &&
                    !empty($this->methods[$item_pmethod_id]) &&
                    !empty($this->methods[$item_pmethod_id]['pname']) &&
                    !empty($this->methods[$item_pmethod_id]['name'])
                ) {
                    $name_text = (string)$this->methods[$item_pmethod_id]['name'];
                    $name_text .= " (" . (string)$this->methods[$item_pmethod_id]['pname'] . ")";
                }
                
                $lang_id = null;
                if (!empty($this->methods) &&
                    !empty($this->methods[$item_pmethod_id]) &&
                    !empty($this->methods[$item_pmethod_id]['language_id'])
                ) {
                    $lang_id = (int)$this->methods[$item_pmethod_id]['language_id'];
                } else { // This is the last chance that the code will work properly
                    $lang_id = Helpers_General::get_default_language_id();
                }

                $formatted_language = "";
                if (!empty($item_pmethod_id) &&
                    !empty($lang_id) &&
                    !empty($this->langs) &&
                    !empty($this->langs[$lang_id]) &&
                    !empty($this->langs[$lang_id]['code'])
                ) {
                    $formatted_language = Lotto_View::format_language($this->langs[$lang_id]['code']);
                }
                
                $payment_method_name = Security::htmlentities($name_text);
                $payment_method_name .= " - ";
                $payment_method_name .= $formatted_language;
                break;
        }
        
        return $payment_method_name;
    }
}
