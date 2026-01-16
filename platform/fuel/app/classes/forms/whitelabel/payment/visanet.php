<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_VisaNet
 */
final class Forms_Whitelabel_Payment_VisaNet extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("visanet");

        $validation->add("input.visanet_user", _("User"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 1024);
        
        $validation->add("input.visanet_password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 255);
        
        $validation->add("input.visanet_merchantid", _("Merchant ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 255);
        
        $validation->add("input.visanet_test", _("Test account"))
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
        $visanet = [];
        
        $visanet_user_error_class = '';
        if (isset($errors['input.visanet_user'])) {
            $visanet_user_error_class = ' has-error';
        }
        $visanet['visanet_user_error_class'] = $visanet_user_error_class;
        
        $visanet_user_value_t = '';
        if (null !== Input::post("input.visanet_user")) {
            $visanet_user_value_t = Input::post("input.visanet_user");
        } elseif (isset($data['visanet_user'])) {
            $visanet_user_value_t = $data['visanet_user'];
        }
        $visanet['visanet_user_value'] = Security::htmlentities($visanet_user_value_t);
        
        $visanet_password_error_class = '';
        if (isset($errors['input.visanet_password'])) {
            $visanet_password_error_class = ' has-error';
        }
        $visanet['visanet_password_error_class'] = $visanet_password_error_class;
        
        $visanet_password_value_t = '';
        if (null !== Input::post("input.visanet_password")) {
            $visanet_password_value_t = Input::post("input.visanet_password");
        } elseif (isset($data['visanet_password'])) {
            $visanet_password_value_t = $data['visanet_password'];
        }
        $visanet['visanet_password_value'] = Security::htmlentities($visanet_password_value_t);
        
        $visanet_merchantid_error_class = '';
        if (isset($errors['input.visanet_merchantid'])) {
            $visanet_merchantid_error_class = ' has-error';
        }
        $visanet['visanet_merchantid_error_class'] = $visanet_merchantid_error_class;
        
        $visanet_merchantid_value_t = '';
        if (null !== Input::post("input.visanet_merchantid")) {
            $visanet_merchantid_value_t = Input::post("input.visanet_merchantid");
        } elseif (isset($data['visanet_merchantid'])) {
            $visanet_merchantid_value_t = $data['visanet_merchantid'];
        }
        $visanet['visanet_merchantid_value'] = Security::htmlentities($visanet_merchantid_value_t);
        
        $visanet_test_checked = '';
        if ((null !== Input::post("input.visanet_test") &&
                Input::post("input.visanet_test") == 1) ||
            (isset($data['visanet_test']) &&
                $data['visanet_test'] == 1)
        ) {
            $visanet_test_checked = ' checked="checked"';
        }
        $visanet['visanet_test_checked'] = $visanet_test_checked;
            
        return $visanet;
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
        $data['visanet_user'] = $additional_values_validation->validated("input.visanet_user");
        $data['visanet_password'] = $additional_values_validation->validated("input.visanet_password");
        $data['visanet_merchantid'] = $additional_values_validation->validated("input.visanet_merchantid");
        $data['visanet_test'] = $additional_values_validation->validated("input.visanet_test") == 1 ? 1 : 0;

        return $data;
    }
}
