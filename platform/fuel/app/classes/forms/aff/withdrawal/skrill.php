<?php

use Fuel\Core\Validation;

/**
 * @deprecated
 * Description of Forms_Aff_Withdrawal_Skrill
 */
class Forms_Aff_Withdrawal_Skrill extends Forms_Aff_Withdrawal_Method
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

        $validation->add("input.sname", _("First name"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{P} ]+$/u');

        $validation->add("input.ssurname", _("Last name"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('min_length', 3)
            ->add_rule('max_length', 100)
            ->add_rule('match_pattern', '/^[\p{L}\p{M}\p{P} ]+$/u');

        $validation->add("input.skrill_email", _("Skrill e-mail"))
            ->add_rule('trim')
            ->add_rule('required')
            ->add_rule('valid_email');

        return $validation;
    }
    
    /**
     *
     * @return array
     */
    public function get_fields(): array
    {
        $fields = [
            'sname',
            'ssurname',
            'skrill_email'
        ];
        
        return $fields;
    }
}
