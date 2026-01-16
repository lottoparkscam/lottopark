<?php

use Fuel\Core\Validation;

/**
 * @deprecated
 * Description of Forms_Whitelabel_Aff_Settings
 */
class Forms_Whitelabel_Aff_Settings extends Forms_Main
{
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel)
    {
        $this->whitelabel = $whitelabel;
    }

    /**
     *
     * @return array
     */
    public function get_whitelabel(): array
    {
        return $this->whitelabel;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();
        
        $validation->add("input.activation_type", _("Type"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", Helpers_General::ACTIVATION_TYPE_NONE)
            ->add_rule("numeric_max", Helpers_General::ACTIVATION_TYPE_REQUIRED);
        
        $validation->add("input.auto_accept", _("Automatically accept new affiliates"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.payouttype", _("Automatically payout commissions"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $accept_text = _("Automatically accept new affiliate leads (registrations)");
        $validation->add("input.leadautoaccept", $accept_text)
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $hide_text = _("Hide Ticket cost and Payment cost fields for affiliates");
        $validation->add("input.hide_ticket_and_payment_cost", $hide_text)
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.hide_amount", _("Hide Amount field for affiliates"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.hide_income", _("Hide Income field for affiliates"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.enable_sign_ups", _("Enable sign-ups"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.disable_sign_ups_without_ref", _("Disable user sign-ups without affiliate link"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);
        
        $validation->add("input.auto_new_user_aff", _("Automatically create an affiliate account for new users"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.aff_can_create_sub_affiliates", _("The affiliate can create sub affiliates"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        $validation->add("input.reflifetime", _("Ref lifetime"))
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 8);
            
        return $validation;
    }
    
    /**
     *
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }
    
    /**
     *
     * @return int
     */
    public function process_form(): int
    {
        $whitelabel = $this->get_whitelabel();
        
        $this->inside = Presenter::forge("whitelabel/affs/settings");
        $this->inside->set("whitelabel", $whitelabel);
        
        if (null === Input::post("input.submit")) {
            return self::RESULT_WITH_ERRORS;
        }
        
        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $whitelabel_model = Model_Whitelabel::find_by_pk($whitelabel['id']);
            
            $activation_type = $validated_form->validated('input.activation_type');
            
            $auto_accept = 0;
            if ($validated_form->validated('input.auto_accept')) {
                $auto_accept = 1;
            }
            
            $payout_type = 0;
            if ($validated_form->validated('input.payouttype')) {
                $payout_type = 1;
            }
            
            $lead_auto_accept = 0;
            if ($validated_form->validated('input.leadautoaccept')) {
                $lead_auto_accept = 1;
            }
            
            $hide_ticket_and_payment_cost = 0;
            if ($validated_form->validated('input.hide_ticket_and_payment_cost')) {
                $hide_ticket_and_payment_cost = 1;
            }
            
            $hide_amount = 0;
            if ($validated_form->validated('input.hide_amount')) {
                $hide_amount = 1;
            }
            
            $hide_income = 0;
            if ($validated_form->validated('input.hide_income')) {
                $hide_income = 1;
            }
            
            $ref_lifetime = $validated_form->validated('input.reflifetime');
            
            $enable_sign_ups = 0;
            if ($validated_form->validated('input.enable_sign_ups')) {
                $enable_sign_ups = 1;
            }

            $aff_auto_create = 0;
            if ($validated_form->validated('input.auto_new_user_aff')) {
                $aff_auto_create = 1;
            }

            $aff_can_create_sub_affiliates = 0;
            if ($validated_form->validated('input.aff_can_create_sub_affiliates')) {
                $aff_can_create_sub_affiliates = 1;
            }

            $disable_sign_ups_without_ref = 0;
            if ($validated_form->validated('input.disable_sign_ups_without_ref')) {
                $disable_sign_ups_without_ref = 1;
            }
            
            $set = [
                'aff_activation_type' => $activation_type,
                'aff_auto_accept' => $auto_accept,
                'aff_payout_type' => $payout_type,
                'aff_lead_auto_accept' => $lead_auto_accept,
                'aff_hide_ticket_and_payment_cost' => $hide_ticket_and_payment_cost,
                'aff_hide_amount' => $hide_amount,
                'aff_hide_income' => $hide_income,
                'aff_ref_lifetime' => $ref_lifetime,
                'aff_enable_sign_ups' => $enable_sign_ups,
                'aff_auto_create_on_register' => $aff_auto_create,
                'aff_can_create_sub_affiliates' => $aff_can_create_sub_affiliates,
                'user_registration_through_ref_only' => $disable_sign_ups_without_ref
            ];
                    
            $whitelabel_model->set($set);
            $whitelabel_model->save();
            
            Lotto_Helper::clear_cache(["model_whitelabel.bydomain." . str_replace('.', '-', $whitelabel['domain'])]);

            Session::set_flash("message", ["success", _("Affiliate settings has been saved!")]);
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);
            return self::RESULT_WITH_ERRORS;
        }
        
        return self::RESULT_OK;
    }
}
