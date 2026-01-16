<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Tpay form
 */
final class Forms_Whitelabel_Payment_Tpay extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("tpay");
        
        $validation->add("input.tpayid", _("tpay.com ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("is_numeric");
        
        $validation->add("input.tpaysecuritykey", _("tpay.com Security Code"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 32);
                        
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
        $tpay = [];
        
        $tpayid_error_class = '';
        if (isset($errors['input.tpayid'])) {
            $tpayid_error_class = ' has-error';
        }
        $tpay['id_error_class'] = $tpayid_error_class;
        
        $tpayid_value_t = '';
        if (null !== Input::post("input.tpayid")) {
            $tpayid_value_t = Input::post("input.tpayid");
        } elseif (isset($data['tpay_id'])) {
            $tpayid_value_t = $data['tpay_id'];
        }
        $tpay['id_value'] = Security::htmlentities($tpayid_value_t);

        $tpaysecuritykey_error_class = '';
        if (isset($errors['input.tpaysecuritykey'])) {
            $tpaysecuritykey_error_class = ' has-error';
        }
        $tpay['security_key_error_class'] = $tpaysecuritykey_error_class;
        
        $tpaysecuritykey_value_t = '';
        if (null !== Input::post("input.tpaysecuritykey")) {
            $tpaysecuritykey_value_t = Input::post("input.tpaysecuritykey");
        } elseif (isset($data['tpay_security_key'])) {
            $tpaysecuritykey_value_t = $data['tpay_security_key'];
        }
        $tpay['security_key_value'] = Security::htmlentities($tpaysecuritykey_value_t);

        $tpay['security_key_info'] = _(
            "Can be set in <strong>Settings &gt; notifications &gt; Security " .
            "Code</strong> section of your tpay.com Merchant Panel.<br>" .
            "Make sure <strong>Allow notification URL overwrite</strong> " .
            "is set to <strong>yes</strong>."
        );
        
        return $tpay;
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
        $data['tpay_id'] = $additional_values_validation->validated("input.tpayid");
        $data['tpay_security_key'] = $additional_values_validation->validated("input.tpaysecuritykey");
        
        return $data;
    }
}
