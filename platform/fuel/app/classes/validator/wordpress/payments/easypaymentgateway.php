<?php
/**
 * @author Marcin Klimek <marcin.klimek at gg.international>
 * Date: 20.05.2019
 * Time: 16:26
 */

use Fuel\Core\Validation;

/**
 * Validator for Easy Payment Gateway user payment.
 */
class Validator_Wordpress_Payments_Easypaymentgateway extends Validator_Validator
{

    /**
     * Build validation object for validator
     * @return Validation
     */
    public function build_validation(): Validation
    {
        // create validation instance
        $validation = Validation::forge("easy-payment-gateway-user");

        // check user payment input
        $validation->add("easy-payment-gateway.national_id", _("National ID"), [], Validator_Rule::national_id());
        $validation->add("easy-payment-gateway.name", _("Name"), [], Validator_Rule::name());
        $validation->add("easy-payment-gateway.surname", _("Surname"), [], Validator_Rule::surname());
        $validation->add("easy-payment-gateway.country_code", _("Country"), [], Validator_Rule::country_code());

        // return validation
        return $validation;
    }
}
