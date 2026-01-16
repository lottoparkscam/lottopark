<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 20.03.2019
 * Time: 11:30
 */

use Core\App;
use Fuel\Core\Input;
use Fuel\Core\Security;

/**
 * Use this trait to implement methods for presentation of payment edit view.
 * IMPORTANT NOTE: this trait can be used only by objects extending \Presenter class.
 */
trait Presenter_Traits_Payments_Edit
{

    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $main_title = _("Payment method");
        if (isset($this->edit)) {
            $main_title .= " - " . $this->edit['name'];
            $main_title .= " (" . _("Payment ID: ") . $this->edit['id'] . ")";
        }
        $this->set("main_payment_method_title", $main_title);
        
        $payment_methods_urls = $this->prepare_urls();
        $this->set("urls", $payment_methods_urls);
        
        $error_class = $this->prepare_error_class();
        $this->set("error_class", $error_class);
        
        $prepared_langs = $this->prepare_language_list($this->edit);
        $this->set("langs", $prepared_langs);
        
        $prepared_methods = $this->prepare_method_list($this->edit);
        $this->set("methods", $prepared_methods);
        
        // These two functions should be call in that order
        // becuase $this->currencies could be overwritten and
        // could not be used properly in prepare_payment_currency_list()
        $prepared_currencies = $this->prepare_currency_list($this->edit);
        if (!isset($this->edit_lp)) {
            $prepared_payment_currencies = $this->prepare_payment_currency_list($this->edit);
            $this->set("payment_currencies", $prepared_payment_currencies);
        }
        $this->set("currencies", $prepared_currencies);
        
        $prepare_main_values = $this->prepare_main_values($this->edit);
        $this->set("main_values", $prepare_main_values);

        $prepareApp = Container::get(App::class);
        $this->set('app', $prepareApp);
        
        $this->prepare_easy_payment_gateway();
        $this->prepare_astro_pay();
        $this->preparePicksellGateway();
        $this->preparePspGateGateway();
        $this->prepareZenGateway();
        $this->prepareOnramperGateway();
        $this->prepareNowPaymentsGateway();
        $this->prepareGcashGateway();
        $this->prepareLencoGateway();
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $add_edit_url = $this->begin_payments_url . '/';
        
        $currency_list_url = '';
        if (isset($this->edit_lp)) {
            $add_edit_url .= 'edit/' . $this->edit_lp;
            $currency_list_url = $this->begin_payments_url .
                "/currency/" .
                $this->edit_lp .
                "/list/";
        } else {
            $add_edit_url .= 'new';
        }
        
        $urls = [
            "back" => $this->begin_payments_url,
            "add_edit" => $add_edit_url,
            "currency_list" => $currency_list_url
        ];
        
        return $urls;
    }
    
    /**
     * This function prepare error class table (class as class for View within HTML)
     *
     * @return array
     */
    private function prepare_error_class(): array
    {
        $error_class = [];
        
        $lang_error_class = '';
        if (isset($this->errors['input.language'])) {
            $lang_error_class = ' has-error';
        }
        $error_class["language"] = $lang_error_class;
        
        $method_error_class = '';
        if (isset($this->errors['input.method'])) {
            $method_error_class = ' has-error';
        }
        $error_class["method"] = $method_error_class;
        
        $name_error_class = '';
        if (isset($this->errors['input.name'])) {
            $name_error_class = ' has-error';
        }
        $error_class["name"] = $name_error_class;
        
        $cost_percent_error_class = '';
        if (isset($this->errors['input.cost_percent'])) {
            $cost_percent_error_class = ' has-error';
        }
        $error_class["cost_percent"] = $cost_percent_error_class;
        
        $cost_fixed_error_class = '';
        if (isset($this->errors['input.cost_fixed'])) {
            $cost_fixed_error_class = ' has-error';
        }
        $error_class["cost_fixed"] = $cost_fixed_error_class;
        
        if (!isset($this->edit_lp)) {
            $payment_currency_error_class = '';
            if (isset($this->errors['input.payment_currency'])) {
                $payment_currency_error_class = ' has-error';
            }
            $error_class["payment_currency"] = $payment_currency_error_class;
        
            $min_purchase_error_class = '';
            if (isset($this->errors['input.min_purchase_by_currency'])) {
                $min_purchase_error_class = ' has-error';
            }
            $error_class["min_purchase_by_currency"] = $min_purchase_error_class;
        }
        
        $custom_logotype_error_class = '';
        if (isset($this->errors['input.custom_logotype'])) {
            $custom_logotype_error_class = ' has-error';
        }
        $error_class["custom_logotype"] = $custom_logotype_error_class;
        
        return $error_class;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $edit
     * @return array
     */
    protected function prepare_language_list(
        Model_Whitelabel_Payment_Method $edit = null
    ):? array {
        $prepared_langs = [];
        
        $language_id_to_select = -1;
        if (Input::post("input.language") !== null) {
            $language_id_to_select = (int)Input::post("input.language");
        } elseif (isset($edit->language_id)) {
            $language_id_to_select = (int)$edit->language_id;
        } else {
            $language_id_to_select = 1;     // Equal to English
        }
        
        foreach ($this->langs as $lang) {
            $is_selected = '';
            if ((int)$language_id_to_select === (int)$lang['id']) {
                $is_selected = ' selected="selected"';
            }
            
            $show_t = Lotto_View::format_language($lang['code']);
            $show_text = Security::htmlentities($show_t);
            
            $single_lang = [
                "id" => $lang['id'],
                "show_text" => $show_text,
                "selected" => $is_selected
            ];
             
            $prepared_langs[] = $single_lang;
        }
        
        return $prepared_langs;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $edit
     * @return array
     */
    protected function prepare_method_list(
        Model_Whitelabel_Payment_Method $edit = null
    ):? array {
        $prepared_methods = [];
        
        $method_id_to_select = -1;
        if (Input::post("input.method") !== null) {
            $method_id_to_select = (int)Input::post("input.method");
        } elseif (isset($edit->payment_method_id)) {
            $method_id_to_select = (int)$edit->payment_method_id;
        } else {
            $method_id_to_select = 0;       // Equal to 'None' option
        }
        
        foreach ($this->methods as $method) {
            $is_selected = '';
            if ((int)$method_id_to_select === (int)$method['id']) {
                $is_selected = ' selected="selected"';
            }
            $method_name = Security::htmlentities($method['name']);
            
            $single_method = [
                "id" => $method['id'],
                "name" => $method_name,
                "selected" => $is_selected
            ];
             
            $prepared_methods[] = $single_method;
        }
        
        return $prepared_methods;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $edit
     * @return array
     */
    protected function prepare_currency_list(
        Model_Whitelabel_Payment_Method $edit = null
    ):? array {
        $prepared_currencies = [];
        
        $cost_currency_id_to_select = -1;
        if (Input::post("input.cost_currency") !== null) {
            $cost_currency_id_to_select = (int)Input::post("input.cost_currency");
        } elseif (isset($edit->cost_currency_id)) {
            $cost_currency_id_to_select = (int)$edit->cost_currency_id;
        } else {
            $cost_currency_id_to_select = (int)$this->default_currency_id;
        }
        
        foreach ($this->currencies as $currency_id => $currency) {
            $is_selected = "";
            if ((int)$cost_currency_id_to_select === (int)$currency_id) {
                $is_selected = ' selected="selected"';
            }
            $currency_code = Security::htmlentities($currency);
            
            $single_currency = [
                "id" => $currency_id,
                "code" => $currency_code,
                "selected" => $is_selected
            ];
             
            $prepared_currencies[] = $single_currency;
        }
        
        return $prepared_currencies;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $edit
     * @return array
     */
    protected function prepare_payment_currency_list(
        Model_Whitelabel_Payment_Method $edit = null
    ):? array {
        $prepared_payment_currencies = [];
        
        $payment_currency_id_to_select = -1;
        if (Input::post("input.payment_currency") !== null) {
            $payment_currency_id_to_select = (int)Input::post("input.payment_currency");
        } elseif (isset($edit->payment_currency_id)) {
            $payment_currency_id_to_select = (int)$edit->payment_currency_id;
        } else {
            $payment_currency_id_to_select = (int)$this->default_currency_id;
        }
        
        foreach ($this->currencies as $currency_id => $currency) {
            $is_selected = "";
            if ((int)$payment_currency_id_to_select === (int)$currency_id) {
                $is_selected = ' selected="selected"';
            }
            
            $currency_code = Security::htmlentities($currency);
            
            $single_payment_currency = [
                "id" => $currency_id,
                "code" => $currency_code,
                "selected" => $is_selected
            ];
             
            $prepared_payment_currencies[] = $single_payment_currency;
        }
        
        return $prepared_payment_currencies;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method $edit
     * @return array|null
     */
    private function prepare_main_values(
        Model_Whitelabel_Payment_Method $edit = null
    ):? array {
        $prepared_main_values = [];
        
        $name_value_temp = '';
        if (null !== Input::post("input.name")) {
            $name_value_temp = Input::post("input.name");
        } elseif (isset($edit['name'])) {
            $name_value_temp = $edit['name'];
        }
        $prepared_main_values["name"] = Security::htmlentities($name_value_temp);
        
        $cost_percent_value_temp = '0';
        if (null !== Input::post("input.cost_percent")) {
            $cost_percent_value_temp = Input::post("input.cost_percent");
        } elseif (isset($edit['cost_percent'])) {
            $cost_percent_value_temp = $edit['cost_percent'];
        }
        $prepared_main_values["cost_percent"] = Security::htmlentities($cost_percent_value_temp);
        
        $cost_fixed_value_temp = '0';
        if (null !== Input::post("input.cost_fixed")) {
            $cost_fixed_value_temp = Input::post("input.cost_fixed");
        } elseif (isset($edit['cost_fixed'])) {
            $cost_fixed_value_temp = $edit['cost_fixed'];
        }
        $prepared_main_values["cost_fixed"] = Security::htmlentities($cost_fixed_value_temp);
        
        if (!isset($this->edit_lp)) {
            $min_purchase_t = '0.00';
            if (null !== Input::post("input.min_purchase_by_currency")) {
                $min_purchase_t = Input::post("input.min_purchase_by_currency");
            } elseif (isset($edit['min_purchase_by_currency'])) {
                $min_purchase_t = $edit['min_purchase_by_currency'];
            }
            $prepared_main_values["min_purchase_by_currency"] = Security::htmlentities($min_purchase_t);

            $prepared_main_values["min_purchase_currency_code"] = Security::htmlentities($this->min_purchase_currency_code);
        }

        $show_checked = '';
        if ((null !== Input::post("input.show") &&
                (int)Input::post("input.show") === 1) ||
            (isset($edit['show']) &&
                (int)$edit['show'] === 1)
        ) {
            $show_checked = ' checked="checked"';
        }
        $prepared_main_values["show"] = $show_checked;

        $only_deposit_checked = '';
        if ((null !== Input::post("input.only_deposit") &&
                (int)Input::post("input.only_deposit") === 1) ||
            (isset($edit['only_deposit']) &&
                (int)$edit['only_deposit'] === 1)
        ) {
            $only_deposit_checked = ' checked="checked"';
        }
        $prepared_main_values["only_deposit"] = $only_deposit_checked;
        
        $show_payment_logotype_checked = '';
        if ((null !== Input::post("input.show_payment_logotype") &&
                (int)Input::post("input.show_payment_logotype") === 1) ||
            (isset($edit['show_payment_logotype']) &&
                (int)$edit['show_payment_logotype'] === 1)
        ) {
            $show_payment_logotype_checked = ' checked="checked"';
        }
        $prepared_main_values["show_payment_logotype"] = $show_payment_logotype_checked;
        
        $custom_logotype_value_temp = '';
        if (null !== Input::post("input.custom_logotype")) {
            $custom_logotype_value_temp = Input::post("input.custom_logotype");
        } elseif (isset($edit['custom_logotype'])) {
            $custom_logotype_value_temp = $edit['custom_logotype'];
        }
        $prepared_main_values["custom_logotype"] = Security::htmlentities($custom_logotype_value_temp);

        $allow_user_to_select_currency_checked = '';

        $isAllowUserToSelectCurrencyInputChecked = null !== Input::post('input.allow_user_to_select_currency') &&
            (int)Input::post('input.allow_user_to_select_currency') === 1;

        $isAllowUserToSelectCurrencyChecked = isset($edit['allow_user_to_select_currency']) &&
            (int)$edit['allow_user_to_select_currency'] === 1;

        if ($isAllowUserToSelectCurrencyInputChecked || $isAllowUserToSelectCurrencyChecked) {
            $allow_user_to_select_currency_checked = ' checked';
        }
        $prepared_main_values['allow_user_to_select_currency'] = $allow_user_to_select_currency_checked;
        
        return $prepared_main_values;
    }
    
    /**
     * Prepare easy payment gateway.
     *
     * @return void
     */
    private function prepare_easy_payment_gateway(): void
    {
        /**
         * @var Presenter_Admin_Whitelabels_Payments_Edit|Presenter_Whitelabel_Settings_Payments_Edit $this
         */
        // NOTE: _epg abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'easy_payment_gateway_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_epg', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $closure_input_last_value = $this->closure_input_last_value('data', $input_name, $prefix);
        $this->set_safe('input_last_value_epg', $closure_input_last_value);
        $this->set_safe('checked_epg', $this->closure_checked($closure_input_last_value));
    }

    /**
     * Prepare astro pay.
     *
     * @return void
     */
    private function prepare_astro_pay(): void
    {
        // NOTE: _ap abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'astro_pay_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_ap', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $this->set_safe('input_last_value_ap', $this->closure_input_last_value('data', $input_name, $prefix));
        $this->set_safe('checked_ap', $this->closure_checked($this->input_last_value_ap));
    }

    private function preparePicksellGateway(): void
    {
        // NOTE: _picksell abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'picksell_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_picksell', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $closure_input_last_value = $this->closure_input_last_value('data', $input_name, $prefix);
        $this->set_safe('input_last_value_picksell', $closure_input_last_value);
        $this->set_safe('checked_picksell', $this->closure_checked($closure_input_last_value));
    }

    private function preparePspGateGateway(): void
    {
        // NOTE: _pspgate abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'pspgate_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_pspgate', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $closure_input_last_value = $this->closure_input_last_value('data', $input_name, $prefix);
        $this->set_safe('input_last_value_pspgate', $closure_input_last_value);
        $this->set_safe('checked_pspgate', $this->closure_checked($closure_input_last_value));
    }

    private function prepareZenGateway(): void
    {
        // NOTE: _zen abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'zen_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_zen', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $closure_input_last_value = $this->closure_input_last_value('data', $input_name, $prefix);
        $this->set_safe('input_last_value_zen', $closure_input_last_value);
        $this->set_safe('checked_zen', $this->closure_checked($closure_input_last_value));
    }

    private function prepareOnramperGateway(): void
    {
        // NOTE: _onramper abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'onramper_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_onramper', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $closure_input_last_value = $this->closure_input_last_value('data', $input_name, $prefix);
        $this->set_safe('input_last_value_onramper', $closure_input_last_value);
        $this->set_safe('checked_onramper', $this->closure_checked($closure_input_last_value));
    }

    private function prepareNowPaymentsGateway(): void
    {
        // NOTE: _nowpayments abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'nowpayments_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_nowpayments', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $closure_input_last_value = $this->closure_input_last_value('data', $input_name, $prefix);
        $this->set_safe('input_last_value_nowpayments', $closure_input_last_value);
        $this->set_safe('checked_nowpayments', $this->closure_checked($closure_input_last_value));
    }

    private function prepareGcashGateway(): void
    {
        // NOTE: _gcash abbreviation due to negligibility of information, it's used only to differentiate between other closures.
        $prefix = 'gcash_';
        $input_name = 'input';
        // set closure for error classes, view will check using closure.
        $this->set_safe('input_has_error_gcash', $this->closure_input_has_error_class($input_name, $prefix));
        // set closures for input values
        $closure_input_last_value = $this->closure_input_last_value('data', $input_name, $prefix);
        $this->set_safe('input_last_value_gcash', $closure_input_last_value);
        $this->set_safe('checked_gcash', $this->closure_checked($closure_input_last_value));
    }

    private function prepareLencoGateway(): void
    {
        $prefix = 'lenco_';
        $inputName = 'input';

        $this->set_safe('inputHasErrorLenco', $this->closure_input_has_error_class($inputName, $prefix));

        $closureInputLastValue = $this->closure_input_last_value('data', $inputName, $prefix);
        $this->set_safe('inputLastValueLenco', $closureInputLastValue);
        $this->set_safe('isCheckedLenco', $this->closure_checked($closureInputLastValue));
    }
}
