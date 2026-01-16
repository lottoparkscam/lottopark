<?php

/**
 *
 */
trait Forms_Whitelabel_Aff_Trait_Options
{
    /**
     * @param \Fuel\Core\Validation $validation
     * @return Void
     */
    private function initialize_aff_input_validation(\Fuel\Core\Validation &$validation): Void
    {
        $validation
            ->add("input.lead_lifetime", _("Lead lifetime"))
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 10)

            ->add("input.is_show_name", _("Show name and surname of the leads."))
            ->add_rule("trim")
            ->add_rule("match_value", 1)

            ->add("input.hide_lead_id", _("Hide lead IDs."))
            ->add_rule("trim")
            ->add_rule("match_value", 1)

            ->add("input.hide_transaction_id", _("Hide transaction IDs."))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
    }
    
    /**
     * Attach affiliate options to affiliate.
     * @param array $affiliate affiliate in form of array.
     * @param \Fuel\Core\Validation $validation validation of input, which contain options.
     * @return array affiliate with options
     */
    private function attach_affiliate_options(
        array $affiliate,
        \Fuel\Core\Validation $validation
    ): array {
        return array_merge(
            $affiliate,
            [
                'aff_lead_lifetime' => $validation->validated('input.lead_lifetime'),
                'is_show_name' => (int)$validation->validated('input.is_show_name'),
                'hide_lead_id' => (int)$validation->validated('input.hide_lead_id'),
                'hide_transaction_id' => (int)$validation->validated('input.hide_transaction_id'),
            ]
        );
    }
}
