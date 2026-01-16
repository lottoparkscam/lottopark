<?php

use Fuel\Core\Validation;

class Validator_Lcs_Draws extends Validator_Abstract
{
    public const DRAW_REQUIRED_FIELDS = [
        'draw_no',
        'date',
        'numbers',
        'is_calculated',
        'sale_sum',
        'prize_total',
        'jackpot', # for tier
        'lines_won_count',
        'tickets_count',
        'currency_code',
        'lottery_prizes'
    ];

    public function build_validation(): Validation
    {
        if ($this->instance) {
            return $this->instance;
        }

        $this->instance = Fieldset::forge(__CLASS__)->validation();

        foreach (self::DRAW_REQUIRED_FIELDS as $field) {
            $this->instance->add_field($field, $field, 'required');
        }

        return $this->instance;
    }
}
