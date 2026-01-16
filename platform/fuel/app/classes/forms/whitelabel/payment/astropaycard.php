<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_Astropaycard
 */
final class Forms_Whitelabel_Payment_Astropaycard extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("astropaycard");
        
        $validation->add("input.astropaycard_x_login", _("AstroPay Card x_login"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 32);

        $validation->add("input.astropaycard_x_trans_key", _("AstroPay Card x_trans_key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 32);
        
        $validation->add("input.astropaycard_secret_key", _("AstroPay Card Secret Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"])
            ->add_rule("max_length", 32);
        
        $validation->add("input.astropaycard_test", _("Test account"))
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
        $astropaycard = [];
        
        $astropaycard_x_login_error_class = "";
        if (isset($errors['input.astropaycard_x_login'])) {
            $astropaycard_x_login_error_class = ' has-error';
        }
        $astropaycard['x_login_error_class'] = $astropaycard_x_login_error_class;

        $astropaycard_x_login_value_t = "";
        if (Input::post("input.astropaycard_x_login") !== null) {
            $astropaycard_x_login_value_t = Input::post("input.astropaycard_x_login");
        } elseif (isset($data['astropaycard_x_login'])) {
            $astropaycard_x_login_value_t = $data['astropaycard_x_login'];
        }
        $astropaycard['x_login_value'] = Security::htmlentities($astropaycard_x_login_value_t);
        
        $astropaycard_x_trans_key_error_class = "";
        if (isset($errors['input.astropaycard_x_trans_key'])) {
            $astropaycard_x_trans_key_error_class = ' has-error';
        }
        $astropaycard['x_trans_key_error_class'] = $astropaycard_x_trans_key_error_class;
        
        $astropaycard_x_trans_key_value_t = "";
        if (Input::post("input.astropaycard_x_trans_key") !== null) {
            $astropaycard_x_trans_key_value_t = Input::post("input.astropaycard_x_trans_key");
        } elseif (isset($data['astropaycard_x_trans_key'])) {
            $astropaycard_x_trans_key_value_t = $data['astropaycard_x_trans_key'];
        }
        $astropaycard['x_trans_key_value'] = Security::htmlentities($astropaycard_x_trans_key_value_t);
        
        $astropaycard_secret_key_error_class = "";
        if (isset($errors['input.astropaycard_secret_key'])) {
            $astropaycard_secret_key_error_class = ' has-error';
        }
        $astropaycard['secret_key_error_class'] = $astropaycard_secret_key_error_class;
        
        $astropaycard_secret_key_value_t = "";
        if (Input::post("input.astropaycard_secret_key") !== null) {
            $astropaycard_secret_key_value_t = Input::post("input.astropaycard_secret_key");
        } elseif (isset($data['astropaycard_secret_key'])) {
            $astropaycard_secret_key_value_t = $data['astropaycard_secret_key'];
        }
        $astropaycard['secret_key_value'] = Security::htmlentities($astropaycard_secret_key_value_t);
        
        $astropaycard_test_checked = '';
        if ((null !== Input::post("input.astropaycard_test") &&
                (int)Input::post("input.astropaycard_test") === 1) ||
            (isset($data['astropaycard_test']) &&
                (int)$data['astropaycard_test'] === 1)
        ) {
            $astropaycard_test_checked = ' checked="checked"';
        }
        $astropaycard['test_checked'] = $astropaycard_test_checked;
        
        return $astropaycard;
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
        $data['astropaycard_x_login'] = $additional_values_validation->validated("input.astropaycard_x_login");
        $data['astropaycard_x_trans_key'] = $additional_values_validation->validated("input.astropaycard_x_trans_key");
        $data['astropaycard_secret_key'] = $additional_values_validation->validated("input.astropaycard_secret_key");
        $data['astropaycard_test'] = $additional_values_validation->validated("input.astropaycard_test") == 1 ? 1 : 0;
                
        return $data;
    }
}
