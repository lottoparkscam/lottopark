<?php

/**
 *
 */
trait Presenter_Traits_Country_Currency_List
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
            "Here you can view and manage currencies assigned to countries and " .
            "set as default selected on site when user try to register."
        );
        $this->set("main_help_block_text", $main_help_block_text);

        $countries_with_defaults = $this->prepare_countries_with_defaults();
        $this->set("countries_with_defaults", $countries_with_defaults);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $new_url = $this->country_currency_start_url . "/new";
        
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
    private function prepare_countries_with_defaults(): array
    {
        $prepared_countries_with_defaults = [];
       
        foreach ($this->countries_with_defaults as $key => $country_with_defaults) {
            $country_with_defaults["country_name"] = Security::htmlentities($country_with_defaults['country_name']);
            
            $country_with_defaults["currency_code"] = Security::htmlentities($country_with_defaults['currency_code']);
            
            $country_with_defaults["edit_url"] = $this->country_currency_start_url .
                "/edit/" . $country_with_defaults['id'];
            $country_with_defaults["delete_url"] = $this->country_currency_start_url .
                "/delete/" . $country_with_defaults['id'];
            
            $prepared_countries_with_defaults[] = $country_with_defaults;
        }

        return $prepared_countries_with_defaults;
    }
}
