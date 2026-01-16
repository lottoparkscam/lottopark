<?php

use Fuel\Core\Validation;

class Validator_Whitelabel_Payments_NowPayments extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    private const API_KEY_FIELD = 'input.nowpayments_api_key';
    private const IPN_SECRET_KEY_FIELD = 'input.nowpayments_ipn_secret_key';
    private const FORCE_PAYMENT_CURRENCY_FIELD = 'input.nowpayments_force_payment_currency';
    private const IS_TEST_FIELD = 'input.nowpayments_is_test';

    public function build_validation(): Validation
    {
        $validation = Validation::forge("nowpayments-gateway");

        $validation->add(self::API_KEY_FIELD, _("API Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 31);

        $validation->add(self::IPN_SECRET_KEY_FIELD, _("IPN Secret Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 32);

        $validation->add(self::FORCE_PAYMENT_CURRENCY_FIELD, _("Force Payment Currency"))
            ->add_rule("trim")
            ->add_rule("max_length", 10);

        $validation->add(self::IS_TEST_FIELD, _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }

    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array {
        return [];
    }

    public function get_data(
        ?Validation $additional_values_validation
    ): array {
        $data = [];
        $data['api_key'] = $additional_values_validation->validated(self::API_KEY_FIELD);
        $data['ipn_secret_key'] = $additional_values_validation->validated(self::IPN_SECRET_KEY_FIELD);
        $data['force_payment_currency'] = $additional_values_validation->validated(self::FORCE_PAYMENT_CURRENCY_FIELD);
        $data['is_test'] = $additional_values_validation->validated(self::IS_TEST_FIELD);

        return $data;
    }
}
