<?php

use Fuel\Core\Validation;

/**
 * Class for preparing ecoPayz form
 */
class Forms_Whitelabel_Payment_Ecopayz extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("ecopayz");
        
        $validation->add("input.merchantid", _("Merchant ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);
        
        $validation->add("input.account", _("Merchant Account Number"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);
        
        $validation->add("input.ecotest", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 100);
        
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
        $ecopayz = [];
        
        $merchantid_error_class = '';
        if (isset($errors['input.merchantid'])) {
            $merchantid_error_class = ' has-error';
        }
        $ecopayz['merchantid_error_class'] = $merchantid_error_class;
        
        $merchantid_value_t = '';
        if (null !== Input::post("input.merchantid")) {
            $merchantid_value_t = Input::post("input.merchantid");
        } elseif (isset($data['merchant_id'])) {
            $merchantid_value_t = $data['merchant_id'];
        }
        $ecopayz['merchantid_value'] = Security::htmlentities($merchantid_value_t);

        $account_error_class = '';
        if (isset($errors['input.account'])) {
            $account_error_class = ' has-error';
        }
        $ecopayz['account_error_class'] = $account_error_class;
        
        $account_value_t = '';
        if (null !== Input::post("input.account")) {
            $account_value_t = Input::post("input.account");
        } elseif (isset($data['account'])) {
            $account_value_t = $data['account'];
        }
        $ecopayz['account_value'] = Security::htmlentities($account_value_t);

        $password_error_class = '';
        if (isset($errors['input.password'])) {
            $password_error_class = ' has-error';
        }
        $ecopayz['password_error_class'] = $password_error_class;
        
        $password_value_t = '';
        if (null !== Input::post("input.password")) {
            $password_value_t = Input::post("input.password");
        } elseif (isset($data['password'])) {
            $password_value_t = $data['password'];
        }
        $ecopayz['password_value'] = Security::htmlentities($password_value_t);

        $test_checked = '';
        if ((null !== Input::post("input.ecotest") &&
                Input::post("input.ecotest") == 1) ||
            (isset($data['test']) &&
                $data['test'] == 1)
        ) {
            $test_checked = ' checked="checked"';
        }
        $ecopayz['test_checked'] = $test_checked;
        
        return $ecopayz;
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
        $data['merchant_id'] = $additional_values_validation->validated("input.merchantid");
        $data['account'] = $additional_values_validation->validated("input.account");
        $data['test'] = $additional_values_validation->validated("input.ecotest") == 1 ? 1 : 0;
        $data['password'] = $additional_values_validation->validated("input.password");
        
        return $data;
    }
}
