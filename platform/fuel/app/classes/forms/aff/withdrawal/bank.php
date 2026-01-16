<?php

use Fuel\Core\Validation;

/**
 * @deprecated
 * Description of Forms_Aff_Withdrawal_Bank
 */
class Forms_Aff_Withdrawal_Bank extends Forms_Aff_Withdrawal_Method
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

        $validation->add("input.bname", _("First name"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{P} ]+$/u');

        $validation->add("input.bsurname", _("Last name"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{P} ]+$/u');

        $validation->add("input.account_no", _("Account IBAN number"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{N} ]+$/u');

        $validation->add("input.account_swift", _("SWIFT"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{N}]+$/u');

        $validation->add("input.bank_name", _("Bank name"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{N}\p{P} ]+$/u');

        return $validation;
    }
    
    /**
     *
     * @return array
     */
    public function get_fields(): array
    {
        $fields = [
            'bname',
            'bsurname',
            'account_no',
            'account_swift',
            'bank_name'
        ];
        
        return $fields;
    }
}
