<?php

use Core\App;
use Fuel\Core\Validation;

class Validator_Whitelabel_Payments_Lenco extends Validator_Validator implements Forms_Whitelabel_Payment_ShowData
{
    private const API_KEY_FIELD = 'input.lenco_api_pub_key';
    private const API_KEY_SECRET_FIELD = 'input.lenco_api_key_secret';
    private const IS_TEST_FIELD = 'input.lenco_is_test';

    private App $app;

    public function __construct()
    {
        $this->app = Container::get(App::class);
    }

    public function build_validation(): Validation
    {
        $validation = Validation::forge('lenco-gateway');

        $validation->add(self::API_KEY_FIELD, 'API Public Key')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('max_length', 52);

        $validation->add(self::API_KEY_SECRET_FIELD, 'API Key Secret')
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('max_length', 64);

        if ($this->app->isNotProduction()) {
            $validation->add(self::IS_TEST_FIELD, 'Test account')
                ->add_rule('trim')
                ->add_rule('match_value', 1);
        }

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
        $data['api_pub_key'] = $additional_values_validation->validated(self::API_KEY_FIELD);
        $data['api_key_secret'] = $additional_values_validation->validated(self::API_KEY_SECRET_FIELD);

        if ($this->app->isNotProduction()) {
            $data['is_test'] = $additional_values_validation->validated(self::IS_TEST_FIELD);
        }

        return $data;
    }
}
