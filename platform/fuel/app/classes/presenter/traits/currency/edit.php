<?php

/**
 *
 */
trait Presenter_Traits_Currency_Edit
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $prepared_urls = $this->prepare_urls();
        $this->set("urls", $prepared_urls);

        $help_block_text = _(
            "These values will be shown in the boxes " .
            "for user on the deposit page!"
        );
        $this->set("help_block_text", $help_block_text);
        
        $list_of_currencies_hide_class = "";
        if (!$this->show_list_of_currencies) {
            $list_of_currencies_hide_class = "hidden";
        }
        $this->set("list_of_currencies_hide_class", $list_of_currencies_hide_class);

        $prepared_currencies = $this->prepare_currencies();
        $this->set("currencies", $prepared_currencies);
        
        $locale = substr(Lotto_Settings::getInstance()->get("locale_default"), 0, 5);
        $this->set("locale_text", $locale);
        
        $nantext = _("This is not a number");
        $this->set("nantext", $nantext);
        
        $greatermindepo = _("This value should be greater than Minimum Deposit by Currency");
        $this->set("greatermindepo", $greatermindepo);
        
        $first_box = [
            "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
            "value" => Security::htmlentities($this->results['first_converted']),
            "default_multi_in_gateway" => Security::htmlentities($this->results['first_default_multi_in_gateway_currency']),
            "old_value" => Security::htmlentities($this->results['first_converted']),
            "default_currency_text" => Security::htmlentities($this->default_currency_text),
            "in_gateway_currency" => Security::htmlentities($this->results['first_in_gateway_currency'])
        ];
        $this->set("first_box", $first_box);
        
        $second_box = [
            "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
            "value" => Security::htmlentities($this->results['second_converted']),
            "default_multi_in_gateway" => Security::htmlentities($this->results['second_default_multi_in_gateway_currency']),
            "old_value" => Security::htmlentities($this->results['second_converted']),
            "default_currency_text" => Security::htmlentities($this->default_currency_text),
            "in_gateway_currency" => Security::htmlentities($this->results['second_in_gateway_currency'])
        ];
        $this->set("second_box", $second_box);
        
        $third_box = [
            "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
            "value" => Security::htmlentities($this->results['third_converted']),
            "default_multi_in_gateway" => Security::htmlentities($this->results['third_default_multi_in_gateway_currency']),
            "old_value" => Security::htmlentities($this->results['third_converted']),
            "default_currency_text" => Security::htmlentities($this->default_currency_text),
            "in_gateway_currency" => Security::htmlentities($this->results['third_in_gateway_currency'])
        ];
        $this->set("third_box", $third_box);
        
        
        if ($this->full_form) {
            $min_purchase_box = [
                "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
                "value" => Security::htmlentities($this->min_payment_record['original']),
                "default_multi_in_gateway" => Security::htmlentities($this->min_payment_record['default_in_gateway']),
                "old_value" => Security::htmlentities($this->min_payment_record['original']),
                "default_currency_text" => Security::htmlentities($this->default_currency_text),
                "in_gateway_currency" => Security::htmlentities($this->min_payment_record['in_gateway_currency'])
            ];
            $this->set("min_purchase_box", $min_purchase_box);
            
            $min_deposit_box = [
                "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
                "value" => Security::htmlentities($this->min_deposit_record['original']),
                "default_multi_in_gateway" => Security::htmlentities($this->min_deposit_record['default_in_gateway']),
                "old_value" => Security::htmlentities($this->min_deposit_record['original']),
                "default_currency_text" => Security::htmlentities($this->default_currency_text),
                "in_gateway_currency" => Security::htmlentities($this->min_deposit_record['in_gateway_currency'])
            ];
            $this->set("min_deposit_box", $min_deposit_box);
            
            $min_withdrawal_box = [
                "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
                "value" => Security::htmlentities($this->min_withdrawal_record['original']),
                "default_multi_in_gateway" => Security::htmlentities($this->min_withdrawal_record['default_in_gateway']),
                "old_value" => Security::htmlentities($this->min_withdrawal_record['original']),
                "default_currency_text" => Security::htmlentities($this->default_currency_text),
                "in_gateway_currency" => Security::htmlentities($this->min_withdrawal_record['in_gateway_currency'])
            ];
            $this->set("min_withdrawal_box", $min_withdrawal_box);
            
            $max_order_amount_box = [
                "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
                "value" => Security::htmlentities($this->max_order_amount_record['original']),
                "default_multi_in_gateway" => Security::htmlentities($this->max_order_amount_record['default_in_gateway']),
                "old_value" => Security::htmlentities($this->max_order_amount_record['original']),
                "default_currency_text" => Security::htmlentities($this->default_currency_text),
                "in_gateway_currency" => Security::htmlentities($this->max_order_amount_record['in_gateway_currency'])
            ];
            $this->set("max_order_amount_box", $max_order_amount_box);
            
            $max_deposit_amount_box = [
                "code" => Lotto_View::format_currency_code($this->first_from_currencies['code']),
                "value" => Security::htmlentities($this->max_deposit_amount_record['original']),
                "default_multi_in_gateway" => Security::htmlentities($this->max_deposit_amount_record['default_in_gateway']),
                "old_value" => Security::htmlentities($this->max_deposit_amount_record['original']),
                "default_currency_text" => Security::htmlentities($this->default_currency_text),
                "in_gateway_currency" => Security::htmlentities($this->max_deposit_amount_record['in_gateway_currency'])
            ];
            $this->set("max_deposit_amount_box", $max_deposit_amount_box);
        }
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $form_url = "";
        if (!isset($this->edit_data)) {
            $form_url = $this->currency_start_url .
                "/new";
        } else {
            $form_url = $this->currency_start_url .
                "/edit/" . intval($this->edit_data['id']);
        }
        
        $urls = [
            "currency" => $this->currency_start_url,
            "form_url" => $form_url
        ];
        
        return $urls;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_currencies(): array
    {
        $prepared_currencies = [];
        
        $currency_id_to_select = -1;
        if (Input::post("input.site_currency") !== null) {
            $currency_id_to_select = (int)Input::post("input.site_currency");
        } elseif (isset($this->edit_data['currency_id'])) {
            $currency_id_to_select = (int)$this->edit_data['currency_id'];
        }
        
        $i = 0;
        foreach ($this->currencies as $key => $currency_entry) {
            $converted_multiplier = round(
                $this->multiplier_in_usd * $currency_entry['rate'],
                Helpers_Currency::RATE_SCALE
            );

            $rounded_in_gateway_currency = round($converted_multiplier, 0);
            if ($converted_multiplier < 1) {
                $rounded_in_gateway_currency = round($converted_multiplier, 1);
            }

            if ($i === 0) {
                $this->first_from_currencies['converted_multiplier'] = $converted_multiplier;
            }
            $i++;

            $is_selected = '';
            if ($currency_id_to_select === (int)$currency_entry['id']) {
                $is_selected = ' selected="selected"';
                $this->first_from_currencies = $currency_entry;
                $this->first_from_currencies['converted_multiplier'] = $converted_multiplier;
            }
            
            $currency = [
                "id" => $currency_entry['id'],
                "code" => $currency_entry['code'],
                "rate" => $currency_entry['rate'],
                "is_selected" => $is_selected,
                "converted_multiplier" => $converted_multiplier,
                "rounded_in_gateway_currency" => $rounded_in_gateway_currency
            ];
            
            $prepared_currencies[] = $currency;
        }

        return $prepared_currencies;
    }
}
