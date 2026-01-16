<?php

/**
 * Class for preparing CCPayments form
 */
class Forms_Whitelabel_CCPayments
{
    /**
     * @param array $kcurrencies Currancies codes
     * @return Validation object
     */
    public function get_prepared_form($kcurrencies)
    {
        $val = Validation::forge();
        
        $match_collection = array_keys($kcurrencies);
        
        $val->add("input.method", _("Gateway"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric");

        $val->add("input.cost_percent", _("Percentage cost"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 99.99);
        
        $val->add("input.cost_fixed", _("Fixed cost"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 999999999.99);
        
        $val->add("input.cost_currency", _("Cost currency"))
            ->add_rule("trim")
            ->add_rule("is_numeric")
            ->add_rule("match_collection", $match_collection);
        
        $val->add("input.payment_currency", _("Payment currency"))
            ->add_rule("trim")
            ->add_rule("is_numberic")
            ->add_rule("match_collection", $match_collection);
        
        return $val;
    }
}
