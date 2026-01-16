<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_Coinpayments form
 */
final class Forms_Whitelabel_Payment_Coinpayments extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("coinpayments");
        
        $validation->add("input.cpmerchantid", _("Merchant ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"])
            ->add_rule("max_length", 64);
        
        $validation->add("input.ipnsecret", _("IPN Secret"))
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
        $coinpayments = [];
        
        $merchantid_error_class = '';
        if (isset($errors['input.cpmerchantid'])) {
            $merchantid_error_class = ' has-error';
        }
        $coinpayments['merchantid_error_class'] = $merchantid_error_class;
        
        $merchantid_value_t = '';
        if (null !== Input::post("input.cpmerchantid")) {
            $merchantid_value_t = Input::post("input.cpmerchantid");
        } elseif (isset($data['merchant_id'])) {
            $merchantid_value_t = $data['merchant_id'];
        }
        $coinpayments['merchantid_value'] = Security::htmlentities($merchantid_value_t);

        $coinpayments['merchantid_info'] = _(
            "Can be found in the <strong>Merchant panel &gt; Account &gt; " .
            "Account Settings &gt; Basic Settings tab &gt; Your Merchant ID</strong>."
        );

        $ipnsecret_error_class = '';
        if (isset($errors['input.cpmerchantid'])) {
            $ipnsecret_error_class = ' has-error';
        }
        $coinpayments['ipn_secret_error_class'] = $ipnsecret_error_class;
            
        $ipn_secret_value_t = '';
        if (null !== Input::post("input.ipnsecret")) {
            $ipn_secret_value_t = Input::post("input.ipnsecret");
        } elseif (isset($data['ipn_secret'])) {
            $ipn_secret_value_t = $data['ipn_secret'];
        }
        $coinpayments['ipn_secret_value'] = Security::htmlentities($ipn_secret_value_t);

        $coinpayments['ipn_secret_info'] = _(
            "Can be set in the <strong>Merchant panel &gt; Account &gt; " .
            "Account Settings &gt; Merchant Settings tab &gt; IPN Secret</strong>."
        );
            
        return $coinpayments;
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
        $data['merchant_id'] = $additional_values_validation->validated("input.cpmerchantid");
        $data['ipn_secret'] = $additional_values_validation->validated("input.ipnsecret");
        
        return $data;
    }
}
