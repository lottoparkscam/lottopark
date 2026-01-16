<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Skrill form
 */
final class Forms_Whitelabel_Payment_Skrill extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("skrill");

        $validation->add("input.merchantemail", _("Merchant E-mail (required)"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('valid_email');

        $validation->add("input.merchantlogourl", _("Merchant Logo URL"))
            ->add_rule("trim")
            ->add_rule("max_length", 240)
            ->add_rule('valid_url');

        $validation->add("input.merchantdescription", _("Merchant Description"))
            ->add_rule("trim")
            ->add_rule("max_length", 30)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{Nd}\p{P} ]+$/u');

        $validation->add("input.secretword", _("Secret Word (required)"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 10);

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
        $skrill = [];
        
        $merchantemail_error_class = '';
        if (isset($errors['input.merchantemail'])) {
            $merchantemail_error_class = ' has-error';
        }
        $skrill['merchantemail_error_class'] = $merchantemail_error_class;
        
        $merchantemail_value_t = '';
        if (null !== Input::post("input.merchantemail")) {
            $merchantemail_value_t = Input::post("input.merchantemail");
        } elseif (isset($data['merchant_email'])) {
            $merchantemail_value_t = $data['merchant_email'];
        }
        $skrill['merchantemail_value'] = Security::htmlentities($merchantemail_value_t);

        $secretword_error_class = '';
        if (isset($errors['input.secretword'])) {
            $secretword_error_class = ' has-error';
        }
        $skrill['secretword_error_class'] = $secretword_error_class;
        
        $secretword_value_t = '';
        if (null !== Input::post("input.secretword")) {
            $secretword_value_t = Input::post("input.secretword");
        } elseif (isset($data['secret_word'])) {
            $secretword_value_t = $data['secret_word'];
        }
        $skrill['secretword_value'] = Security::htmlentities($secretword_value_t);

        $skrill['secretword_info'] = _(
            "Can be set in <strong>Settings &gt; Developer Settings" .
            "</strong> section of your Skrill Digital Wallet account."
        );

        $merchantlogourl_error_class = '';
        if (isset($errors['input.merchantlogourl'])) {
            $merchantlogourl_error_class = ' has-error';
        }
        $skrill['merchantlogourl_error_class'] = $merchantlogourl_error_class;
        
        $merchantlogourl_value_t = '';
        if (null !== Input::post("input.merchantlogourl")) {
            $merchantlogourl_value_t = Input::post("input.merchantlogourl");
        } elseif (isset($data['merchant_logourl'])) {
            $merchantlogourl_value_t = $data['merchant_logourl'];
        }
        $skrill['merchantlogourl_value'] = Security::htmlentities($merchantlogourl_value_t);

        $merchantdescription_error_class = '';
        if (isset($errors['input.merchantdescription'])) {
            $merchantdescription_error_class = ' has-error';
        }
        $skrill['merchantdescription_error_class'] = $merchantdescription_error_class;
        
        $merchantdescription_value_t = '';
        if (null !== Input::post("input.merchantdescription")) {
            $merchantdescription_value_t = Input::post("input.merchantdescription");
        } elseif (isset($data['merchant_description'])) {
            $merchantdescription_value_t = $data['merchant_description'];
        }
        $skrill['merchantdescription_value'] = Security::htmlentities($merchantdescription_value_t);

        return $skrill;
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
        $data['merchant_email'] = $additional_values_validation->validated("input.merchantemail");
        $data['merchant_logourl'] = $additional_values_validation->validated("input.merchantlogourl");
        $data['merchant_description'] = $additional_values_validation->validated("input.merchantdescription");
        $data['secret_word'] = $additional_values_validation->validated("input.secretword");

        return $data;
    }
}
