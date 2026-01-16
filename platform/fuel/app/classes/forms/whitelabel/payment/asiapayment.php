<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Asiapayment form
 */
final class Forms_Whitelabel_Payment_Asiapayment extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("asiapaymentgateway");

        $validation->add("input.merchant_id_asiapayment", _("Merchant ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"])
            ->add_rule("max_length", 64);

        $validation->add("input.sha256key", _("Sha256key"))
            ->add_rule("required")
            ->add_rule("max_length", 100);

        $validation->add("input.apiurl", _("API Url"))
            ->add_rule("required")
            ->add_rule("valid_url");

        $validation->add("input.asiapaymenttest", _("Test environment"))
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
        $asiapayment = [];
        
        $merchantid_error_class = '';
        if (isset($errors['input.merchant_id_asiapayment'])) {
            $merchantid_error_class = ' has-error';
        }
        $asiapayment['merchantid_error_class'] = $merchantid_error_class;
        
        $merchantid_class_value_t = '';
        if (null !== Input::post("input.merchant_id_asiapayment")) {
            $merchantid_class_value_t = Input::post("input.merchant_id_asiapayment");
        } elseif (isset($data['merchant_id_asiapayment'])) {
            $merchantid_class_value_t = $data['merchant_id_asiapayment'];
        }
        $asiapayment['merchantid_value'] = Security::htmlentities($merchantid_class_value_t);

        $sha256key_error_class = '';
        if (isset($errors['input.sha256key'])) {
            $sha256key_error_class = ' has-error';
        }
        $asiapayment['sha256key_error_class'] = $sha256key_error_class;
        
        $sha256key_value_t = '';
        if (null !== Input::post("input.sha256key")) {
            $sha256key_value_t = Input::post("input.sha256key");
        } elseif (isset($data['sha256key'])) {
            $sha256key_value_t = $data['sha256key'];
        }
        $asiapayment['sha256key_value'] = Security::htmlentities($sha256key_value_t);

        $apiurl_error_class = '';
        if (isset($errors['input.apiurl'])) {
            $apiurl_error_class = ' has-error';
        }
        $asiapayment['apiurl_error_class'] = $apiurl_error_class;
            
        $apiurl_value_t = '';
        if (null !== Input::post("input.apiurl")) {
            $apiurl_value_t = Input::post("input.apiurl");
        } elseif (isset($data['apiurl'])) {
            $apiurl_value_t = $data['apiurl'];
        }
        $asiapayment['apiurl_value'] = Security::htmlentities($apiurl_value_t);

        $test_checked = '';
        if ((null !== Input::post("input.asiapaymenttest") &&
                Input::post("input.asiapaymenttest") == 1) ||
            (isset($data['asiapaymenttest']) &&
                $data['asiapaymenttest'] == 1)
        ) {
            $test_checked = ' checked="checked"';
        }
        $asiapayment['test_checked'] = $test_checked;
        
        return $asiapayment;
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
        $data['merchant_id_asiapayment'] = $additional_values_validation->validated("input.merchant_id_asiapayment");
        $data['sha256key'] = $additional_values_validation->validated("input.sha256key");
        $data['apiurl'] = $additional_values_validation->validated("input.apiurl");
        $data['asiapaymenttest'] = $additional_values_validation->validated("input.asiapaymenttest") == 1 ? 1 : 0;
        
        return $data;
    }
}
