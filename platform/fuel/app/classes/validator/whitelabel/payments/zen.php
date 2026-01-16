<?php

use Fuel\Core\Validation;

class Validator_Whitelabel_Payments_Zen extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    private const TERMINAL_UUID_FIELD = 'input.zen_terminal_uuid';
    private const PAYWALL_SECRET_FIELD = 'input.zen_paywall_secret';
    private const MERCHANT_IPN_SECRET_FIELD = 'input.zen_merchant_ipn_secret';
    private const CASINO_TERMINAL_UUID_FIELD = 'input.zen_casino_terminal_uuid';
    private const CASINO_PAYWALL_SECRET_FIELD = 'input.zen_casino_paywall_secret';
    private const CASINO_MERCHANT_IPN_SECRET_FIELD = 'input.zen_casino_merchant_ipn_secret';
    private const IS_TEST_FIELD = 'input.zen_is_test';

    public function build_validation(): Validation
    {
        $validation = Validation::forge('zen-gateway');

        $validation->add(self::TERMINAL_UUID_FIELD, 'Terminal UUID')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('max_length', 36);

        $validation->add(self::PAYWALL_SECRET_FIELD, 'Paywall secret')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('max_length', 32);

        $validation->add(self::MERCHANT_IPN_SECRET_FIELD, 'Merchant IPN secret')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('max_length', 36);

        $validation->add(self::CASINO_TERMINAL_UUID_FIELD, 'Casino terminal UUID')
            ->add_rule('trim')
            ->add_rule('max_length', 36);

        $validation->add(self::CASINO_PAYWALL_SECRET_FIELD, 'Casino paywall secret')
            ->add_rule('trim')
            ->add_rule('max_length', 32);

        $validation->add(self::CASINO_MERCHANT_IPN_SECRET_FIELD, 'Casino merchant IPN secret')
            ->add_rule('trim')
            ->add_rule('max_length', 36);

        $validation->add(self::IS_TEST_FIELD, 'Test account')
            ->add_rule('trim')
            ->add_rule('match_value', 1);

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
        $data['terminal_uuid'] = $additional_values_validation->validated(self::TERMINAL_UUID_FIELD);
        $data['paywall_secret'] = $additional_values_validation->validated(self::PAYWALL_SECRET_FIELD);
        $data['merchant_ipn_secret'] = $additional_values_validation->validated(self::MERCHANT_IPN_SECRET_FIELD);
        $data['casino_terminal_uuid'] = $additional_values_validation->validated(self::CASINO_TERMINAL_UUID_FIELD);
        $data['casino_paywall_secret'] = $additional_values_validation->validated(self::CASINO_PAYWALL_SECRET_FIELD);
        $data['casino_merchant_ipn_secret'] = $additional_values_validation->validated(self::CASINO_MERCHANT_IPN_SECRET_FIELD);
        $data['is_test'] = $additional_values_validation->validated(self::IS_TEST_FIELD);

        return $data;
    }
}
