<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 13.03.2019
 * Time: 16:26
 */

use Fuel\Core\Validation;

/**
 * Validator for AstroPay method addition/edit.
 */
class Validator_Whitelabel_Payments_Astropay extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    
    /**
     * Build validation object for validator
     * @return Validation
     */
    public function build_validation(): Validation
    {
        // create validation instance
        $validation = Validation::forge('astro-pay');

        // check all additional data inputs for EPG: merchant id, product id, merchant password, merchant key, is_test
        $validation->add('input.astro_pay_login', _('Login'))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule("valid_string", ['alpha', 'numeric'])
            ->add_rule("min_length", 3) // TODO: {Vordis 2019-05-29 11:58:43} don't know exact dimensions, these should be safe
            ->add_rule('max_length', 64);

        $validation->add("input.astro_pay_password", _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ['alpha', 'numeric'])
            ->add_rule("min_length", 3) // TODO: {Vordis 2019-05-29 11:58:43} don't know exact dimensions, these should be safe
            ->add_rule('max_length', 64);

        $validation->add("input.astro_pay_secret_key", _("Secret key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ['alpha', 'numeric'])
            ->add_rule("min_length", 3) // TODO: {Vordis 2019-05-29 11:58:43} don't know exact dimensions, these should be safe
            ->add_rule('max_length', 64);

        $validation->add('input.astro_pay_is_test', _('Test account'))
            ->add_rule('trim')
            ->add_rule('match_value', 1);

        // return validation
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
        return [];
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
        $data['login'] = $additional_values_validation->validated("input.astro_pay_login");
        $data['password'] = $additional_values_validation->validated("input.astro_pay_password");
        $data['secret_key'] = $additional_values_validation->validated("input.astro_pay_secret_key");
        $data['is_test'] = $additional_values_validation->validated('input.astro_pay_is_test');
        
        return $data;
    }
}
