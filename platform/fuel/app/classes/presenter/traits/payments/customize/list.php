<?php

/**
 *
 */
trait Presenter_Traits_Payments_Customize_List
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $new_url = $this->start_url . "/customize/" .
            $this->current_kmethod_idx . "/new";

        $this->set("start_url", $this->start_url);
        $this->set("new_url", $new_url);

        $main_help_block_text = _(
            "Here you can customize payment methods."
        );
        $this->set("main_help_block_text", $main_help_block_text);

        $payment_method_customize = $this->prepare_payment_method_customize();
        $this->set("payment_method_customize", $payment_method_customize);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_payment_method_customize(): array
    {
        $payment_method_customize = [];
        
        $start_url = $this->start_url . "/customize/" .
            $this->current_kmethod_idx;
        
        foreach ($this->payment_method_customize as $customize) {
            $formatted_language = Lotto_View::format_language($customize['code']);
            $customize['language'] = Security::htmlentities($formatted_language);
            
            $customize['title'] = Security::htmlentities($customize['title']);
            
            $customize['title_for_mobile'] = Security::htmlentities($customize['title_for_mobile']);
            
            $customize['title_in_description'] = Security::htmlentities($customize['title_in_description']);
            
            $edit_id = intval($customize['id']);
            
            $customize['edit_url'] = $start_url . "/edit/" . $edit_id;
            $customize['delete_url'] = $start_url . "/delete/" . $edit_id;
            
            $customize['description'] = Security::htmlentities($customize['description']);
            
            $customize['additional_failure_text'] = Security::htmlentities($customize['additional_failure_text']);
            
            $customize['additional_success_text'] = Security::htmlentities($customize['additional_success_text']);
            
            $customize['delete_text'] = _(
                "Are you sure? This operation will delete customization row."
            );
            
            $payment_method_customize[] = $customize;
        }

        return $payment_method_customize;
    }
}
