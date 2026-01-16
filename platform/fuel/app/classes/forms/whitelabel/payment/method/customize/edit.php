<?php

use Fuel\Core\Validation;
use Services\Logs\FileLoggerService;

/**
 * Description of Forms_Whitelabel_Payment_Method_Customize
 */
final class Forms_Whitelabel_Payment_Method_Customize_Edit extends Forms_Main
{
    const RESULT_NO_FREE_LANGUAGES = 100;
    
    /**
     *
     * @var null|int
     */
    private $source = null;
    
    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @var Presenter_Presenter|null
     */
    private $inside = null;

    private FileLoggerService $fileLoggerService;

    /**
     *
     * @var array
     */
    private $whitelabel_payment_methods_indexed = [];
    
    /**
     *
     * @var int
     */
    private $edit_lp = null;
    
    /**
     *
     * @var array
     */
    private $whitelabel_languages_indexed_by_id = [];
    
    /**
     *
     * @var array
     */
    private $whitelabel_languages_ids_indexed_by_wl_lang_id = [];
    
    /**
     *
     * @var int
     */
    private $last_whitelabel_language_id = -1;
    
    /**
     *
     * @param int $source
     * @param array $whitelabel
     * @param array $whitelabel_payment_methods_indexed
     */
    public function __construct(
        int $source,
        array $whitelabel,
        array $whitelabel_payment_methods_indexed
    ) {
        $this->fileLoggerService = Container::get(FileLoggerService::class);

        $this->source = $source;
        $this->whitelabel = $whitelabel;
        $this->whitelabel_payment_methods_indexed = $whitelabel_payment_methods_indexed;

        $whitelabel_languages = Model_Whitelabel_Language::get_whitelabel_languages($whitelabel);        
        foreach ($whitelabel_languages as $whitelabel_language) {
            $this->whitelabel_languages_indexed_by_id[$whitelabel_language['id']] = $whitelabel_language;
            $this->whitelabel_languages_ids_indexed_by_wl_lang_id[$whitelabel_language['wl_lang_id']] = $whitelabel_language['id'];
            $this->last_whitelabel_language_id = intval($whitelabel_language['id']);
        }
        
        $path_to_view = "";
        if ($this->source === Helpers_General::SOURCE_ADMIN) {
            $path_to_view = "admin/whitelabels/payments/customize/edit";
        } else {
            $path_to_view = "whitelabel/settings/payments/customize/edit";
        }
        
        $this->inside = Presenter::forge($path_to_view);
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
     *
     * @return Presenter_Presenter|null
     */
    public function get_inside():? Presenter_Presenter
    {
        return $this->inside;
    }

    /**
     *
     * @return int
     */
    private function get_max_id_of_language(): int
    {
        return $this->last_whitelabel_language_id;
    }
    
    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $max_id_of_language = $this->get_max_id_of_language();
        
        $match_collection = array_keys($this->whitelabel_languages_indexed_by_id);
        
        $validation->add("input.language_id", _("Language"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 1)
            ->add_rule("numeric_max", $max_id_of_language)
            ->add_rule("match_collection", $match_collection);
        
        $validation->add("input.title", _("Title"))
            ->add_rule("trim")
            ->add_rule("max_length", 255)
            ->add_rule("required");
        
        $validation->add("input.title_for_mobile", _("Title for mobile"))
            ->add_rule("trim")
            ->add_rule("max_length", 255)
            ->add_rule("required");
        
        $title_description = _(
            'Title in description area (first line of description' .
            ' - default "Pay using ...")'
        );
        $validation->add("input.title_in_description", $title_description)
            ->add_rule("trim")
            ->add_rule("max_length", 255);
        
        $validation->add("input.description", _("Description"))
            ->add_rule("trim");
        
        $validation->add("input.additional_failure_text", _("Additional text on failure page"))
            ->add_rule("trim");
        
        $validation->add("input.additional_success_text", _("Additional text on success page"))
            ->add_rule("trim");
        
        return $validation;
    }

    /**
     *
     * @param string $action
     * @param int $edit_id
     * @return \Model_Whitelabel_Payment_Method_Customize|null
     */
    private function get_edit_data(
        string $action,
        int $edit_id = null
    ):? Model_Whitelabel_Payment_Method_Customize {
        $edit = null;
        
        $this->edit_lp = null;
        
        if ((string)$action !== "customize" ||
        ((string)$action === "customize" &&
            empty($edit_id))
        ) {
            $data = [
                'title' => "",
                'title_for_mobile' => "",
                'title_in_description' => "",
                'description' => "",
                'additional_failure_text' => "",
                'additional_success_text' => ""
            ];
            $edit = Model_Whitelabel_Payment_Method_Customize::forge($data);
            return $edit;
        }

        $edit_pk = (int)$edit_id;
        
        $this->edit_lp = $edit_id;
        
        $whitelabel_payment_method_customize = Model_Whitelabel_Payment_Method_Customize::find_by_pk($edit_pk);

        if (!empty($whitelabel_payment_method_customize)) {
            $edit = $whitelabel_payment_method_customize;
        }
        
        return $edit;
    }
    
    /**
     *
     * @param int $language_id
     * @return int|null
     */
    private function get_whitelabel_language_id(int $language_id = null):? int
    {
        $whitelabel_language_id = null;
        
        if (!empty($this->whitelabel_languages_indexed_by_id)) {
            $whitelabel_language_id = $this->whitelabel_languages_indexed_by_id[$language_id]['wl_lang_id'];
        }
        
        return $whitelabel_language_id;
    }
    
    /**
     *
     * @param int $whitelabel_payment_method_id
     * @return array|null
     */
    private function get_list_of_already_customize_methods(
        int $whitelabel_payment_method_id = null
    ):? array {
        if (empty($whitelabel_payment_method_id)) {
            return null;
        }
        $customize_methods = Model_Whitelabel_Payment_Method_Customize::get_all_for_whitelabel_payment_method($whitelabel_payment_method_id);
        
        if (empty($customize_methods)) {
            return null;
        }
        
        return $customize_methods;
    }
    
    /**
     *
     * @param int $whitelabel_payment_method_id
     * @param int $edit_id
     * @return void
     */
    private function remove_used_languages_from_list(
        int $whitelabel_payment_method_id = null,
        int $edit_id = null
    ): void {
        if (empty($whitelabel_payment_method_id)) {
            return ;
        }
        
        $customize_methods = $this->get_list_of_already_customize_methods($whitelabel_payment_method_id);
        
        if (empty($customize_methods)) {
            return ;
        }
        
        $whitelabel_languages_array_keys = array_keys($this->whitelabel_languages_indexed_by_id);
        $whitelabel_languages_keys_flipped = array_flip($this->whitelabel_languages_ids_indexed_by_wl_lang_id);
        
        foreach ($customize_methods as $key => $method) {
            if (!in_array($method['language_id'], $whitelabel_languages_array_keys)) {
                continue;
            }
            
            if (!empty($edit_id) && $edit_id === (int)$method['id']) {
                continue;
            } else {
                $key_to_remove = (int)$whitelabel_languages_keys_flipped[$method['language_id']];
                unset($this->whitelabel_languages_indexed_by_id[$method['language_id']]);
                unset($this->whitelabel_languages_ids_indexed_by_wl_lang_id[$key_to_remove]);
            }
        }
        
        return ;
    }
    
    /**
     *
     * @param string $action
     * @param int $whitelabel_payment_method_id
     * @param int $edit_id
     * @param int $whitelabel_payment_method_index
     * @return int
     */
    public function process_form(
        string $action,
        int $whitelabel_payment_method_id = null,
        int $edit_id = null,
        int $whitelabel_payment_method_index = null
    ): int {
        if (empty($whitelabel_payment_method_id) ||
            $whitelabel_payment_method_id <= 0
        ) {
            return self::RESULT_WRONG_PAYMENT_METHOD;
        }
        
        $current_whitelabel_payment_method_index = 0;
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            $current_whitelabel_payment_method_index = $whitelabel_payment_method_id;
        } else {
            $current_whitelabel_payment_method_index = $whitelabel_payment_method_index + 1;
        }
        
        $this->remove_used_languages_from_list($whitelabel_payment_method_id, $edit_id);
        
        if (count($this->whitelabel_languages_indexed_by_id) === 0) {
            return self::RESULT_NO_FREE_LANGUAGES;
        }
        
        $edit = $this->get_edit_data($action, $edit_id);
        
//        if ($action === "customize" && empty($edit)) {
//            return self::RESULT_WRONG_PAYMENT_METHOD;
//        }
        
        $this->inside->set("current_kmethod_idx", $current_whitelabel_payment_method_index);
        $this->inside->set("edit", $edit);
        $this->inside->set("edit_id", $this->edit_lp);
        $this->inside->set("whitelabel_languages", $this->whitelabel_languages_indexed_by_id);
        $this->inside->set("whitelabel_languages_keys", $this->whitelabel_languages_ids_indexed_by_wl_lang_id);
        
        if ((int)$this->source === Helpers_General::SOURCE_ADMIN) {
            $this->inside->set("whitelabel", $this->whitelabel);
        }
        
        if (empty(Input::post())) {
            return self::RESULT_GO_FURTHER;
        }
        
        $validated_form = $this->validate_form();

        if ($validated_form->run()) {
            $language_id = $validated_form->validated('input.language_id');
            $whitelabel_language_id = $this->get_whitelabel_language_id($language_id);
            
            $set = [
                'whitelabel_language_id' => $whitelabel_language_id,
                'title' => $validated_form->validated('input.title'),
                'title_for_mobile' => $validated_form->validated('input.title_for_mobile'),
                'title_in_description' => $validated_form->validated('input.title_in_description'),
                'description' => $validated_form->validated('input.description'),
                'additional_failure_text' => $validated_form->validated('input.additional_failure_text'),
                'additional_success_text' => $validated_form->validated('input.additional_success_text'),
            ];

            try {
                DB::start_transaction();
                
                $method_customize = null;
                if (isset($edit['id'])) {
                    $method_customize = $edit;
                } else {
                    $method_customize = Model_Whitelabel_Payment_Method_Customize::forge();
                    $set['whitelabel_payment_method_id'] = $whitelabel_payment_method_id;
                }

                $method_customize->set($set);
                $method_customize->save();
            
                DB::commit_transaction();
            } catch (\Exception $e) {
                DB::rollback_transaction();

                $this->fileLoggerService->error(
                    $e->getMessage()
                );
                
                $errors = ['input.method' => _("There is a problem with database! Please contact us!")];
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $this->inside->set("errors", $errors);

            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}
