<?php

use Fuel\Core\Validation;

/**
 * Class for preparing BitBayPay form
 */
final class Forms_Whitelabel_Payment_Bitbaypay extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("bitbaypay");

        $validation->add("input.marchant_bitbaypay_public_api_key", _("Public API Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 36);

        $validation->add("input.marchant_bitbaypay_private_api_key", _("Private API key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 36);

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
        $bitbaypay = [];
        
        $bitbaypay_public_api_key_error_class = "";
        if (isset($errors['input.marchant_bitbaypay_public_api_key'])) {
            $bitbaypay_public_api_key_error_class = ' has-error';
        }
        $bitbaypay['public_api_key_error_class'] = $bitbaypay_public_api_key_error_class;
            
        $bitbaypay_public_api_key_t = "";
        if (Input::post("input.marchant_bitbaypay_public_api_key") !== null) {
            $bitbaypay_public_api_key_t = Input::post("input.marchant_bitbaypay_public_api_key");
        } elseif (isset($data['marchant_bitbaypay_public_api_key'])) {
            $bitbaypay_public_api_key_t = $data['marchant_bitbaypay_public_api_key'];
        }
        $bitbaypay['public_api_key_value'] = Security::htmlentities($bitbaypay_public_api_key_t);

        $bitbaypay_private_api_key_error_class = "";
        if (isset($errors['input.marchant_bitbaypay_private_api_key'])) {
            $bitbaypay_private_api_key_error_class = ' has-error';
        }
        $bitbaypay['private_api_key_error_class'] = $bitbaypay_private_api_key_error_class;
            
        $bitbaypay_private_api_key_value_t = "";
        if (Input::post("input.marchant_bitbaypay_private_api_key") !== null) {
            $bitbaypay_private_api_key_value_t = Input::post("input.marchant_bitbaypay_private_api_key");
        } elseif (isset($data['marchant_bitbaypay_private_api_key'])) {
            $bitbaypay_private_api_key_value_t = $data['marchant_bitbaypay_private_api_key'];
        }
        $bitbaypay['private_api_key_value'] = Security::htmlentities($bitbaypay_private_api_key_value_t);

        $bitbaypay['public_api_key_help_text'] = _(
            "You can see it on keys list, it is used for recognizing user and key."
        );

        $bitbaypay['private_api_key_help_text'] = _(
            "You can see it on keys list, it is used for recognizing user and key."
        );

        $bitbaypay['api_keys_help_text'] = _(
            "You can generate keys by going to the <strong>Pages</strong> tab under " .
            "the <strong>Management</strong> section, " .
            "and then click <strong>Add Store</strong>. "
        );
        
        return $bitbaypay;
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
        $data['marchant_bitbaypay_public_api_key'] = $additional_values_validation->validated("input.marchant_bitbaypay_public_api_key");
        $data['marchant_bitbaypay_private_api_key'] = $additional_values_validation->validated("input.marchant_bitbaypay_private_api_key");
                
        return $data;
    }
}
