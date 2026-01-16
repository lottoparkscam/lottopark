<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Entercash form
 */
final class Forms_Whitelabel_Payment_Entercash extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("entercash");
        
        $validation->add("input.apiid", _("API ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"])
            ->add_rule("max_length", 50);
        
        $validation->add("input.privatekey", _("Private Key"))
            ->add_rule("trim")
            ->add_rule("required");
        
        $validation->add("input.entercash_test", _("Test account"))
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
        $entercash = [];
        
        $apiid_error_class = '';
        if (isset($errors['input.apiid'])) {
            $apiid_error_class = ' has-error';
        }
        $entercash['api_id_error_class'] = $apiid_error_class;
        
        $apiid_value_t = '';
        if (null !== Input::post("input.apiid")) {
            $apiid_value_t = Input::post("input.apiid");
        } elseif (isset($data['api_id'])) {
            $apiid_value_t = $data['api_id'];
        }
        $entercash['api_id_value'] = Security::htmlentities($apiid_value_t);

        $entercash['api_id_info'] = _(
            "Can be found in the <strong>Merchant panel &gt; Security " .
            "&gt; Public key (PEM-file) &gt; API-ID</strong>."
        );

        $privatekey_error_class = '';
        if (isset($errors['input.privatekey'])) {
            $privatekey_error_class = ' has-error';
        }
        $entercash['private_key_error_class'] = $privatekey_error_class;
        
        $privatekey_value_t = '';
        if (null !== Input::post("input.privatekey")) {
            $privatekey_value_t = Input::post("input.privatekey");
        } elseif (isset($data['private_key'])) {
            $privatekey_value_t = $data['private_key'];
        }
        $entercash['private_key_value'] = Security::htmlentities($privatekey_value_t);

        $entercash['private_key_info'] = _(
            "Can be generated using following commands: <br><strong>Private:" .
            "</strong> <em>openssl genrsa -out your-private-key.pem 2048" .
            "</em><br><strong>Public: </strong> <em>openssl rsa -pubout " .
            "-in your-private-key.pem -out your-public-key.pem -outform " .
            "PEM</em><br>Upload public key file to <strong>Merchant panel " .
            "&gt; Security &gt; Public key (PEM-file) &gt; Public key " .
            "(Pem file)</strong>."
        );

        $test_checked = '';
        if ((null !== Input::post("input.entercash_test") &&
                Input::post("input.entercash_test") == 1) ||
            (isset($data['test']) &&
                $data['test'] == 1)
        ) {
            $test_checked = ' checked="checked"';
        }
        $entercash['test_checked'] = $test_checked;

        return $entercash;
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
        $data['api_id'] = $additional_values_validation->validated("input.apiid");
        $data['private_key'] = $additional_values_validation->validated("input.privatekey");
        $data['test'] = $additional_values_validation->validated("input.entercash_test") == 1 ? 1 : 0;
                
        return $data;
    }
}
