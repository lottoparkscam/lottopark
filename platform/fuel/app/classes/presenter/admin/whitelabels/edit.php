<?php

/**
 * Description of Presenter_Admin_Whitelabels_Edit
 */
class Presenter_Admin_Whitelabels_Edit extends Presenter_Presenter
{
    /**
     *
     * @return void
     */
    public function view(): void
    {
        /**
         * Check error classes
         */
        $error_name_class = '';
        if (isset($this->errors['input.name'])) {
            $error_name_class = ' has-error';
        }
        $this->set("error_name_class", $error_name_class);
        
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
        
        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type'])) {
            $prepaid_alert_limit_help_text = _(
                "When prepaids are under " .
                "that value you will be inform by email."
            );
            $this->set('prepaid_alert_limit_help_text', $prepaid_alert_limit_help_text);
        
            $error_prepaid_alert_limit_class = '';
            if (isset($this->errors['input.prepaid_alert_limit'])) {
                $error_prepaid_alert_limit_class = ' has-error';
            }
            $this->set("error_prepaid_alert_limit_class", $error_prepaid_alert_limit_class);
        }
        
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
        
        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
            Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
        ) {
            $max_order_class_error = "";
            if (isset($this->errors['input.maxorderitems'])) {
                $max_order_class_error = ' has-error';
            }
            $this->set("max_order_class_error", $max_order_class_error);
        }
        
        $manager_currency_class_error = "";
        if (isset($this->errors['input.managercurrency'])) {
            $manager_currency_class_error = ' has-error';
        }
        $this->set("manager_currency_class_error", $manager_currency_class_error);
        
        /*
         * Prepare values
         */
        $name_value_t = $this->whitelabel['name'];
        if (null !== Input::post("input.name")) {
            $name_value_t = Input::post("input.name");
        }
        $name_value = Security::htmlentities($name_value_t);
        $this->set("name_value", $name_value);
        
        $domain_value_t = 'https://' . $this->whitelabel['domain'];
        if (null !== Input::post("input.domain")) {
            $domain_value_t = Input::post("input.domain");
        }
        $domain_value = Security::htmlentities($domain_value_t);
        $this->set("domain_value", $domain_value);

        $company_value_t = $this->whitelabel['company_details'] ?? '';
        if (null !== Input::post("input.company")) {
            $company_value_t = Input::post("input.company");
        }
        $company_value = Security::htmlentities($company_value_t);
        $this->set("company_value", $company_value);
        
        $email_value_t = $this->whitelabel['email'];
        if (null !== Input::post("input.email")) {
            $email_value_t = Input::post("input.email");
        }
        $email_value = Security::htmlentities($email_value_t);
        $this->set("email_value", $email_value);
        
        $realname_value_t = $this->whitelabel['realname'];
        if (null !== Input::post("input.realname")) {
            $realname_value_t = Input::post("input.realname");
        }
        $realname_value = Security::htmlentities($realname_value_t);
        $this->set("realname_value", $realname_value);
        
        $margin_value_t = $this->whitelabel['margin'];
        if (null !== Input::post("input.margin")) {
            $margin_value_t = Input::post("input.margin");
        }
        $margin_value = Security::htmlentities($margin_value_t);
        $this->set("margin_value", $margin_value);
        
        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type'])) {
            $manager_currency_tab = Helpers_Currency::get_mtab_currency(
                true,
                "",
                $this->whitelabel['manager_site_currency_id']
            );
            $prepaid_currency_code = $manager_currency_tab['code'];
            $this->set("prepaid_currency_code", $prepaid_currency_code);
            
            $prepaid_alert_limit_value_t = $this->whitelabel['prepaid_alert_limit'];
            if (null !== Input::post("input.prepaid_alert_limit")) {
                $prepaid_alert_limit_value_t = Input::post("input.prepaid_alert_limit");
            }
            $prepaid_alert_limit_value = Security::htmlentities($prepaid_alert_limit_value_t);
            $this->set("prepaid_alert_limit_value", $prepaid_alert_limit_value);
        }
        
        $username_value_t = $this->whitelabel['username'];
        if (null !== Input::post("input.username")) {
            $username_value_t = Input::post("input.username");
        }
        $username_value = Security::htmlentities($username_value_t);
        $this->set("username_value", $username_value);
        
        if (!Helpers_Whitelabel::is_V1($this->whitelabel['type']) ||
            Helpers_Whitelabel::is_special_ID($this->whitelabel['id'])
        ) {
            $max_order_items_value_t = "";
            if (null !== Input::post("input.maxorderitems")) {
                $max_order_items_value_t = Input::post("input.maxorderitems");
            } elseif (!empty($this->whitelabel['max_order_count'])) {
                $max_order_items_value_t = $this->whitelabel['max_order_count'];
            }
            $max_order_items_value = Security::htmlentities($max_order_items_value_t);
            $this->set("max_order_items_value", $max_order_items_value);
        }
        
        $is_report_checked = '';
        if ((null !== Input::post("input.is_report") &&
                (int)Input::post("input.is_report") === 1) ||
            (isset($this->whitelabel['is_report']) &&
                (int)$this->whitelabel['is_report'] === 1)
        ) {
            $is_report_checked = ' checked="checked"';
        }
        $this->set("is_report_checked", $is_report_checked);
    }
}
