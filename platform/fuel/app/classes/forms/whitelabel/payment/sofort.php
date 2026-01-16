<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Sofort form
 */
final class Forms_Whitelabel_Payment_Sofort extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("sofort");
        
        $validation->add("input.configkey", _("Configuration Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 60);
                        
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
        $sofort = [];
        
        $configkey_error_class = '';
        if (isset($errors['input.configkey'])) {
            $configkey_error_class = ' has-error';
        }
        $sofort['config_key_error_class'] = $configkey_error_class;
        
        $configkey_value_t = '';
        if (null !== Input::post("input.configkey")) {
            $configkey_value_t = Input::post("input.configkey");
        } elseif (isset($data['config_key'])) {
            $configkey_value_t = $data['config_key'];
        }
        $sofort['config_key_value'] = Security::htmlentities($configkey_value_t);

        $sofort['config_key_info'] = _(
            "Can be found in the <strong>Merchant panel &gt; Projects " .
            "&gt; My projects &gt; (choose your project) &gt; General " .
            "settings &gt; Configuration key for your shop system</strong>."
        );

        return $sofort;
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
        $data['config_key'] = $additional_values_validation->validated("input.configkey");
        
        return $data;
    }
}
