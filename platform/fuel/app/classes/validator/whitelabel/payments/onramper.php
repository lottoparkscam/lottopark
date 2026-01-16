<?php

use Fuel\Core\Validation;

class Validator_Whitelabel_Payments_Onramper extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    private const API_KEY_FIELD = 'input.onramper_api_key';
    private const API_KEY_SECRET_FIELD = 'input.onramper_api_key_secret';

    public function build_validation(): Validation
    {
        $validation = Validation::forge("onramper-gateway");

        $validation->add(self::API_KEY_FIELD, _("API Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 52);

        $validation->add(self::API_KEY_SECRET_FIELD, _("API Key Secret"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 43);

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
        $data['api_key_secret'] = $additional_values_validation->validated(self::API_KEY_SECRET_FIELD);

        return $data;
    }
}
