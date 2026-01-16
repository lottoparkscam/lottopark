<?php

use Fuel\Core\Validation;

/**
 * @deprecated
 * Description of Forms_Aff_Withdrawal_Btc
 */
class Forms_Aff_Withdrawal_Btc extends Forms_Aff_Withdrawal_Method
{
    /**
     *
     * @param string $fieldset_name
     */
    public function __construct(string $fieldset_name)
    {
        parent::__construct($fieldset_name);
    }
    
    /**
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge($this->fieldset_name);

        $validation->add("input.btname", _("First name (optional)"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{P} ]+$/u');

        $validation->add("input.btsurname", _("Last name (optional)"))
            ->add_rule('trim')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{P} ]+$/u');

        $validation->add("input.bitcoin", _("Bitcoin wallet address"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 26)
            ->add_rule('max_length', 62)
            ->add_rule('match_pattern', '/^[\p{L}\p{N}]+$/u');

        return $validation;
    }
    
    /**
     *
     * @return array
     */
    public function get_fields(): array
    {
        $fields = [
            'btname',
            'btsurname',
            'bitcoin'
        ];
        
        return $fields;
    }
}
