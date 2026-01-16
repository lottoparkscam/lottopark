<?php

use Fuel\Core\Validation;

/**
 * Description of Forms_Whitelabel_Payment_Custom
 */
final class Forms_Whitelabel_Payment_Custom extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("custom");

        $validation->add("input.custom_url_to_redirect", _("Custom url to redirect"))
            ->add_rule("trim")
            ->add_rule("valid_url")
            ->add_rule("max_length", 1024);

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
        $custom = [];
        
        $custom_url_to_redirect_error_class = '';
        if (isset($errors['input.custom_url_to_redirect'])) {
            $custom_url_to_redirect_error_class = ' has-error';
        }
        $custom['url_to_redirect_error_class'] = $custom_url_to_redirect_error_class;
        
        $custom_url_to_redirect_value_t = '';
        if (null !== Input::post("input.custom_url_to_redirect")) {
            $custom_url_to_redirect_value_t = Input::post("input.custom_url_to_redirect");
        } elseif (isset($data['custom_url_to_redirect'])) {
            $custom_url_to_redirect_value_t = $data['custom_url_to_redirect'];
        }
        $custom['url_to_redirect_value'] = Security::htmlentities($custom_url_to_redirect_value_t);
        
        $custom_info = _(
            "Here you can set URL of the page to redirect on."
        );
        $custom['url_to_redirect_info'] = $custom_info;
        
        return $custom;
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
        $data['custom_url_to_redirect'] = $additional_values_validation->validated("input.custom_url_to_redirect");
        
        return $data;
    }
}
