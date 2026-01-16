<?php

/**
 *
 */
trait Presenter_Traits_Payments_Currency_Edit
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $main_help_block_text = _(
            "Here you add new currency to payment method."
        );
        $this->set("main_help_block_text", $main_help_block_text);
        
        $edit_data_prepared = $this->prepare_edit_data();
        if (!empty($edit_data_prepared)) {
            $this->set("edit", $edit_data_prepared);
        }
     
        $add_edit_url = $this->begin_payments_currency_url . '/';
        $list_url = $add_edit_url . 'list/';

        if (isset($this->edit_id)) {
            $add_edit_url .= 'edit/' . $this->edit_id;
        } else {
            $add_edit_url .= 'new';
        }
        $this->set("add_edit_url", $add_edit_url);
        $this->set("list_url", $list_url);
        
        $prepared_currencies = $this->prepare_payment_currency_list($edit_data_prepared);
        if (!empty($prepared_currencies)) {
            $this->set("currencies", $prepared_currencies);
        }
    }
    
    /**
     *
     * @return array
     */
    private function prepare_edit_data():? array
    {
        $edit_data = [
            "min_purchase" => "0.00"
        ];
        
        if (isset($this->edit_data)) {
            $edit_data["id"] = Security::htmlentities($this->edit_data["id"]);
            $edit_data["whitelabel_payment_method_id"] = Security::htmlentities($this->edit_data["whitelabel_payment_method_id"]);
            $edit_data["is_default"] = Security::htmlentities($this->is_default_checked);
        }
        
        $payment_currency_id = -1;
        $min_purchase_currency_code = reset($this->currencies);
        
        if (Input::post("input.payment_currency_id") !== null) {
            $payment_currency_id = (int)Input::post("input.payment_currency_id");
            $edit_data["currency_id"] = Security::htmlentities($payment_currency_id);
            
            $currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                "",
                $payment_currency_id
            );
            $min_purchase_currency_code = (string)$currency_tab["code"];
        } elseif (isset($this->edit_data["currency_id"])) {
            $payment_currency_id = (int)$this->edit_data["currency_id"];
            $edit_data["currency_id"] = Security::htmlentities($payment_currency_id);
            
            $currency_tab = Helpers_Currency::get_mtab_currency(
                false,
                "",
                $payment_currency_id
            );
            $min_purchase_currency_code = (string)$currency_tab["code"];
        }
        
        $edit_data["min_purchase_currency_code"] = Security::htmlentities($min_purchase_currency_code);
        
        if (Input::post("input.min_purchase") !== null) {
            $edit_data["min_purchase"] = Security::htmlentities(Input::post("input.min_purchase"));
        } elseif (isset($this->edit_data["min_purchase"])) {
            $edit_data["min_purchase"] = Security::htmlentities($this->edit_data["min_purchase"]);
        }
        
        return $edit_data;
    }
    
    /**
     *
     * @param array $edit
     * @return array
     */
    protected function prepare_payment_currency_list(array $edit = null):? array
    {
        $prepared_currencies = [];
        
        $payment_currency_id_to_select = -1;
        if (Input::post("input.payment_currency_id") !== null) {
            $payment_currency_id_to_select = (int)Input::post("input.payment_currency_id");
        } elseif (isset($edit['currency_id'])) {
            $payment_currency_id_to_select = (int)$edit['currency_id'];
        }
        
        foreach ($this->currencies as $currency_id => $currency) {
            $is_selected = "";
            if ((int)$payment_currency_id_to_select === (int)$currency_id) {
                $is_selected = ' selected="selected"';
            }

            $currency_code = Security::htmlentities($currency);
            
            $single_currency = [
                "id" => $currency_id,
                "code" => $currency_code,
                "selected" => $is_selected
            ];
             
            $prepared_currencies[] = $single_currency;
        }
        
        return $prepared_currencies;
    }
}
