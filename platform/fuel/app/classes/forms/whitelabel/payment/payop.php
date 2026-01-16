<?php

use Fuel\Core\Validation;

/** @deprecated use presenter instead */
final class Forms_Whitelabel_Payment_PayOp extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge('payop');

        $validation->add('input.payop_public_key', _('API Public Key'))
            ->add_rule('required')
            ->add_rule('trim')
            ->add_rule('min_length', 45) // 48 +- 3 chars
            ->add_rule('max_length', 51) // 48 +- 3 chars
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add('input.payop_secret_key', _('API Secret Key'))
            ->add_rule('required')
            ->add_rule('trim')
            ->add_rule('min_length', 21) // 24 +- 3 chars
            ->add_rule('max_length', 27) // 24 +- 3 chars
            ->add_rule('valid_string', ['alpha', 'numeric', 'dashes']);

        $validation->add('input.payop_is_test', _('Test account'))
            ->add_rule('trim')
            ->add_rule('match_value', 1);

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
        $payop = [];

        $publickey_error_class = '';
        if (isset($errors['input.payop_public_key'])) {
            $publickey_error_class = ' has-error';
        }
        $payop['publickey_error_class'] = $publickey_error_class;
        
        $publickey_value_t = '';
        if (null !== Input::post("input.payop_public_key")) {
            $publickey_value_t = Input::post("input.payop_public_key");
        } elseif (isset($data['payop_public_key'])) {
            $publickey_value_t = $data['payop_public_key'];
        }
        $payop['publickey_value'] = Security::htmlentities($publickey_value_t);

        $secretkey_error_class = '';
        if (isset($errors['input.payop_secret_key'])) {
            $secretkey_error_class = ' has-error';
        }
        $payop['secretkey_error_class'] = $secretkey_error_class;

        $secretkey_value_t = '';
        if (null !== Input::post("input.payop_secret_key")) {
            $secretkey_value_t = Input::post("input.payop_secret_key");
        } elseif (isset($data['payop_secret_key'])) {
            $secretkey_value_t = $data['payop_secret_key'];
        }
        $payop['secretkey_value'] = Security::htmlentities($secretkey_value_t);

        $payop_test_checked = '';
        if ((null !== Input::post("input.payop_is_test") &&
                (int)Input::post("input.payop_is_test") === 1) ||
            (isset($data['is_test']) &&
                (int)$data['is_test'] === 1)
        ) {
            $payop_test_checked = ' checked="checked"';
        }
        $payop['test_checked'] = $payop_test_checked;

        return $payop;
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
        $data['payop_public_key'] = $additional_values_validation->validated("input.payop_public_key");
        $data['payop_secret_key'] = $additional_values_validation->validated("input.payop_secret_key");
        $data['is_test'] = $additional_values_validation->validated("input.payop_is_test") == 1 ? 1 : 0;
        
        return $data;
    }
}
