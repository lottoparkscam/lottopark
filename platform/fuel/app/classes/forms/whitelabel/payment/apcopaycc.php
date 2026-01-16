<?php

use Fuel\Core\Validation;

/**
 * Class for preparing ApcoPay CC form
 */
final class Forms_Whitelabel_Payment_Apcopaycc extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @var array
     */
    private $errors = [];
    
    /**
     *
     * @return Validation
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("apcopaycc");

        $validation->add("input.apcopaycc_profileid", _("Profile ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 45);

        $validation->add("input.apcopaycc_secretword", _("Secret Word (Hash)"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 45);

        $validation->add("input.apcopaycc_merchantcode", _("Merchant Code"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        $validation->add("input.apcopaycc_password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 45);

        $validation->add("input.apcopaycc_3dsecure", _("3D Secure"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.apcopaycc_bypass3ds", _("Bypass 3Ds"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.apcopaycc_only3ds", _("Only 3Ds"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }

    /**
     *
     * @return Validation
     */
    public function get_prepared_user_form(): Validation
    {
        $validation = Validation::forge("apcopaycc_user");

        $validation->add("apcopaycc.name", _("Name on card"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 40)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validation->add("apcopaycc.address_1", _("Address"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);

        $validation->add("apcopaycc.address_2", _("Address (optional additional information)"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('valid_string', ['alpha', 'specials', 'numeric', 'dashes', 'spaces', 'commas', 'dots', 'forwardslashes', 'utf8']);

        $validation->add("apcopaycc.post-code", _("Postal/ZIP Code"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('max_length', 20)
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes', 'spaces']);

        $validation->add("apcopaycc.city", _("City"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 40)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        $validation->add("apcopaycc.country", _("Country"))
            ->add_rule('trim')
            ->add_rule('stripslashes')
            ->add_rule("required")
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 20)
            ->add_rule('valid_string', ['alpha', 'specials', 'dashes', 'spaces', 'utf8']);

        if (!$validation->run()) {
            $this->errors = Lotto_Helper::generate_errors($validation->error());
        }

        return $validation;
    }

    /**
     *
     * @return array
     */
    public function get_errors(): array
    {
        return $this->errors;
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
        $apcopaycc = [];
        
        $profileid_error_class = '';
        if (isset($errors['input.apcopaycc_profileid'])) {
            $profileid_error_class = ' has-error';
        }
        $apcopaycc['profileid_error_class'] = $profileid_error_class;
        
        $profileid_value_t = '';
        if (null !== Input::post("input.apcopaycc_profileid")) {
            $profileid_value_t = Input::post("input.apcopaycc_profileid");
        } elseif (isset($data['profile_id'])) {
            $profileid_value_t = $data['profile_id'];
        }
        $apcopaycc['profileid_value'] = Security::htmlentities($profileid_value_t);

        $secretword_error_class = '';
        if (isset($errors['input.apcopaycc_secretword'])) {
            $secretword_error_class = ' has-error';
        }
        $apcopaycc['secretword_error_class'] = $secretword_error_class;
        
        $secretword_value_t = '';
        if (null !== Input::post("input.apcopaycc_secretword")) {
            $secretword_value_t = Input::post("input.apcopaycc_secretword");
        } elseif (isset($data['secret_word'])) {
            $secretword_value_t = $data['secret_word'];
        }
        $apcopaycc['secretword_value'] = Security::htmlentities($secretword_value_t);

        $merchantcode_error_class = '';
        if (isset($errors['input.apcopaycc_merchantcode'])) {
            $merchantcode_error_class = ' has-error';
        }
        $apcopaycc['merchantcode_error_class'] = $merchantcode_error_class;
            
        $merchantcode_value_t = '';
        if (null !== Input::post("input.apcopaycc_merchantcode")) {
            $merchantcode_value_t = Input::post("input.apcopaycc_merchantcode");
        } elseif (isset($data['merchant_code'])) {
            $merchantcode_value_t = $data['merchant_code'];
        }
        $apcopaycc['merchantcode_value'] = Security::htmlentities($merchantcode_value_t);

        $password_error_class = '';
        if (isset($errors['input.apcopaycc_password'])) {
            $password_error_class = ' has-error';
        }
        $apcopaycc['password_error_class'] = $password_error_class;
            
        $password_value_t = '';
        if (null !== Input::post("input.apcopaycc_password")) {
            $password_value_t = Input::post("input.apcopaycc_password");
        } elseif (isset($data['password'])) {
            $password_value_t = $data['password'];
        }
        $apcopaycc['password_value'] = Security::htmlentities($password_value_t);

        $checked_3dsecure = '';
        if ((null !== Input::post("input.apcopaycc_3dsecure") &&
                Input::post("input.apcopaycc_3dsecure") == 1) ||
            (isset($data['3d_secure']) &&
                $data['3d_secure'] == 1)
        ) {
            $checked_3dsecure = ' checked="checked"';
        }
        $apcopaycc['checked_3dsecure'] = $checked_3dsecure;
            
        $checked_bypass3ds = '';
        if ((null !== Input::post("input.apcopaycc_bypass3ds") &&
                Input::post("input.apcopaycc_bypass3ds") == 1) ||
            (isset($data['bypass_3ds']) &&
                $data['bypass_3ds'] == 1)
        ) {
            $checked_bypass3ds = ' checked="checked"';
        }
        $apcopaycc['checked_bypass3ds'] = $checked_bypass3ds;
            
        $checked_only3ds = '';
        if ((null !== Input::post("input.apcopaycc_only3ds") &&
                Input::post("input.apcopaycc_only3ds") == 1) ||
            (isset($data['only_3ds']) &&
                $data['only_3ds'] == 1)
        ) {
            $checked_only3ds = ' checked="checked"';
        }
        $apcopaycc['checked_only3ds'] = $checked_only3ds;
        
        return $apcopaycc;
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
        $data['profile_id'] = $additional_values_validation->validated("input.apcopaycc_profileid");
        $data['secret_word'] = $additional_values_validation->validated("input.apcopaycc_secretword");
        $data['merchant_code'] = $additional_values_validation->validated("input.apcopaycc_merchantcode");
        $data['password'] = $additional_values_validation->validated("input.apcopaycc_password");
        $data['3d_secure'] = $additional_values_validation->validated("input.apcopaycc_3dsecure") == 1 ? 1 : 0;
        $data['bypass_3ds'] = $additional_values_validation->validated("input.apcopaycc_bypass3ds") == 1 ? 1 : 0;
        $data['only_3ds'] = $additional_values_validation->validated("input.apcopaycc_only3ds") == 1 ? 1 : 0;
                
        return $data;
    }
}
