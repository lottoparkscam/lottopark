<?php

use Fuel\Core\Validation;

class Validator_Lcs_Draw extends Validator_Abstract
{

    const DRAW_DATA_REQUIRED_FIELDS = [
        'next_draw_date',
        'next_next_draw_date',
        'timezone',
        'jackpot',
        'currency_code'
    ];

    public function build_validation(): Validation
    {
        if ($this->instance) {
            return $this->instance;
        }

        $this->instance = Fieldset::forge(__CLASS__)->validation();

        foreach (self::DRAW_DATA_REQUIRED_FIELDS as $field) {
            $this->instance->add_field($field, $field, 'required');
        }

        return $this->instance;
    }
}