<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Cubits form
 */
final class Forms_Whitelabel_Payment_Cubits extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("cubits");
        
        $validation->add("input.cubits_apikey", _("API Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 50); // max. 32 + buffer
        
        $validation->add("input.cubits_apisecret", _("API Secret"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 100); // max. 64 + buffer
        
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
        $cubits = [];
        
        $apikey_error_class = '';
        if (isset($errors['input.cubits_apikey'])) {
            $apikey_error_class = ' has-error';
        }
        $cubits['apikey_error_class'] = $apikey_error_class;
        
        $apikey_value_t = '';
        if (null !== Input::post("input.cubits_apikey")) {
            $apikey_value_t = Input::post("input.cubits_apikey");
        } elseif (isset($data['api_key'])) {
            $apikey_value_t = $data['api_key'];
        }
        $cubits['apikey_value'] = Security::htmlentities($apikey_value_t);
        
        $apisecret_error_class = '';
        if (isset($errors['input.cubits_apisecret'])) {
            $apisecret_error_class = ' has-error';
        }
        $cubits['apisecret_error_class'] = $apisecret_error_class;
        
        $apisecret_value_t = '';
        if (null !== Input::post("input.cubits_apisecret")) {
            $apisecret_value_t = Input::post("input.cubits_apisecret");
        } elseif (isset($data['api_secret'])) {
            $apisecret_value_t = $data['api_secret'];
        }
        $cubits['apisecret_value'] = Security::htmlentities($apisecret_value_t);
        
        $cubits['apisecret_info'] = _(
            "Can be generated in <strong>Merchant &gt; Integration " .
            "Tools &gt; API Integration</strong>."
        );
        
        return $cubits;
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
        $data['api_key'] = $additional_values_validation->validated("input.cubits_apikey");
        $data['api_secret'] = $additional_values_validation->validated("input.cubits_apisecret");
        
        return $data;
    }
}
