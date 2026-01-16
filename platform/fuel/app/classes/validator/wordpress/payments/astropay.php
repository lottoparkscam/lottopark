<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 20.05.2019
 * Time: 16:26
 */

use Fuel\Core\Validation;

/**
 * Validator for AstroPay user payment.
 */
class Validator_Wordpress_Payments_Astropay extends Validator_Validator
{

    /**
     * Build validation object for validator
     * @return Validation
     */
    public function build_validation(): Validation
    {
        // create validation instance
        $validation = Validation::forge("astro-pay-user");

        // check user payment input
        $validation->add("astro-pay.national_id", _("National ID"), [], Validator_Rule::national_id())
            ->add_rule("required");

        $validation->add("astro-pay.bank_code", _("Bank"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "uppercase"])
            ->add_rule("min_length", 1)
            ->add_rule("max_length", 3); // 3 is in astro pay documentation

        $validation->add("astro-pay.name", _("Name"), [], Validator_Rule::name())
            ->add_rule("required");

        $validation->add("astro-pay.surname", _("Surname"), [], Validator_Rule::surname())
            ->add_rule("required");

        // return validation
        return $validation;
    }
}
