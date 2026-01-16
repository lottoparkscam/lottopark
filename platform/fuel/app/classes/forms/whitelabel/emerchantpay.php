<?php

/**
 * Class for preparing Forms_Whitelabel_Emerchantpay form
 */
class Forms_Whitelabel_Emerchantpay
{
    /**
     *
     * @return Validation object
     */
    public function get_prepared_form()
    {
        $val = Validation::forge("emerchantpay");
        
        $val->add("input.accountid", _("Account ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('is_numeric');
        
        $val->add("input.endpoint", _("Endpoint URL"))
            ->add_rule("trim")
            ->add_rule('valid_url');
        
        $val->add("input.apikey", _("API Key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"]);
        
        $val->add("input.secretkey", _("Secret key"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"]);
        
        $val->add("input.descriptor", _("Descriptor"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("max_length", 100);

        $val->add("input.minorder", _("Minimum order"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999);
        
        $val->add("input.test", _("Test account"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        return $val;
    }
}
