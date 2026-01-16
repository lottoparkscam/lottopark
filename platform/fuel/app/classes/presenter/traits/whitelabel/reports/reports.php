<?php

/**
 *
 */
trait Presenter_Traits_Whitelabel_Reports_Reports
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        /*
         * Filters
         */
        $prepared_date_filters = $this->prepare_date_filters();
        $this->set('date_filters', $prepared_date_filters);
        
        $prepared_languages = $this->prepare_languages();
        $this->set('languages', $prepared_languages);
        
        $prepared_countries = $this->prepare_countries();
        $this->set('countries', $prepared_countries);
        /*
         * End
         */
    }
    
    /**
     *
     * @return array
     */
    protected function prepare_date_filters(): array
    {
        $prepare_date_filters = [];

        $reports_range_start_t = '';
        if (Input::get("filter.range_start") != null) {
            $reports_range_start_t = Input::get("filter.range_start");
        }
        $prepare_date_filters['range_start'] = Security::htmlentities($reports_range_start_t);

        $reports_range_end_t = '';
        if (Input::get("filter.range_end") != null) {
            $reports_range_end_t = Input::get("filter.range_end");
        }
        $prepare_date_filters['range_end'] = Security::htmlentities($reports_range_end_t);
        
        return $prepare_date_filters;
    }
    
    /**
     *
     * @return array
     */
    protected function prepare_languages(): array
    {
        $prepared_languages = [];
        
        $language_id_to_select = -1;
        if (Input::get("filter.language") !== null) {
            $language_id_to_select = (int)Input::get("filter.language");
        }
        
        foreach ($this->languages as $language) {
            $is_selected = '';
            if ((int)$language_id_to_select === (int)$language['id']) {
                $is_selected = ' selected="selected"';
            }
            $language_code = Lotto_View::format_language($language['code']);
            
            $single_language_row = [
                "id" => $language['id'],
                "lang_code" => $language_code,
                "selected" => $is_selected
            ];
             
            $prepared_languages[] = $single_language_row;
        }
        
        return $prepared_languages;
    }
    
    /**
     *
     * @return array
     */
    protected function prepare_countries(): array
    {
        $prepared_countries = [];
        
        $country_id_to_select = "a";
        if (Input::get("filter.country") !== null) {
            $country_id_to_select = (string)Input::get("filter.country");
        }
        
        foreach ($this->countries as $key => $country) {
            $is_selected = '';
            if ((string)$country_id_to_select === (string)$key) {
                $is_selected = ' selected="selected"';
            }
            $country_name = Security::htmlentities($country);
            
            $single_sountry_row = [
                "id" => $key,
                "name" => $country_name,
                "selected" => $is_selected
            ];
             
            $prepared_countries[] = $single_sountry_row;
        }
        
        return $prepared_countries;
    }
}
