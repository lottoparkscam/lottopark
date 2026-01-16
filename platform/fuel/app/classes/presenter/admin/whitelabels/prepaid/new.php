<?php

/**
 * Description of Presenter_Admin_Whitelabels_Prepaid_New
 */
class Presenter_Admin_Whitelabels_Prepaid_New extends Presenter_Presenter
{
    /**
     *
     * @return void
     */
    public function view(): void
    {
        $prepared_urls = $this->get_prepared_urls();
        $this->set('urls', $prepared_urls);
        
        $manager_currency_tab = Helpers_Currency::get_mtab_currency(
            true,
            "",
            $this->whitelabel['manager_site_currency_id']
        );
        
        $manager_currency_code = $manager_currency_tab['code'];
        $this->set('manager_currency_code', $manager_currency_code);
        
        $amount_error_class = "";
        if (isset($this->errors['input.amount'])) {
            $amount_error_class = ' has-error';
        }
        $this->set('amount_error_class', $amount_error_class);
        
        $amount_value_t = "";
        if (null !== Input::post("input.amount")) {
            $amount_value_t = Input::post("input.amount");
        }
        $amount_value = Security::htmlentities($amount_value_t);
        $this->set('amount_value', $amount_value);
        
        $amount_help_text = _(
            "Use dot for decimal digits. No whitespaces. " .
            "Can be negative."
        );
        $this->set('amount_help_text', $amount_help_text);
    }
    
    /**
     *
     * @return array
     */
    private function get_prepared_urls(): array
    {
        $start_url = "/whitelabels/prepaid/" . $this->whitelabel['id'];
        $urls = [
            'back' => $start_url,
            'form' => $start_url . "/new"
        ];
    
        return $urls;
    }
}
