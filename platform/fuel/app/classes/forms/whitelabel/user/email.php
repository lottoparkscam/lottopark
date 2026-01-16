<?php

use Fuel\Core\Validation;

/**
 *
 */
class Forms_Whitelabel_User_Email extends Forms_Main
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
     */
    public function __construct($whitelabel)
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
     * @return View
     */
    public function get_inside()
    {
        return $this->inside;
    }

    /**
     * @return Validation object
     */
    protected function validate_form(): Validation
    {
        $val = Validation::forge();

        $val->add("input.email", _("E-mail"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("valid_email");

        return $val;
    }

    /**
     *
     * @param string $view_template
     * @param Model_Whitelabel_User $user
     * @return int
     */
    public function process_form(string $view_template, $user): int
    {
        $this->inside = View::forge($view_template);
        $this->inside->set("user", $user);
        
        if (Input::post("input.email") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $val = $this->validate_form();

        $whitelabel = $this->get_whitelabel();

        if ($val->run()) {
            $res = Model_Whitelabel_User::get_count_for_whitelabel_and_email(
                $whitelabel,
                $val->validated('input.email')
            );

            if (is_null($res)) {    // If that situation happen it means that there is a problem with DB
                return self::RESULT_NULL_COUNTED;
            }

            $userscnt = $res[0]['count'];

            if ($userscnt > 0) {
                $errors = ['input.email' => _("This e-mail is already taken!")];
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }

            $username = null;

            $helper = new Helpers_Whitelabel(
                $whitelabel,
                $user,
                $username,
                $val->validated("input.email")
            );

            $success = $helper->process_cc_method_user_email();

            if (!$success) {
                $errors = [
                    "input.email" => _("Cannot update e-mail in eMerchantPay system. Please contact us!")
                ];
                $this->inside->set("errors", $errors);
                
                return self::RESULT_WITH_ERRORS;
            }
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}
