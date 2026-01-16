<?php

use Fuel\Core\Validation;

/**
 * Validator for managers->settings blocked_countries/add in consequence of submitting blocked_countries/new.
 *
 * @author Marcin Klimek <marcin.klimek at gg.international>
 */
class Validator_Whitelabel_Settings_Blocked_Add extends Validator_Validator
{
    // 14.03.2019 11:18 Vordis TODO: fix usage to use trimmed value of country code

    /**
     * Collection of country codes to be matched against input.
     * @var array
     */
    private $country_codes;

    /**
     * Create new instance of validator.
     * @param array $country_codes Collection of country codes to be matched against input.
     */
    public function __construct(array $country_codes)
    {
        $this->country_codes = $country_codes;
    }

    /**
     * Build validation object for validator
     * @return Validation
     */
    public function build_validation(): Validation
    {
        // create validation instance
        $validation = Validation::forge();

        // add country code and it's rules
        $validation->add("input.code", _("Country"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('match_collection', $this->country_codes);

        // return validation
        return $validation;
    }

}
