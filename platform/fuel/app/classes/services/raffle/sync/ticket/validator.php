<?php

use Fuel\Core\Validation;

class Services_Raffle_Sync_Ticket_Validator extends Validator_Abstract
{
    public const UPDATE_REQUIRED_FIELDS = [
        'status',
        'amount',
        'prize',
        'draw_date',
        'lottery_ticket_lines'
    ];

    public function build_validation(): Validation
    {
        if ($this->instance) {
            return $this->instance;
        }

        $this->instance = Validation::forge(__CLASS__);

        foreach (self::UPDATE_REQUIRED_FIELDS as $field) {
            $this->instance->add_field($field, $field, 'required');
        }

        return $this->instance;
    }
}
