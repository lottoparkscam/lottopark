<?php

use Fuel\Core\Validation;

class Validator_Whitelabel_Payments_Gcash extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    private const MERCHANT_ID_FIELD = 'input.gcash_merchant_id';
    private const MERCHANT_NAME_FIELD = 'input.gcash_merchant_name';
    private const API_CLIENT_ID_FIELD = 'input.gcash_api_client_id';
    private const API_KEY_SECRET_FIELD = 'input.gcash_api_key_secret';
    private const IS_TEST_FIELD = 'input.gcash_is_test';

    public function build_validation(): Validation
    {
        $validation = Validation::forge('gcash-gateway');

        $validation->add(self::MERCHANT_ID_FIELD, 'Merchant ID')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_string', ['numeric'])
            ->add_rule('is_numeric')
            ->add_rule('max_length', 10);

        $validation->add(self::MERCHANT_NAME_FIELD, 'Merchant Name')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('max_length', 50);

        $validation->add(self::API_CLIENT_ID_FIELD, 'API Client ID')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('max_length', 36);

        $validation->add(self::API_KEY_SECRET_FIELD, 'API Key Secret')
            ->add_rule('trim')
            ->add_rule('required')
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
        $data['merchant_id'] = $additional_values_validation->validated(self::MERCHANT_ID_FIELD);
        $data['merchant_name'] = $additional_values_validation->validated(self::MERCHANT_NAME_FIELD);
        $data['api_client_id'] = $additional_values_validation->validated(self::API_CLIENT_ID_FIELD);
        $data['api_key_secret'] = $additional_values_validation->validated(self::API_KEY_SECRET_FIELD);
        $data['is_test'] = $additional_values_validation->validated(self::IS_TEST_FIELD);

        return $data;
    }
}
