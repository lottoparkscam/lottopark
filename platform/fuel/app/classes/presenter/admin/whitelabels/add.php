<?php

/**
 * Description of Presenter_Admin_Whitelabels_Add
 */
class Presenter_Admin_Whitelabels_Add extends Presenter_Presenter
{
    /**
     *
     * @return void
     */
    public function view(): void
    {
        $domain_help_text = _(
            "E.g. <strong>https://whitelabel.com</strong>. " .
            "Only lowercase letters (a-z), numbers, " .
            "and hyphens are allowed."
        );
        $this->set('domain_help_text', $domain_help_text);
        
        $site_currency_help_block_text = _(
            "Default currency for English " .
            "language for users that do not have " .
            "country set up in their profile."
        );
        $this->set('site_currency_help_block_text', $site_currency_help_block_text);
        
        $prepaid_alert_limit_help_text = _(
            "When prepaids are under " .
            "that value you will be inform by email."
        );
        $this->set('prepaid_alert_limit_help_text', $prepaid_alert_limit_help_text);
        
        /**
         * Check error classes
         */
        $error_name_class = '';
        if (isset($this->errors['input.name'])) {
            $error_name_class = ' has-error';
        }
        $this->set("error_name_class", $error_name_class);
        
        $error_themename_class = '';
        if (isset($this->errors['input.themename'])) {
            $error_themename_class = ' has-error';
        }
        $this->set("error_themename_class", $error_themename_class);
        
        $error_domain_class = '';
        if (isset($this->errors['input.domain'])) {
            $error_domain_class = ' has-error';
        }
        $this->set("error_domain_class", $error_domain_class);

        $error_company_class = '';
        if (isset($this->errors['input.company'])) {
            $error_company_class = ' has-error';
        }
        $this->set("error_company_class", $error_company_class);
        
        $error_email_class = '';
        if (isset($this->errors['input.email'])) {
            $error_email_class = ' has-error';
        }
        $this->set("error_email_class", $error_email_class);
        
        $error_realname_class = '';
        if (isset($this->errors['input.realname'])) {
            $error_realname_class = ' has-error';
        }
        $this->set("error_realname_class", $error_realname_class);
        
        $error_margin_class = '';
        if (isset($this->errors['input.margin'])) {
            $error_margin_class = ' has-error';
        }
        $this->set("error_margin_class", $error_margin_class);
        
        $error_type_class = '';
        if (isset($this->errors['input.type'])) {
            $error_type_class = ' has-error';
        }
        $this->set("error_type_class", $error_type_class);
        
        $error_prepaid_class = '';
        if (isset($this->errors['input.prepaid'])) {
            $error_prepaid_class = ' has-error';
        }
        $this->set("error_prepaid_class", $error_prepaid_class);
        
        $error_prepaid_alert_limit_class = '';
        if (isset($this->errors['input.prepaid_alert_limit'])) {
            $error_prepaid_alert_limit_class = ' has-error';
        }
        $this->set("error_prepaid_alert_limit_class", $error_prepaid_alert_limit_class);
        
        $error_username_class = '';
        if (isset($this->errors['input.username']) ||
            isset($this->error_whitelabel_exists)
        ) {
            $error_username_class = ' has-error';
        }
        $this->set("error_username_class", $error_username_class);
        
        $error_passoword_class = '';
        if (isset($this->errors['input.password'])) {
            $error_passoword_class = ' has-error';
        }
        $this->set("error_passoword_class", $error_passoword_class);
        
        $error_prefix_class = '';
        if (isset($this->errors['input.prefix']) ||
            isset($this->error_whitelabel_exists)
        ) {
            $error_prefix_class = ' has-error';
        }
        $this->set("error_prefix_class", $error_prefix_class);
        
        $manager_currency_class_error = "";
        if (isset($this->errors['input.managercurrency'])) {
            $manager_currency_class_error = ' has-error';
        }
        $this->set("manager_currency_class_error", $manager_currency_class_error);
        
        $site_currency_class_error = "";
        if (isset($this->errors['input.sitecurrency'])) {
            $site_currency_class_error = ' has-error';
        }
        $this->set("site_currency_class_error", $site_currency_class_error);
        
        /*
         * Prepare values
         */
        $name_value_t = '';
        if (null !== Input::post("input.name")) {
            $name_value_t = Input::post("input.name");
        }
        $name_value = Security::htmlentities($name_value_t);
        $this->set("name_value", $name_value);
        
        $themename_value_t = '';
        if (null !== Input::post("input.themename")) {
            $themename_value_t = Input::post("input.themename");
        }
        $themename_value = Security::htmlentities($themename_value_t);
        $this->set("themename_value", $themename_value);
        
        $domain_value_t = '';
        if (null !== Input::post("input.domain")) {
            $domain_value_t = Input::post("input.domain");
        }
        $domain_value = Security::htmlentities($domain_value_t);
        $this->set("domain_value", $domain_value);

        $company_value_t = '';
        if (null !== Input::post("input.company")) {
            $company_value_t = Input::post("input.company");
        }
        $company_value = Security::htmlentities($company_value_t);
        $this->set("company_value", $company_value);
        
        $email_value_t = '';
        if (null !== Input::post("input.email")) {
            $email_value_t = Input::post("input.email");
        }
        $email_value = Security::htmlentities($email_value_t);
        $this->set("email_value", $email_value);
        
        $realname_value_t = '';
        if (null !== Input::post("input.realname")) {
            $realname_value_t = Input::post("input.realname");
        }
        $realname_value = Security::htmlentities($realname_value_t);
        $this->set("realname_value", $realname_value);
        
        $margin_value_t = '10';
        if (null !== Input::post("input.margin")) {
            $margin_value_t = Input::post("input.margin");
        }
        $margin_value = Security::htmlentities($margin_value_t);
        $this->set("margin_value", $margin_value);
        
        $prepared_whitelabel_types = $this->prepare_whitelabel_types();
        $this->set('whitelabel_types', $prepared_whitelabel_types);
        
        $prepaid_alert_limit_value_t = $this->prepaid_alert_limit_value;
        if (null !== Input::post("input.prepaid_alert_limit")) {
            $prepaid_alert_limit_value_t = Input::post("input.prepaid_alert_limit");
        }
        $prepaid_alert_limit_value = Security::htmlentities($prepaid_alert_limit_value_t);
        $this->set("prepaid_alert_limit_value", $prepaid_alert_limit_value);
        
        $prepaid_value_t = '0';
        if (null !== Input::post("input.prepaid")) {
            $prepaid_value_t = Input::post("input.prepaid");
        }
        $prepaid_value = Security::htmlentities($prepaid_value_t);
        $this->set("prepaid_value", $prepaid_value);
        
        $username_value_t = '';
        if (null !== Input::post("input.username")) {
            $username_value_t = Input::post("input.username");
        }
        $username_value = Security::htmlentities($username_value_t);
        $this->set("username_value", $username_value);
        
        $prefix_value_t = '';
        if (null !== Input::post("input.prefix")) {
            $prefix_value_t = Input::post("input.prefix");
        }
        $prefix_value = Security::htmlentities($prefix_value_t);
        $this->set("prefix_value", $prefix_value);
        
        $is_report_checked = ' checked="checked"';
        if (null !== Input::post("input") &&
            null === Input::post("input.is_report")
        ) {
            $is_report_checked = '';
        }
        $this->set("is_report_checked", $is_report_checked);
        
        $prepared_manager_currencies = $this->prepare_manager_currencies();
        $this->set('manager_currencies', $prepared_manager_currencies);
        
        $prepared_site_currencies = $this->prepare_site_currencies();
        $this->set('site_currencies', $prepared_site_currencies);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_whitelabel_types(): array
    {
        $prepared_whitelabel_types = [];
        
        // If no type send (first time page is shown)
        // select first option (V1)
        $type_id_to_select = Helpers_General::WHITELABEL_TYPE_V1;
        if (Input::post("input.type") !== null) {
            $type_id_to_select = (int)Input::post("input.type");
        }
        
        foreach ($this->whitelabel_types as $key => $type_name) {
            $is_selected = "";
            if ($type_id_to_select === (int)$key) {
                $is_selected = ' selected="selected"';
            }
            
            $single_type = [
                "id" => (int)$key,
                "name" => $type_name,
                "is_selected" => $is_selected
            ];
            
            $prepared_whitelabel_types[] = $single_type;
        }
        
        return $prepared_whitelabel_types;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_manager_currencies(): array
    {
        $prepared_manager_currencies = [];
        
        $currecy_id_to_select = -1;
        if (Input::post("input.managercurrency") !== null) {
            $currecy_id_to_select = (int)Input::post("input.managercurrency");
        }
        
        foreach ($this->currencies as $key => $currency) {
            $is_selected = "";
            if ($currecy_id_to_select === (int)$key) {
                $is_selected = ' selected="selected"';
            }
            
            $single_currency = [
                "id" => (int)$key,
                "code" => $currency,
                "is_selected" => $is_selected
            ];
            
            $prepared_manager_currencies[] = $single_currency;
        }
        
        return $prepared_manager_currencies;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_site_currencies(): array
    {
        $prepared_site_currencies = [];
        
        $currecy_id_to_select = -1;
        if (Input::post("input.sitecurrency") !== null) {
            $currecy_id_to_select = (int)Input::post("input.sitecurrency");
        }
        
        foreach ($this->currencies as $key => $currency) {
            $is_selected = "";
            if ($currecy_id_to_select === (int)$key) {
                $is_selected = ' selected="selected"';
            }
            
            $single_currency = [
                "id" => (int)$key,
                "code" => $currency,
                "is_selected" => $is_selected
            ];
            
            $prepared_site_currencies[] = $single_currency;
        }
        
        return $prepared_site_currencies;
    }
}
