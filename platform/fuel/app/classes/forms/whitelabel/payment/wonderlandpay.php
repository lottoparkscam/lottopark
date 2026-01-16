<?php

use Fuel\Core\Validation;

/** @deprecated use presenter instead */
final class Forms_Whitelabel_Payment_WonderlandPay extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;

    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("wonderlandpay");

        $validation->add("input.wonderlandpay_merchant_number", _("API Merchant Number"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 5);

        $validation->add("input.wonderlandpay_gateway_number", _("API Gateway Number"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 8);

        $validation->add("input.wonderlandpay_secret_key", _("API Secret Key"))
            ->add_rule('required')
            ->add_rule("trim")
            ->add_rule("valid_string", ["alpha", "numeric", "dashes"]);

        $validation->add("input.wonderlandpay_is_test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }

    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array {
        $wonderlandpay = [];

        $merchantnumber_error_class = '';
        if (isset($errors['input.wonderlandpay_merchant_number'])) {
            $merchantnumber_error_class = ' has-error';
        }
        $wonderlandpay['merchantnumber_error_class'] = $merchantnumber_error_class;

        $merchantnumber_value_t = '';
        if (null !== Input::post("input.wonderlandpay_merchant_number")) {
            $merchantnumber_value_t = Input::post("input.wonderlandpay_merchant_number");
        } elseif (isset($data['wonderlandpay_merchant_number'])) {
            $merchantnumber_value_t = $data['wonderlandpay_merchant_number'];
        }
        $wonderlandpay['merchantnumber_value'] = Security::htmlentities($merchantnumber_value_t);

        $gatewaynumber_error_class = '';
        if (isset($errors['input.wonderlandpay_gateway_number'])) {
            $gatewaynumber_error_class = ' has-error';
        }
        $wonderlandpay['gatewaynumber_error_class'] = $gatewaynumber_error_class;

        $gatewaynumber_value_t = '';
        if (null !== Input::post("input.wonderlandpay_gateway_number")) {
            $gatewaynumber_value_t = Input::post("input.wonderlandpay_gateway_number");
        } elseif (isset($data['wonderlandpay_gateway_number'])) {
            $gatewaynumber_value_t = $data['wonderlandpay_gateway_number'];
        }
        $wonderlandpay['gatewaynumber_value'] = Security::htmlentities($gatewaynumber_value_t);

        $secretkey_error_class = '';
        if (isset($errors['input.wonderlandpay_secret_key'])) {
            $secretkey_error_class = ' has-error';
        }
        $wonderlandpay['secretkey_error_class'] = $secretkey_error_class;

        $secretkey_value_t = '';
        if (null !== Input::post("input.wonderlandpay_secret_key")) {
            $secretkey_value_t = Input::post("input.wonderlandpay_secret_key");
        } elseif (isset($data['wonderlandpay_secret_key'])) {
            $secretkey_value_t = $data['wonderlandpay_secret_key'];
        }
        $wonderlandpay['secretkey_value'] = Security::htmlentities($secretkey_value_t);

        $wonderlandpay_test_checked = '';
        if ((null !== Input::post("input.wonderlandpay_is_test") &&
                (int)Input::post("input.wonderlandpay_is_test") === 1) ||
            (isset($data['is_test']) &&
                (int)$data['is_test'] === 1)
        ) {
            $wonderlandpay_test_checked = ' checked="checked"';
        }
        $wonderlandpay['test_checked'] = $wonderlandpay_test_checked;

        return $wonderlandpay;
    }

    public function get_data(
        ?Validation $additional_values_validation
    ): array {
        $data = [];
        $data['wonderlandpay_merchant_number'] = $additional_values_validation->validated("input.wonderlandpay_merchant_number");
        $data['wonderlandpay_gateway_number'] = $additional_values_validation->validated("input.wonderlandpay_gateway_number");
        $data['wonderlandpay_secret_key'] = $additional_values_validation->validated("input.wonderlandpay_secret_key");
        $data['is_test'] = $additional_values_validation->validated("input.wonderlandpay_is_test") == 1 ? 1 : 0;

        return $data;
    }
}
