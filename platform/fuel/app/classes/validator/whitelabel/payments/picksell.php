<?php

use Fuel\Core\Validation;

class Validator_Whitelabel_Payments_Picksell extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    public function build_validation(): Validation
    {
        $validation = Validation::forge("picksell-gateway");

        $validation->add("input.picksell_merchant_id", _("Merchant ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["numeric"])
            ->add_rule("max_length", 10)
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1);

        $validation->add("input.picksell_api_key_token", _("API key token"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 40);

        $validation->add("input.picksell_api_key_secret", _("API key secret"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 64);

        $validation->add("input.picksell_is_test", _("Test account"))
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
        $data['merchant_id'] = $additional_values_validation->validated('input.picksell_merchant_id');
        $data['api_key_token'] = $additional_values_validation->validated('input.picksell_api_key_token');
        $data['api_key_secret'] = $additional_values_validation->validated('input.picksell_api_key_secret');
        $data['is_test'] = $additional_values_validation->validated('input.picksell_is_test');

        return $data;
    }
}
