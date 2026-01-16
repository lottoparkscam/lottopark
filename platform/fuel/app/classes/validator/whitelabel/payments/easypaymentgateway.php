<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 13.03.2019
 * Time: 16:26
 */

use Fuel\Core\Validation;

/**
 * Validator for Easy Payment Gateway method addition.
 */
class Validator_Whitelabel_Payments_Easypaymentgateway extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    
    /**
     * Build validation object for validator
     * @return Validation
     */
    public function build_validation(): Validation
    {
        // create validation instance
        $validation = Validation::forge("easy-payment-gateway");

        // check all additional data inputs for EPG: merchant id, product id, merchant password, merchant key, is_test
        $validation->add("input.easy_payment_gateway_merchant_id", _("Merchant ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        $validation->add("input.easy_payment_gateway_product_id", _("Product ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 20)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        $validation->add("input.easy_payment_gateway_merchant_password", _("Merchant Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ['alpha', 'numeric'])
            ->add_rule("exact_length", 32); // AES encryption requirement

        $validation->add("input.easy_payment_gateway_top_logo_url", _("Top Logo Url"))
            ->add_rule("trim")
            ->add_rule("valid_url")
            ->add_rule("max_length", 1024);

        $validation->add("input.easy_payment_gateway_subtitle", _("Subtitle"))
            ->add_rule("trim")
            ->add_rule("valid_string", ['alpha', 'numeric', 'spaces'])
            ->add_rule("max_length", 255);

        $validation->add("input.easy_payment_gateway_payment_solution", _("Payment solution"))
            ->add_rule("trim")
            ->add_rule("valid_string", ['alpha', 'numeric', 'spaces'])
            ->add_rule("max_length", 128);

        $validation->add("input.easy_payment_gateway_is_test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

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
        $data['merchant_id'] = $additional_values_validation->validated('input.easy_payment_gateway_merchant_id');
        $data['product_id'] = $additional_values_validation->validated('input.easy_payment_gateway_product_id');
        $data['merchant_password'] = $additional_values_validation->validated('input.easy_payment_gateway_merchant_password');
        $data['top_logo_url'] = $additional_values_validation->validated('input.easy_payment_gateway_top_logo_url');
        $data['subtitle'] = $additional_values_validation->validated('input.easy_payment_gateway_subtitle');
        $data['payment_solution'] = $additional_values_validation->validated('input.easy_payment_gateway_payment_solution');
        $data['is_test'] = $additional_values_validation->validated('input.easy_payment_gateway_is_test');
                
        return $data;
    }
}
