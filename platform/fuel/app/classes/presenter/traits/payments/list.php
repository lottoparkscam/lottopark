<?php

/**
 *
 */
trait Presenter_Traits_Payments_List
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $prepared_show_column_show_on_payment_page = $this->prepare_show_column_show_on_payment_page();
        $this->set("show_column_show_on_payment_page", $prepared_show_column_show_on_payment_page);
            
        $prepared_show_new_button = $this->prepare_show_new_button();
        $this->set("show_new_button", $prepared_show_new_button);
        
        $prepared_urls = $this->prepare_urls();
        $this->set("urls", $prepared_urls);
        
        $warning_text = _(
            "The default payments are those from English language. " .
            "You don't have to specify every payment method for " .
            "every language if they are the same."
        );
        $this->set("warning_text", $warning_text);
        
        $prepared_payment_methods = $this->prepare_payment_methods();
        $this->set("payment_methods", $prepared_payment_methods);
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
    private function prepare_show_column_show_on_payment_page(): bool
    {
        $show_column_show_on_payment_page = true;
        if (!$this->is_whitelabel_type_valid()) {
            $show_column_show_on_payment_page = false;
        }
        
        return $show_column_show_on_payment_page;
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
     * @return array
     */
    private function prepare_urls(): array
    {
        $new_url = $this->payment_methods_start_url . "/new";
        
        $urls = [
            "main" => $this->payment_methods_start_url,
            "cc_settings" => $this->ccpayment_methods_start_url,
            "new" => $new_url
        ];
        
        return $urls;
    }
    
    /**
     *
     * @return array|null
     */
    private function prepare_payment_methods():? array
    {
        $prepared_methods = [];
        
        $i = 0;
        $idx = 0;
        foreach ($this->methods as $item) {
            if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
                $idx = (int)$item['id'];
            } else {
                $i++;
                $idx = $i;
            }

            $single_payment_method = [
                "orderup_url" => $this->payment_methods_start_url . "/orderup/" . $idx,
                "orderdown_url" => $this->payment_methods_start_url . "/orderdown/" . $idx,
                "currency_list_url" => $this->payment_methods_start_url . "/currency/" . $idx . "/list/",
                "edit_url" => $this->payment_methods_start_url . "/edit/" . $idx . "/whitelabelpayment/" . $item['id'],
                "customize_url" => $this->payment_methods_start_url . "/customize/" . $idx . "/list/",
            ];
            
            $show_edit = true;
            $hide_row = false;
            if (!$this->is_whitelabel_type_valid()) {
                $show_edit = false;
                if ((int)$item['show'] === 0) {
                    $hide_row = true;
                }
            }
            $single_payment_method['show_edit'] = $show_edit;
            $single_payment_method['hide_row'] = $hide_row;
            
            $lang_code = $this->langs[$item['language_id']]['code'];
            $single_payment_method["lang_code"] = Lotto_View::format_language($lang_code);
            
            $single_payment_method["order"] = Lotto_View::format_number($item['order']);

            $show_order_up = false;
            if ($item['order'] > 1) {
                $show_order_up = true;
            }
            $single_payment_method["show_order_up"] = $show_order_up;
            
            $show_order_down = false;
            if ($item['order'] < $this->lang_methods[$item['language_id']]) {
                $show_order_down = true;
            }
            $single_payment_method["show_order_down"] = $show_order_down;
            
            $single_payment_method["name"] = Security::htmlentities($item['name']);
            
            $single_payment_method["pname"] = Security::htmlentities($item['pname']);
            
            $show = _("Yes");
            if ((int)$item['show'] === 0) {
                $show = _("No");
            }
            $single_payment_method["show"] = $show;

            $default_payment_currency_temp = "";
            if (!empty($this->currencies) &&
                !empty($this->currencies[$item['payment_currency_id']])
            ) {
                $default_payment_currency_temp = $this->currencies[$item['payment_currency_id']];
            }
            $single_payment_method["default_payment_currency"] = Security::htmlentities($default_payment_currency_temp);
            
            $prepared_methods[] = $single_payment_method;
        }
        
        return $prepared_methods;
    }
}
