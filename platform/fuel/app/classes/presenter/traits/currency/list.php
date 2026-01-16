<?php

/**
 *
 */
trait Presenter_Traits_Currency_List
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $prepared_urls = $this->prepare_urls();
        $this->set("urls", $prepared_urls);

        $main_help_block_text = _(
            "Here you can view and manage currencies available " .
            "on site when user try to register."
        );
        $this->set("main_help_block_text", $main_help_block_text);

        $prepared_available_currencies = $this->prepare_available_currencies();
        $this->set("available_currencies", $prepared_available_currencies);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $new_url = $this->currency_start_url . "/new";
        
        $urls = [
            "currency" => $this->currency_start_url,
            "country_currency" => $this->country_currency_start_url,
            "new" => $new_url
        ];
        
        return $urls;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_available_currencies(): array
    {
        $prepared_available_currencies = [];
       
        foreach ($this->available_currencies as $key => $available_currency) {
            $available_currency["is_default_for_site_sign"] = Lotto_View::show_boolean($available_currency["is_default_for_site"]);
            
            $deposits = [
                "first_box_value" => $available_currency["first_box"],
                "second_box_value" => $available_currency["second_box"],
                "third_box_value" => $available_currency["third_box"],
            ];

            $i = 1;
            foreach ($deposits as $deposit_key => $deposit) {
                $addtional_text = "For " . $i++ . " box: ";
                $single_value = Helpers_Currency::get_prepared_deposit_value(
                    $deposit,
                    $available_currency["currency_code"],
                    $addtional_text
                );
                $available_currency[$deposit_key] = Security::htmlentities($single_value);
            }

            if ($this->full_form) {
                $values_with_currency = [
                    "min_purchase_amount" => '',
                    "min_deposit_amount" => '',
                    "min_withdrawal" => '',
                    "max_order_amount" => '',
                    "max_deposit_amount" => ''
                ];
                
                foreach ($values_with_currency as $value_key => $one_value) {
                    $single_value = Lotto_View::format_currency(
                        $available_currency[$value_key],
                        $available_currency["currency_code"],
                        true
                    );
                    $available_currency[$value_key] = Security::htmlentities($single_value);
                }
            }
            
            $available_currency["edit_url"] = $this->currency_start_url .
                "/edit/" . $available_currency['id'];
            
            if ($this->show_delete_button &&
                !$available_currency['is_default_for_site']
            ) {
                $available_currency["delete_url"] = $this->currency_start_url .
                        "/delete/" . $available_currency['id'];

                $available_currency["delete_text"] = _(
                        "Are you sure? This operation will " .
                        "delete assignment that currency as " .
                        "default to countries."
                );
            }
            
            $prepared_available_currencies[] = $available_currency;
        }

        return $prepared_available_currencies;
    }
}
