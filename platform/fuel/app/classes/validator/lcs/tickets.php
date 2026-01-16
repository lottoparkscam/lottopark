<?php

use Fuel\Core\Validation;

class Validator_Lcs_Tickets extends Validator_Abstract
{

    const TICKETS_REQUIRED_FIELDS = [
        'token',
        'amount',
        'ip',
        'lines'
    ];

    public function build_validation(): Validation
    {
        if ($this->instance) {
            return $this->instance;
        }

        $this->instance = Fieldset::forge(__CLASS__)->validation();

        foreach (self::TICKETS_REQUIRED_FIELDS as $field) {
            $this->instance->add_field($field, $field, 'required');
        }

        return $this->instance;
    }
}