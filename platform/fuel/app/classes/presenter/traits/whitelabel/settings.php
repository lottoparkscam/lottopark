<?php

/**
 *
 */
trait Presenter_Traits_Whitelabel_Settings
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $help_block_text = _(
            "Be aware that changing the " .
            "e-mail activation type from no activation or " .
            "optional level to required level will make all " .
            "your not-confirmed users inactive! In other way, " .
            "changing activation type from required level to " .
            "optional or no activation level will make all " .
            "your not-confirmed users active!"
        );
        $this->set("help_block_text", $help_block_text);
        
        $prepared_error_classes = $this->prepare_error_classes();
        $this->set("error_classes", $prepared_error_classes);
        
        $prepared_activation_types = $this->prepare_activation_types();
        $this->set("activation_types", $prepared_activation_types);
        
        $prepared_other_values = $this->prepare_other_values();
        $this->set("other_values", $prepared_other_values);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_error_classes(): array
    {
        $prepared_error_classes = [];
        
        $type_class_error = "";
        if (isset($this->errors['input.type'])) {
            $type_class_error = ' has-error';
        }
        $prepared_error_classes["type"] = $type_class_error;
        
        if ($this->full_form) {
            $max_auto_payout_class_error = "";
            if (isset($this->errors['input.maxpayout'])) {
                $max_auto_payout_class_error = ' has-error';
            }
            $prepared_error_classes["maxpayout"] = $max_auto_payout_class_error;
        }
        
        return $prepared_error_classes;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_activation_types(): array
    {
        $prepared_activation_types = [];
        
        $activation_types_list = Helpers_General::get_activation_types();
        $activation_types_list_keys = array_keys($activation_types_list);

        $activation_type_to_select = -1;
        if (Input::post("input.type") !== null) {
            $activation_type_to_select = (int)Input::post("input.type");
        } elseif (!empty($this->whitelabel['user_activation_type']) &&
            in_array($this->whitelabel['user_activation_type'], $activation_types_list_keys)
        ) {
            $activation_type_to_select = (int)$this->whitelabel['user_activation_type'];
        }
        
        foreach ($activation_types_list as $type_key => $text_to_show) {
            $is_selected = '';
            if ($activation_type_to_select === (int)$type_key) {
                $is_selected = ' selected="selected"';
            }
            
            $single_activation_type = [
                "key" => $type_key,
                "selected" => $is_selected,
                "text" => $text_to_show
            ];
            
            $prepared_activation_types[] = $single_activation_type;
        }
    
        return $prepared_activation_types;
    }
    
    /**
     *
     * @return array
     */
    private function prepare_other_values(): array
    {
        $prepared_other_values = [];
        
        $system_currency_code = Helpers_Currency::get_system_currency_code();
        $prepared_other_values["currency_code"] = Lotto_View::format_currency_code($system_currency_code);
        
        if ($this->full_form) {
            $max_autopayout_value = '';
            if (null !== Input::post("input.maxpayout")) {
                $max_autopayout_value = Input::post("input.maxpayout");
            } elseif (!empty($this->whitelabel['max_payout'])) {
                $max_autopayout_value = $this->whitelabel['max_payout'];
            }
            
            $prepared_other_values["max_auto_payout"] = Security::htmlentities($max_autopayout_value);
        }
        
        return $prepared_other_values;
    }
}
