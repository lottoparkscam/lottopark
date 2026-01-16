<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Dusupay form
 */
final class Forms_Whitelabel_Payment_Dusupay extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("dusupay");

        $validation->add("input.merchant_dusupay_id", _("User ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        $validation->add("input.merchant_dusupay_apikey", _("Mackey/Salt Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"])
            ->add_rule("max_length", 144);
        
        $validation->add("input.dusupay_test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }
    
    /**
     *
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array {
        $dusupay = [];
        
        $dusupay_merchantid_error_class = "";
        if (isset($errors['input.merchant_dusupay_id'])) {
            $dusupay_merchantid_error_class = ' has-error';
        }
        $dusupay['merchantid_error_class'] = $dusupay_merchantid_error_class;
        
        $dusupay_merchantid_value_t = "";
        if (Input::post("input.merchant_dusupay_id") !== null) {
            $dusupay_merchantid_value_t = Input::post("input.merchant_dusupay_id");
        } elseif (isset($data['merchant_dusupay_id'])) {
            $dusupay_merchantid_value_t = $data['merchant_dusupay_id'];
        }
        $dusupay['merchantid_value'] = Security::htmlentities($dusupay_merchantid_value_t);

        $dusupay['merchantid_help_text'] = _(
            "You can find that value " .
            "by choosing proper <strong>Merchant Account</strong>, " .
            "choose <strong>Settings</strong> entrance from main menu " .
            "and <strong>General</strong> tab."
        );

        $dusupay_apikey_error_class = "";
        if (isset($errors['input.merchant_dusupay_apikey'])) {
            $dusupay_apikey_error_class = ' has-error';
        }
        $dusupay['apikey_error_class'] = $dusupay_apikey_error_class;
        
        $dusupay_apikey_value_t = "";
        if (Input::post("input.merchant_dusupay_apikey") !== null) {
            $dusupay_apikey_value_t = Input::post("input.merchant_dusupay_apikey");
        } elseif (isset($data['merchant_dusupay_apikey'])) {
            $dusupay_apikey_value_t = $data['merchant_dusupay_apikey'];
        }
        $dusupay['apikey_value'] = Security::htmlentities($dusupay_apikey_value_t);

        $dusupay['apikey_help_text'] = _(
            "You can find that value " .
            "by choosing proper <strong>Merchant Account</strong>, " .
            "choose <strong>Settings</strong> entrance from main menu " .
            "and <strong>Security and Notifications</strong> tab " .
            "and finally click on <strong>Click To View " .
            "Mackey/Salt Key</strong> button."
        );

        $dusupay_test_checked = '';
        if ((null !== Input::post("input.dusupay_test") &&
                Input::post("input.dusupay_test") == 1) ||
            (isset($data['dusupay_test']) && $data['dusupay_test'] == 1)
        ) {
            $dusupay_test_checked = ' checked="checked"';
        }
        $dusupay['test_checked'] = $dusupay_test_checked;
        
        return $dusupay;
    }
    
    /**
     *
     * @param Validation|null $additional_values_validation
     * @return array
     */
    public function get_data(
        ?Validation $additional_values_validation
    ): array {
        $data = [];
        $data['merchant_dusupay_id'] = $additional_values_validation->validated('input.merchant_dusupay_id');
        $data['merchant_dusupay_apikey'] = $additional_values_validation->validated('input.merchant_dusupay_apikey');
        $data['dusupay_test'] = $additional_values_validation->validated('input.dusupay_test');
        
        return $data;
    }
}
