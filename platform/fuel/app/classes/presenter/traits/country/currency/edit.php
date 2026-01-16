<?php

/**
 *
 */
trait Presenter_Traits_Country_Currency_Edit
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $prepared_urls = $this->prepare_urls();
        $this->set("urls", $prepared_urls);
        
        $title_text = $this->prepare_title();
        $this->set("title_text", $title_text);

        $main_help_block_text = _(
            "Here you can assign currency as default to country."
        );
        $this->set("main_help_block_text", $main_help_block_text);
        
        $available_countries = $this->prepare_available_countries();
        $this->set("available_countries", $available_countries);
        
        $available_currencies = $this->prepare_available_currencies();
        $this->set("available_currencies", $available_currencies);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $form_url = $this->country_currency_start_url;
        if (!isset($this->edit_data)) {
            $form_url .= "/new";
        } else {
            $form_url .= "/edit/" . intval($this->edit_data['id']);
        }

        $urls = [
            "country_currency" => $this->country_currency_start_url,
            "form" => $form_url
        ];
        
        return $urls;
    }
    
    /**
     *
     * @return string
     */
    private function prepare_title(): string
    {
        $title_text = "";
        if (!isset($this->edit_data)) {
            $title_text = _("Add default currency for country");
        } else {
            $title_text = _("Edit default currency for country");
        }
        
        return $title_text;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_available_countries(): array
    {
        $prepared_available_countries = [];
        
        $available_country_key_to_select = "";
        if (isset($this->edit_data)) {
            $available_country_key_to_select = (string)$this->edit_data['country_code'];
        }
        
        foreach ($this->available_countries as $key => $country) {
            $is_selected = "";
            if ($available_country_key_to_select === (string)$key) {
                $is_selected = " selected='selected'";
            }
            
            $single_country = [
                "code" => Security::htmlentities($key),
                "name" => Security::htmlentities($country),
                "is_selected" => $is_selected
            ];
            
            $prepared_available_countries[] = $single_country;
        }

        return $prepared_available_countries;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_available_currencies(): array
    {
        $prepared_available_currencies = [];
        
        $available_currency_id_to_select = -1;
        if (Input::post("input.defaultcurrency") !== null) {
            $available_currency_id_to_select = (int)Input::post("input.defaultcurrency");
        } elseif (isset($this->edit_data['whitelabel_default_currency_id'])) {
            $available_currency_id_to_select = (int)$this->edit_data['whitelabel_default_currency_id'];
        }
        
        foreach ($this->available_currencies as $key => $available_currency) {
            $is_selected = "";
            if ($available_currency_id_to_select === (int)$available_currency['id']) {
                $is_selected = ' selected="selected"';
            }
            
            $single_currency = [
                "id" => (int)$available_currency['id'],
                "currency_code" => $available_currency['currency_code'],
                "is_selected" => $is_selected
            ];
            
            $prepared_available_currencies[] = $single_currency;
        }
        
        return $prepared_available_currencies;
    }
}
