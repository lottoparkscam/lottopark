<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_CreditCardSandbox
 */
final class Forms_Whitelabel_Payment_CreditCardSandbox extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("creditcardsandbox");

        $validation->add("input.creditcardsandbox_url_to_redirect", _("Credit Card Sandbox url to redirect"))
            ->add_rule("trim")
            ->add_rule("required")
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
        $creditcardsandbox = [];
        
        $creditcardsandbox_url_to_redirect_error_class = '';
        if (isset($errors['input.creditcardsandbox_url_to_redirect'])) {
            $creditcardsandbox_url_to_redirect_error_class = ' has-error';
        }
        $creditcardsandbox['url_to_redirect_error_class'] = $creditcardsandbox_url_to_redirect_error_class;
        
        $creditcardsandbox_url_to_redirect_value_t = '';
        if (null !== Input::post("input.creditcardsandbox_url_to_redirect")) {
            $creditcardsandbox_url_to_redirect_value_t = Input::post("input.creditcardsandbox_url_to_redirect");
        } elseif (isset($data['creditcardsandbox_url_to_redirect'])) {
            $creditcardsandbox_url_to_redirect_value_t = $data['creditcardsandbox_url_to_redirect'];
        }
        $creditcardsandbox['url_to_redirect_value'] = Security::htmlentities($creditcardsandbox_url_to_redirect_value_t);
        
        $creditcardsandbox_info = _(
            "Here you can set URL of the page to redirect on."
        );
        $creditcardsandbox['url_to_redirect_info'] = $creditcardsandbox_info;
        
        return $creditcardsandbox;
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
        $data['creditcardsandbox_url_to_redirect'] = $additional_values_validation->validated("input.creditcardsandbox_url_to_redirect");
        
        return $data;
    }
}
