<?php

use Fuel\Core\Validation;

/**
 * Validator for ZEN user form on payment page
 */
class Validator_Wordpress_Payments_Zen extends Validator_Validator
{
    public const NAME_FIELD = 'zen.name';
    public const SURNAME_FIELD = 'zen.surname';

    /**
     * Build validation object for validator
     * @return Validation
     */
    public function build_validation(): Validation
    {
        // create validation instance
        $validation = Validation::forge("zen-user");

        // check user payment input
        $validation->add(self::NAME_FIELD, _("First Name"), [], self::nameRules());

        $validation->add(self::SURNAME_FIELD, _("Last Name"), [], self::nameRules());

        // return validation
        return $validation;
    }

    /**
     * Custom FirstName LastName rules to allow other alphabets
     * e.g. Thai, Bengali, Chinese, Japan etc.
     */
    public static function nameRules(): array
    {
        $pattern[] = '[:alpha:]'; // specials
        $pattern[] = '_\-'; // dashes
        $pattern[] = ' '; // spaces
        $pattern[] = "'"; // singlequotes

        $pattern[] = '\p{Thai}';
        $pattern[] = '\p{Bengali}';

        return [
            'trim',
            'stripslashes',
            'required',
            ['min_length', 1],
            ['max_length', 60],
            ['match_pattern', '/^([' . implode('', $pattern) . '])+$/u'],
        ];
    }
}
