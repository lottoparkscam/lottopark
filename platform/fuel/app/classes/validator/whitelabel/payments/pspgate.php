<?php

use Fuel\Core\Validation;

class Validator_Whitelabel_Payments_PspGate extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    private const CLIENT_ID_FIELD = 'input.pspgate_client_id';
    private const CLIENT_SECRET_FIELD = 'input.pspgate_client_secret';
    private const USERNAME_FIELD = 'input.pspgate_username';
    private const PASSWORD_FIELD = 'input.pspgate_password';
    private const API_PASSWORD_FIELD = 'input.pspgate_api_password';
    private const IS_TEST_FIELD = 'input.pspgate_is_test';

    public function build_validation(): Validation
    {
        $validation = Validation::forge("pspgate-gateway");

        $validation->add(self::CLIENT_ID_FIELD, _("Client ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        $validation->add(self::CLIENT_SECRET_FIELD, _("Client Secret"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 40);

        $validation->add(self::USERNAME_FIELD, _("Username"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 11);

        $validation->add(self::PASSWORD_FIELD, _("Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 11);

        $validation->add(self::API_PASSWORD_FIELD, _("API Password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 11);

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
        $data['client_id'] = $additional_values_validation->validated(self::CLIENT_ID_FIELD);
        $data['client_secret'] = $additional_values_validation->validated(self::CLIENT_SECRET_FIELD);
        $data['username'] = $additional_values_validation->validated(self::USERNAME_FIELD);
        $data['password'] = $additional_values_validation->validated(self::PASSWORD_FIELD);
        $data['api_password'] = $additional_values_validation->validated(self::API_PASSWORD_FIELD);
        $data['is_test'] = $additional_values_validation->validated(self::IS_TEST_FIELD);

        return $data;
    }
}
