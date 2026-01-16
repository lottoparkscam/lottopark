<?php

use Fuel\Core\Validation;

class Validator_Lcs_Prizes extends Validator_Abstract
{
    public const PRIZES_REQUIRED_FIELDS = [
        'lines_won_count',
        'total',
        'per_user',
        'currency_code', # mapped to 'currency_id',
    ];

    public function build_validation(): Validation
    {
        if ($this->instance) {
            return $this->instance;
        }

        $this->instance = Fieldset::forge(__CLASS__)->validation();

        foreach (self::PRIZES_REQUIRED_FIELDS as $field) {
            $this->instance->add_field($field, $field, 'required');
        }

        return $this->instance;
    }
}
