<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Forms_Whitelabel_Payment_VisaNet
 */
final class Forms_Whitelabel_Payment_Bhartipay extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge('bhartipay');
        
        $validation->add('input.bhartipay_pay_id', _('Bhartipay payload id'))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric']);

        $validation->add('input.bhartipay_secret_key', _('Bhartipay secret key (salt)'))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['alpha', 'numeric']);
        
        $validation->add("input.bhartipay_test", _("Test account"))
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
    public function prepare_data_to_show(array $data = null, array $errors = null): array
    {
        $data['bhartipay_pay_id'] = Input::post('input.bhartipay_pay_id') ?? $data['bhartipay_pay_id'] ?? '';
        $data['bhartipay_secret_key'] = Input::post('input.bhartipay_secret_key') ?? $data['bhartipay_secret_key'] ?? '';
        
        $data['bhartipay_test_checked'] = '';
        if (Input::post('input.bhartipay_test') ?? $data['bhartipay_test'] ?? false) {
            $data['bhartipay_test_checked'] = ' checked="checked"';
        }
        
        return $data;
    }

    /**
     *
     * @param Validation|null $additional_values_validation
     * @return array
     */
    public function get_data(?Validation $additional_values_validation): array
    {
        $data = [];
        $data['bhartipay_pay_id'] = $additional_values_validation->validated('input.bhartipay_pay_id');
        $data['bhartipay_secret_key'] = $additional_values_validation->validated('input.bhartipay_secret_key');
        $data['bhartipay_test'] = $additional_values_validation->validated("input.bhartipay_test") == 1 ? 1 : 0;
        
        return $data;
    }
}
