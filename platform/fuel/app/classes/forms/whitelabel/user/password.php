<?php

use Fuel\Core\Validation;

/**
 *
 */
class Forms_Whitelabel_User_Password extends Forms_Main
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
        
        $val->add("input.password", _("New password"))
            ->add_rule("trim")
            ->add_rule("required")
            ->add_rule('min_length', 6);
        
        return $val;
    }
    
    /**
     *
     * @param string $view_template
     * @param Model_Whitelabel_User $user
     * @return null
     */
    public function process_form(string $view_template, $user): int
    {
        $this->inside = View::forge($view_template);
        $this->inside->set("user", $user);
        
        if (Input::post("input.password") === null) {
            return self::RESULT_GO_FURTHER;
        }

        $val = $this->validate_form();

        if ($val->run()) {
            $newsalt = Lotto_Security::generate_salt();
            $newhash = Lotto_Security::generate_hash(
                $val->validated('input.password'),
                $newsalt
            );
            $user->set([
                'salt' => $newsalt,
                'hash' => $newhash,
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
