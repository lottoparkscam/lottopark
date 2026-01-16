<?php

use Fuel\Core\Validation;
use Services\PageCacheService;

final class Forms_Whitelabel_Multidraw_Option extends Forms_Main
{

    /**
     *
     * @var array
     */
    private $whitelabel = [];

    /**
     *
     * @param array $whitelabel
     */
    public function __construct(array $whitelabel = [])
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
     *
     * @return Validation
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("draws", _("Number of draws"))
            ->add_rule('required')
            ->add_rule("trim");

        $validation->add("discount", _("Discount"))
            ->add_rule("trim");

        return $validation;
    }

    /**
     * Process multidraw option
     *
     * @param type $inside
     * @param int|null $option_id
     * @return int
     */
    public function process_form(&$inside, int $option_id = null): int
    {
        if (Input::post("submit") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $validated_form = $this->validate_form();

        // Validate
        if ($validated_form->run()) {
            $isError = $this->process_database_action($validated_form, $option_id);
            if (!$isError) {
                $pageCacheService = Container::get(PageCacheService::class);
                $pageCacheService->clearWhitelabel();
            }
            return $isError;
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $inside->set("errors", $errors);
        }

        return self::RESULT_WITH_ERRORS;
    }

    /**
     *
     * @param Validation $validated_form
     * @param int|null $option_id
     * @return int
     */
    private function process_database_action(
        Validation $validated_form,
        int $option_id = null
    ): int {
        $whitelabel = $this->get_whitelabel();

        if (empty($whitelabel) || empty($whitelabel['id'])) {
            return self::RESULT_WITH_ERRORS;
        }
        
        if (empty($validated_form->validated("draws"))) {
            Session::set_flash("message", ["danger", _("Empty number of draws!")]);
            return self::RESULT_WITH_ERRORS;
        }
        
        $whitelabel_id = (int) $whitelabel['id'];
        $numberOfDraws = (int) $validated_form->validated("draws");
        $discount = (string) $validated_form->validated("discount");
        
        if (empty($option_id)) {
            Model_Whitelabel_Multidraw_Option::add_whitelabel_option(
                $whitelabel_id,
                $numberOfDraws,
                $discount
            );
            
            Session::set_flash("message", ["success", _("New option has been added!")]);

            return self::RESULT_OK;
        }

        Model_Whitelabel_Multidraw_Option::edit_whitelabel_option(
            $whitelabel_id,
            $option_id,
            $numberOfDraws,
            $discount
        );
        
        Session::set_flash("message", ["success", _("New option has been added!")]);

        return self::RESULT_OK;
    }
    
    /**
     * Update email template content
     *
     * @param $custom_template_id
     * @param $template_id
     * @param $whitelabel_id
     * @param $title
     * @param $content
     * @param $mail_lang
     */
    private function mail_update(
        $custom_template_id,
        $template_id,
        $whitelabel_id,
        $title,
        $content,
        $mail_lang,
        $additional_translates
    ) {
        Model_Whitelabel_Mails_Custom_Template::update_email_template(
            $custom_template_id,
            $template_id,
            $whitelabel_id,
            $title,
            $content,
            $mail_lang,
            $additional_translates
        );

        Session::set_flash("message", ["success", _("Mail has been edited!")]);
    }

    /**
     * Restore default email template
     *
     * @param $whitelabel_id
     * @param $slug
     * @param $lang
     */
    public function restore_default(
        $whitelabel_id,
        $slug,
        $lang
    ) {
        Model_Whitelabel_Mails_Custom_Template::restore_default(
            $whitelabel_id,
            $slug,
            $lang
        );

        Session::set_flash("message", ["success", _("Mail has been restored!")]);
    }

    /**
     * Prepare new additional translates for email template
     *
     * @param $additional_translates
     * @param $new_additional_translates
     * @return array
     */
    private function prepare_new_additional_translates(
        $additional_translates,
        $new_additional_translates
    ) {
        $data = [];

        foreach ($additional_translates as $key => $row) {
            $data[$key] = [
                'label' => $additional_translates[$key]['label'],
                'translation' => $new_additional_translates[$key]
            ];
        }

        return $data;
    }
}
