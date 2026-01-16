<?php

use Fuel\Core\Validation;

/**
 *
 */
class Forms_Whitelabel_User_Balance extends Forms_Main
{
    /**
     *
     * @var View
     */
    private $inside = null;
    
    /**
     *
     */
    public function __construct()
    {
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
        
        $val->add("input.balance", _("User balance"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule("is_numeric")
            ->add_rule("numeric_min", 0)
            ->add_rule("numeric_max", 9999999);
        
        return $val;
    }
    
    /**
     *
     * @param string $view_template
     * @param array $user
     * @return int
     */
    public function process_form(string $view_template, $user): int
    {
        $this->inside = View::forge($view_template);
        
        $currencies = Lotto_Settings::getInstance()->get("currencies");
        $this->inside->set("user", $user);
        $this->inside->set("currencies", $currencies);
        
        if (Input::post("input.balance") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $val = $this->validate_form();

        if ($val->run()) {
            $user->set([
                'balance' => $val->validated("input.balance"),
                'last_update' => DB::expr("NOW()")
            ]);
            $user->save();
        } else {
            $errors = Lotto_Helper::generate_errors($val->error());
            $this->inside->set("errors", $errors);
            
            return self::RESULT_WITH_ERRORS;
        }

        return self::RESULT_OK;
    }
}
