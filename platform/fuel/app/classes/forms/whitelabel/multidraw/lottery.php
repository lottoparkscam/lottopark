<?php

use Fuel\Core\Validation;

/**
 *
 */
final class Forms_Whitelabel_Multidraw_Lottery extends Forms_Main
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
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $validation = Validation::forge();

        $validation->add("lotteries", _("Lotteries"));

        return $validation;
    }

    /**
     * Process email template change form
     *
     * @param $custom_template_id
     * @param $template_id
     * @param $inside
     * @param null $mail_lang
     * @param $id
     */
    public function process_form($inside)
    {
        if (Input::post("submit") === null) {
            return;
        }

        $validated_form = $this->validate_form();

        // Validate
        if ($validated_form->run()) {
            return $this->process_database_action($validated_form);
        } else {
            $errors = Lotto_Helper::generate_errors($validated_form->error());
            $inside->set("errors", $errors);
        }

        return;
    }

    /**
     *
     * @param Validation $validated_form
     * @return bool
     */
    private function process_database_action(Validation $validated_form): bool
    {
        $whitelabel = $this->get_whitelabel();

        if (empty($whitelabel)) {
            return false;
        }
        
        Model_Whitelabel_Multidraw_Lottery::clear_lotteries($whitelabel['id']);

        Model_Whitelabel_Multidraw_Lottery::update_lotteries(
            $whitelabel['id'],
            $validated_form->validated("lotteries")
        );

        Session::set_flash("message", ["success", _("Lotteries list has been updated!")]);

        return true;
    }
}
