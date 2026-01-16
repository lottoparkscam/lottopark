<?php

use Fuel\Core\Validation;

/**
 * Validator for PSPGATE user form on payment page
 */
class Validator_Wordpress_Payments_PspGate extends Validator_Validator
{
    public const NAME_FIELD = 'pspgate.name';
    public const SURNAME_FIELD = 'pspgate.surname';

    /**
     * Build validation object for validator
     * @return Validation
     */
    public function build_validation(): Validation
    {
        // create validation instance
        $validation = Validation::forge("pspgate-user");

        // check user payment input
        $validation->add(self::NAME_FIELD, _("First Name"), [], Validator_Rule::shortName());
        $validation->add(self::SURNAME_FIELD, _("Last Name"), [], Validator_Rule::shortSurname());

        // return validation
        return $validation;
    }
}
