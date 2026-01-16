<?php

/**
 * Description of Presenter_Admin_Whitelabels_List
 */
class Presenter_Admin_Whitelabels_List extends Presenter_Presenter
{
    /**
     *
     * @return void
     */
    public function view(): void
    {
        $text_to_show_on_confirmation = _(
            "Are you sure you want to disable this lottery?&#10;" .
            "All bought users' tickets will stay pending unless " .
            "you provide the latest draw numbers manually."
        );
        $this->set('text_to_show_on_confirmation', $text_to_show_on_confirmation);
        
        $prepared_whitelabels = $this->get_prepare_whitelabels();
        $this->set('whitelabels', $prepared_whitelabels);
    }
    
    /**
     *
     * @return array
     */
    private function get_prepare_whitelabels(): array
    {
        $prepared_whitelabels = [];
        
        foreach ($this->whitelabels as $whitelabel) {
            $single_row = [
                'edit_url' => '/whitelabels/edit/' . $whitelabel['id'],
                'languages_url' => '/whitelabels/languages/' . $whitelabel['id'],
                'payments_url' => '/whitelabels/payments/' . $whitelabel['id'],
                'prepaid_url' => '/whitelabels/prepaid/' . $whitelabel['id'],
                'settings_url' => '/whitelabels/settings/' . $whitelabel['id'],
                'currencies_url' => '/whitelabels/settings_currency/' . $whitelabel['id'],
            ];
            
            $single_row['name'] = Security::htmlentities($whitelabel['name']);
            
            $single_row['domain'] = Security::htmlentities($whitelabel['domain']);
            
            $type = 'V' . $whitelabel['type'];
            $single_row['type'] = Security::htmlentities($type);
            
            $show_prepaid_button = false;
            $prepaid_text = _("NA");
            $prepaid_alert_limit_text = _("NA");
            $prepaid_class_alert = "";
            
            if (!Helpers_Whitelabel::is_V1($whitelabel['type'])) {
                $manager_currency = Helpers_Currency::get_mtab_currency(
                    false,
                    null,
                    $whitelabel['manager_site_currency_id']
                );
                
                $prepaid_text_temp = Lotto_View::format_currency(
                    $whitelabel['prepaid'],
                    $manager_currency['code'],
                    true
                );
                
                $prepaid_alert_limit_text_temp = Lotto_View::format_currency(
                    $whitelabel['prepaid_alert_limit'],
                    $manager_currency['code'],
                    true
                );
                
                $prepaid_text = $prepaid_text_temp;
                $prepaid_alert_limit_text = $prepaid_alert_limit_text_temp;
                
                $show_prepaid_button = true;
                
                if ($whitelabel['prepaid'] < 0) {
                    $prepaid_class_alert = "alert alert-danger";
                } elseif ($whitelabel['prepaid_alert_limit'] > $whitelabel['prepaid']) {
                    $prepaid_class_alert = "alert alert-warning";
                }
            }
            $single_row['prepaid_text'] = Security::htmlentities($prepaid_text);
            $single_row['prepaid_alert_limit_text'] = Security::htmlentities($prepaid_alert_limit_text);
            $single_row['show_prepaid_button'] = Security::htmlentities($show_prepaid_button);
            $single_row['prepaid_class_alert'] = Security::htmlentities($prepaid_class_alert);
            $single_row['last_login'] = Security::htmlentities($whitelabel['last_login']);
            $single_row['last_active'] = Security::htmlentities($whitelabel['last_active']);
            
            $margin_in_percentage = round($whitelabel['margin'] / 100, 2);
            $margin_in_formatted = Lotto_View::format_percentage($margin_in_percentage);
            
            $single_row['margin'] = $margin_in_formatted;
            
            $prepared_whitelabels[] = $single_row;
        }
        
        return $prepared_whitelabels;
    }
}
