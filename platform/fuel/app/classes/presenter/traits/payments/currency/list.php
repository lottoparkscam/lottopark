<?php

/**
 *
 */
trait Presenter_Traits_Payments_Currency_List
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $prepared_urls = $this->prepare_urls();
        $this->set("urls", $prepared_urls);
        
        $prepared_show_new_button = $this->prepare_show_new_button();
        $this->set("show_new_button", $prepared_show_new_button);
        
        $prepared_show_manage_column = $this->prepare_show_manage_column();
        $this->set("show_manage_column", $prepared_show_manage_column);
        
        $main_help_block_text = _(
            "Here you can view and manage payment methods currencies."
        );
        $this->set("main_help_block_text", $main_help_block_text);

        $payment_method_currencies = $this->prepare_payment_method_currencies();
        $this->set("payment_method_currencies", $payment_method_currencies);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $new_url = $this->start_url . "/currency/" .
            $this->current_kmethod_idx . "/new";
        
        $urls = [
            "back" => $this->start_url,
            "new" => $new_url
        ];
        
        return $urls;
    }
    
    /**
     *
     * @return bool
     */
    private function is_whitelabel_type_valid(): bool
    {
        $type_valid = true;
        if ((int)$this->source !== Helpers_General::SOURCE_ADMIN &&
            Helpers_Whitelabel::is_V1($this->whitelabel['type']) &&
            !Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
        ) {
            $type_valid = false;
        }
        
        return $type_valid;
    }
    
    /**
     *
     * @return bool
     */
    private function prepare_show_new_button(): bool
    {
        $show_new_button = true;
        if (!$this->is_whitelabel_type_valid()) {
            $show_new_button = false;
        }
        
        return $show_new_button;
    }
    
    /**
     *
     * @return bool
     */
    private function prepare_show_manage_column(): bool
    {
        $show_manage_column = true;
        if (!$this->is_whitelabel_type_valid()) {
            $show_manage_column = false;
        }
        
        return $show_manage_column;
    }

    /**
     *
     * @return array
     */
    private function prepare_payment_method_currencies(): array
    {
        $payment_method_currencies = [];
        
        $start_url = $this->start_url . "/currency/" .
            $this->current_kmethod_idx;
        
        foreach ($this->payment_method_currencies as $currency) {
            $currency['code'] = Security::htmlentities($currency['payment_currency_code']);
            
            $currency['is_default_show_icon'] = Lotto_View::show_boolean($currency['is_default']);
            
            $min_purchase_value = Lotto_View::format_currency(
                $currency["min_purchase"],
                $currency['payment_currency_code'],
                true
            );
            $currency['min_purchase'] = Security::htmlentities($min_purchase_value);
            
            $edit_id = intval($currency['id']);
            
            $currency['edit_url'] = $start_url . "/edit/" . $edit_id;
            $currency['delete_url'] = $start_url . "/delete/" . $edit_id;
            
            $currency['delete_text'] = _(
                "Are you sure? This operation will " .
                "delete currency from the available list of currencies. "
            );
            
            $payment_method_currencies[] = $currency;
        }

        return $payment_method_currencies;
    }
}
