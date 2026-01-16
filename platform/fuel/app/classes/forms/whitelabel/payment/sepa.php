<?php

use Fuel\Core\Validation;

/**
 * Description of Forms_Whitelabel_Payment_Sepa
 */
final class Forms_Whitelabel_Payment_Sepa extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("sepa");

        $validation->add("input.sepa_member_id", _("MemberId"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10);

        $validation->add("input.sepa_to_type", _("Totype"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 30);

        $validation->add("input.sepa_secure_key", _("Secure Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 32);
        
        $validation->add("input.sepa_test", _("Test account"))
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
        $sepa = [];
        
        $sepa_member_id_error_class = '';
        if (isset($errors['input.sepa_member_id'])) {
            $sepa_member_id_error_class = ' has-error';
        }
        $sepa['sepa_member_id_error_class'] = $sepa_member_id_error_class;
        
        $sepa_member_id_value_t = '';
        if (null !== Input::post("input.sepa_member_id")) {
            $sepa_member_id_value_t = Input::post("input.sepa_member_id");
        } elseif (isset($data['sepa_member_id'])) {
            $sepa_member_id_value_t = $data['sepa_member_id'];
        }
        $sepa['sepa_member_id_value'] = Security::htmlentities($sepa_member_id_value_t);
        
        $sepa_member_id_info = _(
            "Here you can set Merchant's unique ID."
        );
        $sepa['sepa_member_id_info'] = $sepa_member_id_info;
        
        $sepa_secure_key_error_class = '';
        if (isset($errors['input.sepa_secure_key'])) {
            $sepa_secure_key_error_class = ' has-error';
        }
        $sepa['sepa_secure_key_error_class'] = $sepa_secure_key_error_class;
        
        $sepa_secure_key_value_t = '';
        if (null !== Input::post("input.sepa_secure_key")) {
            $sepa_secure_key_value_t = Input::post("input.sepa_secure_key");
        } elseif (isset($data['sepa_secure_key'])) {
            $sepa_secure_key_value_t = $data['sepa_secure_key'];
        }
        $sepa['sepa_secure_key_value'] = Security::htmlentities($sepa_secure_key_value_t);
        
        $sepa_secure_key_info = _(
            "Here you can set Secure Key."
        );
        $sepa['sepa_secure_key_info'] = $sepa_secure_key_info;
        
        $sepa_to_type_error_class = '';
        if (isset($errors['input.sepa_to_type'])) {
            $sepa_to_type_error_class = ' has-error';
        }
        $sepa['sepa_to_type_error_class'] = $sepa_to_type_error_class;
        
        $sepa_to_type_value_t = '';
        if (null !== Input::post("input.sepa_to_type")) {
            $sepa_to_type_value_t = Input::post("input.sepa_to_type");
        } elseif (isset($data['sepa_to_type'])) {
            $sepa_to_type_value_t = $data['sepa_to_type'];
        }
        $sepa['sepa_to_type_value'] = Security::htmlentities($sepa_to_type_value_t);
        
        $sepa_to_type_info = _(
            "Here you can set Merchant's Partner name (without spaces)."
        );
        $sepa['sepa_to_type_info'] = $sepa_to_type_info;
        
        $sepa_test_checked = '';
        if ((null !== Input::post("input.sepa_test") &&
                (int)Input::post("input.sepa_test") === 1) ||
            (isset($data['sepa_test']) &&
                (int)$data['sepa_test'] === 1)
        ) {
            $sepa_test_checked = ' checked="checked"';
        }
        $sepa['test_checked'] = $sepa_test_checked;
        
        return $sepa;
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
        $data['sepa_member_id'] = $additional_values_validation->validated("input.sepa_member_id");
        $data['sepa_secure_key'] = $additional_values_validation->validated("input.sepa_secure_key");
        $data['sepa_to_type'] = $additional_values_validation->validated("input.sepa_to_type");
        $data['sepa_test'] = $additional_values_validation->validated("input.sepa_test") == 1 ? 1 : 0;
        
        return $data;
    }
}
