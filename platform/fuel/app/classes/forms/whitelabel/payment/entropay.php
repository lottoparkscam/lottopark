<?php

use Fuel\Core\Validation;

/**
 * Class for preparing Entropay form
 */
final class Forms_Whitelabel_Payment_Entropay extends Forms_Main implements Forms_Whitelabel_Payment_ShowData
{
    use Traits_Payment_Method_Currency;
    
    /**
     *
     * @return Validation object
     */
    public function validate_form(): Validation
    {
        $validation = Validation::forge("entropay");
        
        $validation->add("input.ref", _("Referrer ID"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_string", ["alpha", "numeric"])
            ->add_rule("max_length", 64);
        
        $validation->add("input.entropaytest", _("Test environment"))
            ->add_rule("trim")
            ->add_rule("match_value", 1);

        return $validation;
    }
    
    /**
     *
     * @param array $data
     * @param array $errors
     * @return array
     */
    public function prepare_data_to_show(
        array $data = null,
        array $errors = null
    ): array {
        $entropay = [];
        
        $ref_error_class = '';
        if (isset($errors['input.ref'])) {
            $ref_error_class = ' has-error';
        }
        $entropay['ref_error_class'] = $ref_error_class;
        
        $ref_value_t = '';
        if (null !== Input::post("input.ref")) {
            $ref_value_t = Input::post("input.ref");
        } elseif (isset($data['referrer_id'])) {
            $ref_value_t = $data['referrer_id'];
        }
        $entropay['ref_value'] = Security::htmlentities($ref_value_t);

        $test_checked = '';
        if ((null !== Input::post("input.entropaytest") &&
                Input::post("input.entropaytest") == 1) ||
            (isset($data['test']) &&
                $data['test'] == 1)
        ) {
            $test_checked = ' checked="checked"';
        }
        $entropay['test_checked'] = $test_checked;
        
        return $entropay;
    }
    
    /**
     *
     * @param Validation|null $additional_values_validation
     * @return array
     */
    public function get_data(
        ?Validation $additional_values_validation
    ): array {
        $data = [];
        $data['referrer_id'] = $additional_values_validation->validated("input.ref");
        $data['test'] = $additional_values_validation->validated("input.entropaytest") == 1 ? 1 : 0;
                
        return $data;
    }
}
