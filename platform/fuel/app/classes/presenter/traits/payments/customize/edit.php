<?php

/**
 *
 */
trait Presenter_Traits_Payments_Customize_Edit
{
    /**
     *
     * @return void
     */
    private function main_process(): void
    {
        $main_help_block_text = _(
            "Here you can custimeze title and " .
            "description for payment method on payment page."
        );
        $this->set("main_help_block_text", $main_help_block_text);
        
        $payment_methods_urls = $this->prepare_urls();
        $this->set("urls", $payment_methods_urls);
        
        $error_class = $this->prepare_error_class();
        $this->set("error_class", $error_class);
        
        $prepared_languages = $this->prepare_language_list($this->edit);
        $this->set("languages_list", $prepared_languages);
    }
    
    /**
     *
     * @return array
     */
    private function prepare_urls(): array
    {
        $add_edit_url = $this->begin_url . '/';
        $list_url = $add_edit_url . 'list/';

        if (isset($this->edit_id)) {
            $add_edit_url .= 'edit/' . $this->edit_id;
        } else {
            $add_edit_url .= 'new';
        }
        
        $urls = [
            "back" => $list_url,
            "add_edit" => $add_edit_url
        ];
        
        return $urls;
    }
    
    /**
     * This function prepare error class table (class as class for View within HTML)
     *
     * @return array
     */
    private function prepare_error_class(): array
    {
        $error_class = [];
        
        $language_id_error_class = '';
        if (isset($this->errors['input.language_id'])) {
            $language_id_error_class = ' has-error';
        }
        $error_class["language_id"] = $language_id_error_class;
        
        $title_error_class = '';
        if (isset($this->errors['input.title'])) {
            $title_error_class = ' has-error';
        }
        $error_class["title"] = $title_error_class;
        
        $title_for_mobile_error_class = '';
        if (isset($this->errors['input.title_for_mobile'])) {
            $title_for_mobile_error_class = ' has-error';
        }
        $error_class["title_for_mobile"] = $title_for_mobile_error_class;
        
        $title_in_description_error_class = '';
        if (isset($this->errors['input.title_in_description'])) {
            $title_in_description_error_class = ' has-error';
        }
        $error_class["title_in_description"] = $title_in_description_error_class;
        
        $description_error_class = '';
        if (isset($this->errors['input.description'])) {
            $description_error_class = ' has-error';
        }
        $error_class["description"] = $description_error_class;
        
        $additional_failure_text_error_class = '';
        if (isset($this->errors['input.additional_failure_text'])) {
            $additional_failure_text_error_class = ' has-error';
        }
        $error_class["additional_failure_text"] = $additional_failure_text_error_class;
        
        $additional_success_text_error_class = '';
        if (isset($this->errors['input.additional_success_text'])) {
            $additional_success_text_error_class = ' has-error';
        }
        $error_class["additional_success_text"] = $additional_success_text_error_class;
        
        return $error_class;
    }
    
    /**
     *
     * @param Model_Whitelabel_Payment_Method_Customize $edit
     * @return array
     */
    protected function prepare_language_list(
        Model_Whitelabel_Payment_Method_Customize $edit = null
    ):? array {
        $prepared_languages = [];
        
        $language_id_to_select = -1;
        if (Input::post("input.language_id") !== null) {
            $language_id_to_select = (int)Input::post("input.language_id");
        } elseif (isset($edit->whitelabel_language_id)) {
            if (isset($this->whitelabel_languages_keys[$edit->whitelabel_language_id])) {
                $language_id_to_select = (int)$this->whitelabel_languages_keys[$edit->whitelabel_language_id];
            }
        } else {
            $language_id_to_select = 1;     // Equal to English
        }
        
        foreach ($this->whitelabel_languages as $language) {
            $is_selected = '';
            if ((int)$language_id_to_select === (int)$language['id']) {
                $is_selected = ' selected="selected"';
            }
            
            $formatted_language = Lotto_View::format_language($language['code']);
            $show_text = Security::htmlentities($formatted_language);
            
            $single_language = [
                "id" => $language['id'],
                "show_text" => $show_text,
                "selected" => $is_selected
            ];
             
            $prepared_languages[] = $single_language;
        }
        
        return $prepared_languages;
    }
}
